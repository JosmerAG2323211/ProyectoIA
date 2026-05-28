<?php
// reportar_falla.php
require_once 'config/conexion.php';

$mensaje = "";
$clase_alerta = "";

// 1. Obtener equipos para el select
$stmt_equipos = $pdo->query("SELECT id, nombre, marca, modelo FROM equipos ORDER BY nombre ASC");
$equipos = $stmt_equipos->fetchAll();

// 2. Obtener los usuarios que son técnicos para el select
$stmt_tecnicos = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'tecnico' ORDER BY nombre ASC");
$tecnicos = $stmt_tecnicos->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipo_id = $_POST['equipo_id'];
    $descripcion_falla = trim($_POST['descripcion_falla']);
    
    // Obtener el técnico asignado (permite valores nulos en la BD)
    $tecnico_id = !empty($_POST['tecnico_id']) ? $_POST['tecnico_id'] : null;
    
    $usuario_id = 1; // ID simulado del usuario logueado
    $foto_nombre = null;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $foto_nombre = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
            if (!is_dir('./uploads')) {
                mkdir('./uploads', 0777, true);
            }
            $dest_path = './uploads/' . $foto_nombre;
            move_uploaded_file($fileTmpPath, $dest_path);
        }
    }

    // Priorización automática simulada (IA)
    $prioridad = "Rutinaria";
    if (preg_match('/(urgente|inmediato|rompio|humo|fuego|grave|critico)/i', $descripcion_falla)) {
        $prioridad = "Crítica";
    } elseif (preg_match('/(falla|error|dañado|malfuncionamiento)/i', $descripcion_falla)) {
        $prioridad = "Urgente";
    }

    $diagnostico_ia = "Análisis Inteligente Preliminar: Se ha registrado la anomalía reportada.";

    // Inserción en la base de datos incluyendo técnico asignado
    $sql = "INSERT INTO reportes_fallas (equipo_id, usuario_id, tecnico_id, descripcion_falla, foto_evidencia, prioridad, diagnostico_ia, estado) VALUES (?, ?, ?, ?, ?, ?, ?, 'Abierto')";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$equipo_id, $usuario_id, $tecnico_id, $descripcion_falla, $foto_nombre, $prioridad, $diagnostico_ia])) {
        $mensaje = "¡Reporte procesado exitosamente!";
        $clase_alerta = "is-success";
    } else {
        $mensaje = "Error operacional al procesar el reporte.";
        $clase_alerta = "is-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Falla - Mantenimiento IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .input, .textarea, .select select {
            background-color: #1a152c !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }
        .input:focus, .textarea:focus, .select select:focus {
            border-color: var(--dynamic-core-color, #ff007f) !important;
            box-shadow: 0 0 0 3px rgba(255, 0, 127, 0.15) !important;
        }
        
        .select select option {
            background-color: #130e1b !important;
            color: #fff !important;
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
        .select:not(.is-multiple):not(.is-loading)::after {
            border-color: rgba(255, 255, 255, 0.4) !important;
        }
        
        :root {
            --dynamic-core-color: #ff007f; 
        }
        .theme-accent-text {
            color: var(--dynamic-core-color) !important;
        }
        .theme-accent-bg {
            background-color: var(--dynamic-core-color) !important;
            color: #fff !important;
            font-weight: 500;
            border-radius: 8px !important;
            border: none !important;
            transition: opacity 0.2s ease;
        }
        .theme-accent-bg:hover {
            opacity: 0.9;
            color: #fff !important;
        }

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

        .menu-floating-trigger {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999; 
            background-color: var(--dynamic-core-color) !important;
            color: #fff !important;
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
            text-shadow: 0 0 10px rgba(255, 0, 127, 0.15);
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
                Reportar Falla Técnica
            </h1>

            <?php if ($mensaje): ?>
                <div class="notification <?php echo $clase_alerta; ?> py-2 px-3 is-size-7 mb-4" style="border-radius: 8px;">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="field mb-4">
                    <label class="label">Máquina o Equipo Afectado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="equipo_id" required>
                                <option value="" disabled selected hidden>Ej: Seleccione el equipo de la planta</option>
                                <?php foreach ($equipos as $eq): ?>
                                    <option value="<?php echo $eq['id']; ?>">
                                        <?php echo htmlspecialchars($eq['nombre'] . ' [' . $eq['modelo'] . ']'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field mb-4">
                    <label class="label">Descripción de la Anomalía</label>
                    <div class="control">
                        <textarea class="textarea" name="descripcion_falla" rows="4" placeholder="Ej: Pérdida de presión en válvula secundaria, sobrecalentamiento intermitente..." required></textarea>
                    </div>
                </div>

                <div class="field mb-4">
                    <label class="label">Asignar Técnico Responsable</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tecnico_id">
                                <option value="" selected>Dejar sin asignar (Por defecto)</option>
                                <?php foreach ($tecnicos as $tec): ?>
                                    <option value="<?php echo $tec['id']; ?>">
                                        <?php echo htmlspecialchars($tec['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="label">Evidencia de Falla (Solo Imagen)</label>
                    <div class="file has-name is-fullwidth">
                        <label class="file-label">
                            <input class="file-input" type="file" name="foto" accept="image/*" id="foto-file">
                            <span class="file-cta" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.05); color: #fff; border-radius: 8px 0 0 8px;">
                                <span class="file-icon theme-accent-text">
                                    <i class="fa-regular fa-image"></i>
                                </span>
                                <span class="file-label" style="font-size: 0.85rem;">Adjuntar foto...</span>
                            </span>
                            <span class="file-name" id="foto-name-display" style="border-color: rgba(255, 255, 255, 0.08); color: rgba(255,255,255,0.3); background: transparent; font-size: 0.85rem; border-radius: 0 8px 8px 0; display: flex; align-items: center;">
                                Ningún archivo seleccionado
                            </span>
                        </label>
                    </div>
                </div>

                <div class="field mt-5">
                    <div class="control buttons is-centered">
                        <button type="submit" class="button theme-accent-bg px-5 py-4" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Guardar Reporte
                        </button>
                        <a href="javascript:history.back();" class="button btn-regresar px-5 py-4" style="font-size: 0.95rem;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
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
            color: 0xff007f, 
            size: 1.2,
            transparent: true,
            opacity: 0.85,
            blending: THREE.AdditiveBlending
        });

        const savedColor = localStorage.getItem('selected-theme');
        if (savedColor === 'cyan') {
            document.documentElement.style.setProperty('--dynamic-core-color', '#00e5ff');
            material.color.setHex(0x00e5ff);
        } else if (savedColor === 'matrix-green') {
            document.documentElement.style.setProperty('--dynamic-core-color', '#39ff14');
            material.color.setHex(0x39ff14);
        } else {
            document.documentElement.style.setProperty('--dynamic-core-color', '#ff007f');
            material.color.setHex(0xff007f);
        }

        const mouse = { x: 0, y: 0, targetX: 0, targetY: 0, radius: 22, activo: false };
        let mouseTimeout;

        window.addEventListener('mousemove', (event) => {
            mouse.activo = true;
            mouse.targetX = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.targetY = -(event.clientY / window.innerHeight) * 2 + 1;
            clearTimeout(mouseTimeout);
            mouseTimeout = setTimeout(() => { mouse.activo = false; }, 1500);
        });

        function inicializarTextoParticulas() {
            const textCanvas = document.createElement('canvas');
            const textCtx = textCanvas.getContext('2d');
            textCanvas.width = 720;  
            textCanvas.height = 250; 

            textCtx.font = 'bold 140px Impact, "Arial Black", sans-serif';
            textCtx.fillStyle = '#ffffff';
            textCtx.textBaseline = 'middle';
            textCtx.textAlign = 'center';
            textCtx.fillText('AI.G', textCanvas.width / 2, textCanvas.height / 2);

            const imgData = textCtx.getImageData(0, 0, textCanvas.width, textCanvas.height);
            const puntosValidos = [];
            const escalaX = 0.4;
            const escalaY = 0.4;

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
                            posAttr.array[idx] -= (dx / dist) * force * 0.9; 
                            posAttr.array[idx+1] -= (dy / dist) * force * 0.9;
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
                particleSystem.rotation.y = Math.sin(Date.now() * 0.0002) * 0.03;
                particleSystem.rotation.x = Math.cos(Date.now() * 0.0002) * 0.01;
            }
            renderer.render(scene, camera);
        }

        setTimeout(inicializarTextoParticulas, 100);

        const fileInput = document.getElementById('foto-file');
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                document.getElementById('foto-name-display').textContent = fileInput.files[0].name;
            }
        };

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        document.getElementById('sidebar-trigger-button').addEventListener('click', function(e) {
            e.stopPropagation();
            const sidebar = document.querySelector('.sidebar') || document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('is-active');
            }
        });

        // Asegurar que el título herede el neón al cargar el DOM
        document.addEventListener("DOMContentLoaded", () => {
            const sidebarTitle = document.querySelector('.sidebar h1, .sidebar .brand-title');
            if(sidebarTitle) {
                sidebarTitle.style.setProperty('color', 'var(--dynamic-core-color)', 'important');
            }
        });
    </script>
</body>
</html>