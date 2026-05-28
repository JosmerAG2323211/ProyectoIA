<?php
// editar_equipo.php
require_once 'config/conexion.php';

$mensaje = "";
$clase_alerta = "";

if (!isset($_GET['id'])) {
    header("Location: gestion_recursos.php");
    exit;
}

$id = intval($_GET['id']);

// Obtener datos actuales del hardware
$stmt = $pdo->prepare("SELECT * FROM equipos WHERE id = ?");
$stmt->execute([$id]);
$equipo = $stmt->fetch();

if (!$equipo) {
    header("Location: gestion_recursos.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $marca = trim($_POST['marca']);
    $modelo = trim($_POST['modelo']);
    
    if (!empty($nombre) && !empty($marca) && !empty($modelo)) {
        try {
            $stmt_update = $pdo->prepare("UPDATE equipos SET nombre = ?, marca = ?, modelo = ? WHERE id = ?");
            $stmt_update->execute([$nombre, $marca, $modelo, $id]);
            
            header("Location: gestion_recursos.php?status=equipo_updated");
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar los datos en el sistema central.";
            $clase_alerta = "is-danger";
        }
    } else {
        $mensaje = "Todos los campos de identificación son mandatorios.";
        $clase_alerta = "is-warning";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Hardware - Mantenimiento IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* FONDO EXCLUSIVO INTEGRADO CON THREE.JS */
        body {
            background-color: #070a13;
            min-height: 100vh;
            overflow-x: hidden;
            color: #f1f5f9;
            font-family: system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
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

        .main-content-area {
            position: relative;
            z-index: 2;
            padding: 3rem 1.5rem;
        }

        /* Cristal esmerilado translúcido mediante CSS */
        .custom-card {
            background-color: rgba(13, 20, 38, 0.65) !important;
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45);
        }

        .title, .subtitle, .label {
            color: #ffffff !important;
        }

        /* Inputs Futuristas */
        .input {
            background-color: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
            transition: all 0.3s ease;
        }
        .input:focus {
            border-color: var(--dynamic-core-color, #00e5ff) !important;
            box-shadow: 0 0 10px rgba(0, 229, 255, 0.2) !important;
        }

        :root {
            --dynamic-core-color: #00e5ff;
        }
        .theme-accent-text {
            color: var(--dynamic-core-color) !important;
        }
        .theme-accent-border {
            border-left: 4px solid var(--dynamic-core-color) !important;
        }

        /* Botón de Guardado */
        .btn-cyber-action {
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-cyber-action:hover {
            border-color: var(--dynamic-core-color) !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 0 15px rgba(0, 229, 255, 0.15);
        }
        .btn-cyber-action .icon {
            color: var(--dynamic-core-color) !important;
        }
    </style>
</head>
<body>

    <?php include 'componentes/sidebar.php'; ?>

    <div id="gan-canvas-container"></div>

    <main class="section main-content-area">
        <div class="container is-max-desktop" style="margin-top: 25px;">
            
            <div class="block mb-6" style="padding-left: 10px;">
                <h1 class="title is-2" style="letter-spacing: 2px; font-weight: 800; text-transform: uppercase;">Actualizar Equipo</h1>
                <h2 class="subtitle is-6 mt-1" style="color: #cbd5e1 !important;">
                    Modificando los parámetros para: <span class="theme-accent-text" style="font-weight: 500;"><?php echo htmlspecialchars($equipo['nombre']); ?></span>
                </h2>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="notification <?php echo $clase_alerta; ?> custom-card theme-accent-border mb-5 py-3 has-text-white">
                    <button class="delete" onclick="this.parentElement.style.display='none';"></button>
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <div class="box custom-card p-5">
                <form action="" method="POST">
                    <div class="field mb-4">
                        <label class="label">Nombre del Activo / Hardware</label>
                        <div class="control">
                            <input class="input" type="text" name="nombre" value="<?php echo htmlspecialchars($equipo['nombre']); ?>" required>
                        </div>
                    </div>

                    <div class="columns">
                        <div class="column">
                            <div class="field mb-4">
                                <label class="label">Marca</label>
                                <div class="control">
                                    <input class="input" type="text" name="marca" value="<?php echo htmlspecialchars($equipo['marca']); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="column">
                            <div class="field mb-4">
                                <label class="label">Modelo Técnico</label>
                                <div class="control">
                                    <input class="input" type="text" name="modelo" value="<?php echo htmlspecialchars($equipo['modelo']); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="field is-grouped is-justify-content-flex-end mt-5">
                        <p class="control">
                            <a href="gestion_recursos.php" class="button is-light is-outlined" style="color: #fff; border-color: rgba(255,255,255,0.2);">Cancelar</a>
                        </p>
                        <p class="control">
                            <button type="submit" class="button btn-cyber-action">
                                <span class="icon"><i class="fa-solid fa-floppy-disk"></i></span>
                                <span>Guardar Cambios</span>
                            </button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

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
            color: 0x00e5ff,
            size: 1.1,
            transparent: true,
            opacity: 0.85,
            blending: THREE.AdditiveBlending
        });

        const savedColor = localStorage.getItem('selected-theme');
        if (savedColor === 'neon-purple') {
            material.color.setHex(0xff007f);
            document.documentElement.style.setProperty('--dynamic-core-color', '#ff007f');
        } else if (savedColor === 'matrix-green') {
            material.color.setHex(0x39ff14);
            document.documentElement.style.setProperty('--dynamic-core-color', '#39ff14');
        }

        const mouse = { x: 0, y: 0, targetX: 0, targetY: 0, radius: 22, activo: false };
        let mouseTimeout;

        window.addEventListener('mousemove', (event) => {
            mouse.activo = true;
            mouse.targetX = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.targetY = -(event.clientY / window.innerHeight) * 2 + 1;
            clearTimeout(mouseTimeout);
            mouseTimeout = setTimeout(() => { mouse.activo = false; }, 1200);
        });

        function inicializarTextoParticulas() {
            const textCanvas = document.createElement('canvas');
            const textCtx = textCanvas.getContext('2d');
            textCanvas.width = 700;  
            textCanvas.height = 250; 

            textCtx.font = 'bold 130px Impact, sans-serif';
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
            renderer.render(scene, camera);
        }

        setTimeout(inicializarTextoParticulas, 100);

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>
</html>