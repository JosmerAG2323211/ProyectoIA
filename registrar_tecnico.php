<?php
// registrar_tecnico.php
require_once 'config/conexion.php';

$mensaje = "";
$clase_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $rol = 'tecnico'; // Se fuerza el rol técnico basándonos en la estructura SQL

    if (!empty($nombre) && !empty($correo) && !empty($password)) {
        // Validar si el correo institucional o personal ya fue registrado
        $checkSql = "SELECT COUNT(*) FROM usuarios WHERE correo = ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$correo]);
        
        if ($checkStmt->fetchColumn() > 0) {
            $mensaje = "El correo electrónico ya está asignado a otro operador.";
            $clase_alerta = "is-danger";
        } else {
            // Guardar el registro técnico de forma limpia
            $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$nombre, $correo, $password, $rol])) {
                $mensaje = "Nuevo técnico dado de alta exitosamente en la plataforma RAG.";
                $clase_alerta = "is-success";
            } else {
                $mensaje = "Error de escritura en la base de datos de infraestructura.";
                $clase_alerta = "is-danger";
            }
        }
    } else {
        $mensaje = "Por favor, proporcione todos los campos mandatorios de seguridad.";
        $clase_alerta = "is-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Técnico - Mantenimiento IA</title>
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
            color: #fff; 
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
        
        /* INPUTS OSCUROS CON ENFOQUE REACTIVO */
        .input { 
            background-color: #1a152c !important; 
            border: 1px solid rgba(255, 255, 255, 0.08) !important; 
            color: #fff !important; 
            border-radius: 8px !important;
            transition: all 0.3s ease;
        }
        .input:focus {
            border-color: var(--dynamic-core-color, #00e5ff) !important;
            box-shadow: 0 0 0 3px rgba(0, 229, 255, 0.15) !important;
        }
        .input::placeholder { 
            color: rgba(255, 255, 255, 0.2) !important; 
        }
        .control.has-icons-left .icon {
            color: rgba(255, 255, 255, 0.3) !important;
        }
        .control.has-icons-left .input:focus ~ .icon {
            color: var(--dynamic-core-color, #00e5ff) !important;
        }

        .label { 
            color: #d1cbdc !important; 
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }
        
        :root { --dynamic-core-color: #00e5ff; }
        .theme-accent-text { color: var(--dynamic-core-color) !important; }
        
        .theme-accent-bg {
            background-color: var(--dynamic-core-color) !important;
            color: #120d1e !important;
            font-weight: 600;
            border-radius: 8px !important;
            border: none !important;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .theme-accent-bg:hover {
            opacity: 0.9;
            color: #120d1e !important;
        }

        /* DISEÑO DE BOTÓN DE RETORNO */
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

        /* DISPARADOR FLOTANTE DEL MENÚ LATERAL */
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

        /* Inyección cromática neón para la marca del Sidebar */
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
                    <i class="fa-solid fa-user-plus"></i>
                </span>
                Registrar Personal Técnico
            </h1>

            <?php if ($mensaje): ?>
                <div class="notification <?php echo $clase_alerta; ?> py-2 px-3 is-size-7 mb-4" style="border-radius: 8px;">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="registrar_tecnico.php" method="POST">
                <div class="field mb-4">
                    <label class="label">Nombre Completo del Operador</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="nombre" placeholder="Ej: Wilber Ruiz" required>
                        <span class="icon is-small is-left"><i class="fa-solid fa-user"></i></span>
                    </div>
                </div>

                <div class="field mb-4">
                    <label class="label">Correo Electrónico (Usuario)</label>
                    <div class="control has-icons-left">
                        <input class="input" type="email" name="correo" placeholder="Ej: tecnico@ugma.edu.ve" required>
                        <span class="icon is-small is-left"><i class="fa-solid fa-envelope"></i></span>
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="label">Contraseña de Acceso Cortafuegos</label>
                    <div class="control has-icons-left">
                        <input class="input" type="password" name="password" placeholder="••••••••" required>
                        <span class="icon is-small is-left"><i class="fa-solid fa-lock"></i></span>
                    </div>
                </div>

                <div class="field mt-5">
                    <div class="control buttons is-centered">
                        <button type="submit" class="button theme-accent-bg px-5 py-4" style="font-size: 0.95rem;">
                            <i class="fa-solid fa-user-plus mr-2"></i> Dar de Alta
                        </button>
                        <a href="index.php" class="button btn-regresar px-5 py-4" style="font-size: 0.95rem;">
                            Regresar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        // --- ADAPTACIÓN AUTOMÁTICA DEL COLOR DE NÚCLEO DESDE LOCALSTORAGE ---
        let hexColor = 0x00e5ff; let cssColor = '#00e5ff';
        const savedColor = localStorage.getItem('selected-theme');
        if(savedColor === 'neon-purple') { hexColor = 0xff007f; cssColor = '#ff007f'; }
        else if(savedColor === 'matrix-green') { hexColor = 0x39ff14; cssColor = '#39ff14'; }
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
        let particleCount = 0; let positions, originalPositions, particleSystem;
        const material = new THREE.PointsMaterial({ color: hexColor, size: 1.1, transparent: true, opacity: 0.9, blending: THREE.AdditiveBlending });

        // SISTEMA DE SEGUIMIENTO FÍSICO DEL MOUSE
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
            const textCanvas = document.createElement('canvas'); const textCtx = textCanvas.getContext('2d');
            textCanvas.width = 700; textCanvas.height = 250;
            textCtx.font = 'bold 130px Impact, sans-serif'; textCtx.fillStyle = '#ffffff';
            textCtx.textAlign = 'center'; textCtx.textBaseline = 'middle';
            textCtx.fillText('AI.G', textCanvas.width / 2, textCanvas.height / 2);
            const imgData = textCtx.getImageData(0, 0, textCanvas.width, textCanvas.height);
            const puntosValidos = [];
            for (let y = 0; y < textCanvas.height; y++) {
                for (let x = 0; x < textCanvas.width; x++) {
                    if (imgData.data[(x + y * textCanvas.width) * 4 + 3] > 128) {
                        puntosValidos.push({ x: (x - textCanvas.width / 2) * 0.38, y: -(y - textCanvas.height / 2) * 0.38 });
                    }
                }
            }
            particleCount = puntosValidos.length;
            positions = new Float32Array(particleCount * 3); originalPositions = new Float32Array(particleCount * 3);
            for (let i = 0; i < particleCount; i++) {
                let idx = i * 3;
                positions[idx] = puntosValidos[i].x; positions[idx + 1] = puntosValidos[i].y; positions[idx + 2] = (Math.random() - 0.5) * 1.5;
                originalPositions[idx] = puntosValidos[i].x; originalPositions[idx + 1] = puntosValidos[i].y; originalPositions[idx + 2] = positions[idx + 2];
            }
            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            particleSystem = new THREE.Points(geometry, material); scene.add(particleSystem);
            animate();
        }
        
        function animate() {
            requestAnimationFrame(animate);
            
            // Interpolación suavizada de las coordenadas del mouse
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
        
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight; camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // --- GESTIÓN INTERACTIVA DEL SIDEBAR DESDE TRIGGER ---
        document.getElementById('sidebar-trigger-button').addEventListener('click', function(e) {
            e.stopPropagation();
            const sidebar = document.querySelector('.sidebar') || document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('is-active');
            }
        });

        // Asegurar que las cabeceras del menú rendericen el color correcto al cargar
        document.addEventListener("DOMContentLoaded", () => {
            const sidebarTitle = document.querySelector('.sidebar h1, .sidebar .brand-title, .sidebar-header');
            if(sidebarTitle) {
                sidebarTitle.style.setProperty('color', 'var(--dynamic-core-color)', 'important');
            }
        });
    </script>
</body>
</html>