<?php
// registrar_equipo.php
require_once 'config/conexion.php';

$mensaje = "";
$clase_alerta = "";

// Función simple para limpiar nombres de archivos
function sanitizeFileName($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $modelo = trim($_POST['modelo']);
    $marca = trim($_POST['marca']);
    
    if (isset($_FILES['manual']) && $_FILES['manual']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['manual']['tmp_name'];
        $fileName = $_FILES['manual']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if ($fileExtension === 'pdf') {
            $nuevoNombreArchivo = time() . '_' . sanitizeFileName($fileName);
            $uploadFileDir = './manuales/';
            
            if(!is_dir($uploadFileDir)){
                mkdir($uploadFileDir, 0777, true);
            }
            
            $dest_path = $uploadFileDir . $nuevoNombreArchivo;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $sql = "INSERT INTO equipos (nombre, modelo, marca, archivo_manual) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$nombre, $modelo, $marca, $nuevoNombreArchivo])) {
                    $mensaje = "Equipo y manual registrados exitosamente.";
                    $clase_alerta = "is-success";
                } else {
                    $mensaje = "Error al guardar en la base de datos.";
                    $clase_alerta = "is-danger";
                }
            } else {
                $mensaje = "Error al mover el archivo al directorio de destino.";
                $clase_alerta = "is-danger";
            }
        } else {
            $mensaje = "Formato no permitido. Solo se aceptan archivos PDF.";
            $clase_alerta = "is-danger";
        }
    } else {
        $mensaje = "Por favor, seleccione el manual técnico en PDF.";
        $clase_alerta = "is-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Equipo - Mantenimiento IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            background-color: #0b0713;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 40px 20px;
        }
        #gan-canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
            opacity: 0.35;
        }
        .main-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 520px;
        }
        .custom-card {
            background-color: rgba(19, 14, 27, 0.82) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 35px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }
        
        /* DISEÑO DE INPUTS OSCUROS ALINEADO */
        .input, .textarea, .select select {
            background-color: #1a152c !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }
        .input:focus, .textarea:focus, .select select:focus {
            border-color: var(--dynamic-core-color, #00e5ff) !important;
            box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.15) !important;
        }
        
        .input::placeholder, .textarea::placeholder {
            color: rgba(255, 255, 255, 0.2) !important;
        }
        .label {
            color: #d1cbdc !important;
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        
        :root {
            --dynamic-core-color: #00e5ff;
        }
        .theme-accent-text {
            color: var(--dynamic-core-color) !important;
        }
        .theme-accent-bg {
            background-color: var(--dynamic-core-color) !important;
            color: #120d1e !important;
            font-weight: 600;
            border-radius: 8px !important;
            border: none !important;
            transition: opacity 0.2s ease;
        }
        .theme-accent-bg:hover {
            opacity: 0.9;
            color: #120d1e !important;
        }

        /* DISEÑO REFINADO DEL BOTÓN CANCELAR/REGRESAR */
        .btn-regresar {
            background-color: rgba(255, 255, 255, 0.07) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.03) !important;
            border-radius: 8px !important;
            font-weight: 500;
            transition: background-color 0.2s ease, opacity 0.2s ease;
        }
        .btn-regresar:hover {
            background-color: rgba(255, 255, 255, 0.14) !important;
            color: #ffffff !important;
        }

        /* BOTÓN FLOTANTE TRIGGER DEL SIDEBAR */
        .menu-floating-trigger {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999; 
            background-color: var(--dynamic-core-color) !important;
            color: #120d1e !important;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        .menu-floating-trigger:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        /* Fuerza el color neón dinámico en el título del Sidebar */
        .sidebar h1, .sidebar .brand-title, .sidebar-header {
            color: var(--dynamic-core-color) !important;
            text-shadow: 0 0 10px rgba(0, 229, 255, 0.15);
        }
    </style>
</head>
<body>
    <?php include 'componentes/sidebar.php'; ?>
    
    <div id="gan-canvas-container"></div>

    <div class="main-wrapper">
        <div class="box custom-card">
            <h1 class="title is-4 has-text-white mb-5 is-flex is-align-items-center">
                <span class="icon theme-accent-text mr-2" style="font-size: 1.15rem;">
                    <i class="fa-solid fa-square-plus"></i>
                </span>
                Registrar Nuevo Equipo
            </h1>

            <?php if ($mensaje): ?>
                <div class="notification <?php echo $clase_alerta; ?> py-2 px-3 is-size-7 mb-4" style="border-radius: 8px;">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="registrar_equipo.php" method="POST" enctype="multipart/form-data">
                <div class="field mb-4">
                    <label class="label">Nombre del Equipo / Máquina</label>
                    <div class="control">
                        <input class="input" type="text" name="nombre" placeholder="Ej: Torno CNC, Compresor Industrial" required>
                    </div>
                </div>

                <div class="field mb-4">
                    <label class="label">Marca</label>
                    <div class="control">
                        <input class="input" type="text" name="marca" placeholder="Ej: Siemens, Caterpillar" required>
                    </div>
                </div>

                <div class="field mb-4">
                    <label class="label">Modelo / Serie</label>
                    <div class="control">
                        <input class="input" type="text" name="modelo" placeholder="Ej: XZ-2000, V8-Turbo" required>
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="label">Manual Técnico (Solo PDF)</label>
                    <div class="file has-name is-fullwidth">
                        <label class="file-label">
                            <input class="file-input" type="file" name="manual" accept=".pdf" required id="manual-file">
                            <span class="file-cta" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.05); color: #fff; border-radius: 8px 0 0 8px;">
                                <span class="file-icon theme-accent-text">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </span>
                                <span class="file-label" style="font-size: 0.85rem;">Seleccionar PDF…</span>
                            </span>
                            <span class="file-name" id="file-name-display" style="border-color: rgba(255, 255, 255, 0.08); color: rgba(255,255,255,0.3); background: transparent; font-size: 0.85rem; border-radius: 0 8px 8px 0; display: flex; align-items: center;">
                                Ningún archivo seleccionado
                            </span>
                        </label>
                    </div>
                </div>

                <div class="field mt-5">
                    <div class="control buttons is-centered">
                        <button type="submit" class="button theme-accent-bg px-5 py-4" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Equipo
                        </button>
                        <a href="index.php" class="button btn-regresar px-5 py-4" style="font-size: 0.95rem;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // --- ADAPTACIÓN AUTOMÁTICA DEL COLOR DE NÚCLEO DESDE LOCALSTORAGE ---
        let hexColor = 0x00e5ff;
        let cssColor = '#00e5ff';
        const savedColor = localStorage.getItem('selected-theme');
        
        if(savedColor === 'neon-purple') {
            hexColor = 0xff007f;
            cssColor = '#ff007f';
        } else if(savedColor === 'matrix-green') {
            hexColor = 0x39ff14;
            cssColor = '#39ff14';
        }
        document.documentElement.style.setProperty('--dynamic-core-color', cssColor);

        // --- MOTOR INDUSTRIAL DE PARTÍCULAS 3D (AI.G) ---
        const container = document.getElementById('gan-canvas-container');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 60;

        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        container.appendChild(renderer.domElement);

        let geometry = new THREE.BufferGeometry();
        let particleCount = 0;
        let positions, originalPositions;
        let particleSystem;

        const material = new THREE.PointsMaterial({
            color: hexColor,
            size: 1.1,
            transparent: true,
            opacity: 0.9,
            blending: THREE.AdditiveBlending
        });

        const mouse = { x: 0, y: 0, targetX: 0, targetY: 0, radius: 20, activo: false };
        let mouseTimeout;

        window.addEventListener('mousemove', (event) => {
            mouse.activo = true;
            mouse.targetX = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.targetY = -(event.clientY / window.innerHeight) * 2 + 1;

            clearTimeout(mouseTimeout);
            mouseTimeout = setTimeout(() => { mouse.activo = false; }, 1500);
        });

        document.addEventListener('mouseleave', () => { mouse.activo = false; });

        function inicializarTextoParticulas() {
            const textCanvas = document.createElement('canvas');
            const textCtx = textCanvas.getContext('2d');
            textCanvas.width = 700;  
            textCanvas.height = 250; 

            textCtx.font = 'bold 130px Impact, "Arial Black", sans-serif';
            textCtx.fillStyle = '#ffffff';
            textCtx.textAlign = 'center';
            textCtx.textBaseline = 'middle';
            textCtx.fillText('AI.G', textCanvas.width / 2, textCanvas.height / 2);

            const imgData = textCtx.getImageData(0, 0, textCanvas.width, textCanvas.height);
            const puntosValidos = [];
            const escalaX = 0.38;
            const escalaY = 0.38;

            for (let y = 0; y < textCanvas.height; y++) {
                for (let x = 0; x < textCanvas.width; x++) {
                    const index = (x + y * textCanvas.width) * 4;
                    if (imgData.data[index + 3] > 128) { 
                        puntosValidos.push({
                            x: (x - textCanvas.width / 2) * escalaX,  
                            y: -(y - textCanvas.height / 2) * escalaY
                        });
                    }
                }
            }

            particleCount = puntosValidos.length;
            positions = new Float32Array(particleCount * 3);
            originalPositions = new Float32Array(particleCount * 3);

            for (let i = 0; i < particleCount; i++) {
                const idx = i * 3;
                positions[idx]     = puntosValidos[i].x;
                positions[idx + 1] = puntosValidos[i].y;
                positions[idx + 2] = (Math.random() - 0.5) * 1.5; 

                originalPositions[idx]     = puntosValidos[i].x;
                originalPositions[idx + 1] = puntosValidos[i].y;
                originalPositions[idx + 2] = positions[idx + 2];
            }

            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            particleSystem = new THREE.Points(geometry, material);
            scene.add(particleSystem);
            animate();
        }

        function animate() {
            requestAnimationFrame(animate);
            mouse.x += (mouse.targetX * 65 - mouse.x) * 0.08;
            mouse.y += (mouse.targetY * 35 - mouse.y) * 0.08;

            if (geometry && geometry.attributes.position) {
                const posAttr = geometry.attributes.position;
                for (let i = 0; i < particleCount; i++) {
                    let idx = i * 3;
                    let px = originalPositions[idx];
                    let py = originalPositions[idx+1];

                    if (mouse.activo) {
                        let dx = mouse.x - px;
                        let dy = mouse.y - py;
                        let dist = Math.sqrt(dx * dx + dy * dy);
                        if (dist < mouse.radius) {
                            let force = (mouse.radius - dist) / mouse.radius;
                            posAttr.array[idx] -= (dx / dist) * force * 0.8; 
                            posAttr.array[idx+1] -= (dy / dist) * force * 0.8;
                        } else {
                            posAttr.array[idx] += (px - posAttr.array[idx]) * 0.06;
                            posAttr.array[idx+1] += (py - posAttr.array[idx+1]) * 0.06;
                        }
                    } else {
                        posAttr.array[idx] += (px - posAttr.array[idx]) * 0.08;
                        posAttr.array[idx+1] += (py - posAttr.array[idx+1]) * 0.08;
                    }
                }
                posAttr.needsUpdate = true;
            }
            if (particleSystem) {
                particleSystem.rotation.y = Math.sin(Date.now() * 0.0003) * 0.02;
                particleSystem.rotation.x = Math.cos(Date.now() * 0.0003) * 0.01;
            }
            renderer.render(scene, camera);
        }

        setTimeout(inicializarTextoParticulas, 150);

        // --- ACTUALIZAR NOMBRE DEL ARCHIVO SELECCIONADO ---
        const fileInput = document.getElementById('manual-file');
        if(fileInput) {
            fileInput.onchange = () => {
                if (fileInput.files.length > 0) {
                    document.getElementById('file-name-display').textContent = fileInput.files[0].name;
                }
            }
        }

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // --- TRIGGER FLOTANTE PARA INTERACCIÓN DEL SIDEBAR ---
        document.getElementById('sidebar-trigger-button').addEventListener('click', function(e) {
            e.stopPropagation();
            const sidebar = document.querySelector('.sidebar') || document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('is-active');
            }
        });

        // Asegurar que el título herede el color dinámico al cargar el DOM
        document.addEventListener("DOMContentLoaded", () => {
            const sidebarTitle = document.querySelector('.sidebar h1, .sidebar .brand-title, .sidebar-header');
            if(sidebarTitle) {
                sidebarTitle.style.setProperty('color', 'var(--dynamic-core-color)', 'important');
            }
        });
    </script>
</body>
</html>