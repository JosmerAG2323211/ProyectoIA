<?php
// gestion_recursos.php
require_once 'config/conexion.php';

// 1. ESCUCHADOR DE ELIMINACIONES: Ya está configurado y procesa la baja directo en la BD
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] === 'eliminar_tecnico') {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol = 'tecnico'");
        $stmt->execute([$id]);
        header("Location: gestion_recursos.php?status=tecnico_deleted");
        exit;
    }
    
    if ($_GET['action'] === 'eliminar_equipo') {
        $stmt = $pdo->prepare("DELETE FROM equipos WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: gestion_recursos.php?status=equipo_deleted");
        exit;
    }
}

// 2. Consultar Equipos Registrados
$query_equipos = "SELECT id, nombre, marca, modelo FROM equipos ORDER BY id DESC";
$stmt_equipos = $pdo->query($query_equipos);
$equipos = $stmt_equipos->fetchAll();

// 3. Consultar Técnicos Registrados
$query_tecnicos = "SELECT id, nombre, correo FROM usuarios WHERE rol = 'tecnico' ORDER BY id DESC";
$stmt_tecnicos = $pdo->query($query_tecnicos);
$tecnicos = $stmt_tecnicos->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Recursos - Mantenimiento IA</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* FONDO EXCLUSIVO: Estilo Ciberpunk Cósmico Oscuro */
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

        /* Tarjetas translúcidas idénticas a tu captura */
        .custom-card {
            background-color: rgba(13, 20, 38, 0.65) !important;
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.45);
        }

        .title, .subtitle, th {
            color: #ffffff !important;
        }

        /* Tablas de alto contraste */
        .table {
            background-color: transparent !important;
            color: #cbd5e1 !important;
            width: 100%;
        }
        .table thead th {
            color: var(--dynamic-core-color, #00e5ff) !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1) !important;
            letter-spacing: 0.5px;
        }
        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            vertical-align: middle !important;
        }
        .table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        .table-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            width: 100%;
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

        /* Botones Ciberpunk */
        .btn-cyber-action {
            background-color: rgba(255, 255, 255, 0.03) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }
        .btn-cyber-action:hover {
            border-color: var(--dynamic-core-color) !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        .btn-cyber-action .icon {
            color: var(--dynamic-core-color) !important;
        }

        .btn-action-outline {
            background-color: rgba(255, 255, 255, 0.02) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #fff !important;
        }
        .btn-action-outline:hover {
            border-color: var(--dynamic-core-color) !important;
            background-color: rgba(255, 255, 255, 0.06) !important;
        }

        @media screen and (max-width: 768px) {
            .table td, .table th {
                font-size: 0.8rem !important;
                padding: 0.5em !important;
            }
        }
    </style>
</head>
<body>

    <?php include 'componentes/sidebar.php'; ?>
    
    <div id="gan-canvas-container"></div>
    
    <main class="section main-content-area">
        <div class="container" style="margin-top: 25px;">
            
            <div class="columns is-vcentered mb-6">
                <div class="column" style="padding-left: 10px;">
                    <h1 class="title is-2" style="letter-spacing: 2px; font-weight: 800; text-transform: uppercase;">Recursos</h1>
                    <h2 class="subtitle is-6 mt-1" style="color: #cbd5e1 !important; letter-spacing: 1px;">
                        Gestión Unificada de <span class="theme-accent-text" style="font-weight: 500;">Equipos y Personal Técnico</span>
                    </h2>
                </div>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="notification custom-card theme-accent-border mb-5 py-3 has-text-white">
                    <button class="delete" onclick="this.parentElement.style.display='none';"></button>
                    <?php 
                        if ($_GET['status'] === 'tecnico_deleted') echo '<i class="fa-solid fa-circle-check mr-2 has-text-danger"></i> Técnico desvinculado del sistema correctamente.';
                        if ($_GET['status'] === 'equipo_deleted') echo '<i class="fa-solid fa-circle-check mr-2 has-text-danger"></i> Equipo e historial asociado eliminados.';
                        if ($_GET['status'] === 'equipo_updated') echo '<i class="fa-solid fa-circle-check mr-2 theme-accent-text"></i> Características del equipo actualizadas con éxito.';
                        if ($_GET['status'] === 'tecnico_updated') echo '<i class="fa-solid fa-circle-check mr-2 theme-accent-text"></i> Datos del especialista sincronizados correctamente.';
                    ?>
                </div>
            <?php endif; ?>

            <div class="columns is-desktop">
                
                <div class="column is-6-desktop">
                    <div class="box custom-card p-5">
                        <div class="level is-mobile mb-4">
                            <div class="level-left">
                                <h3 class="title is-4 mb-0">
                                    <i class="fa-solid fa-microchip mr-2 theme-accent-text"></i> Equipos
                                </h3>
                            </div>
                            <div class="level-right">
                                <a href="registrar_equipo.php" class="button is-small btn-cyber-action">
                                    <span class="icon"><i class="fa-solid fa-plus"></i></span>
                                    <span>Añadir</span>
                                </a>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="table is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>Hardware</th>
                                        <th>Marca / Modelo</th>
                                        <th class="has-text-centered">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($equipos)): ?>
                                        <tr>
                                            <td colspan="3" class="has-text-centered has-text-grey py-4">No hay hardware mapeado.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($equipos as $eq): ?>
                                            <tr>
                                                <td>
                                                    <strong class="has-text-white"><?php echo htmlspecialchars($eq['nombre']); ?></strong>
                                                    <p class="is-size-7 has-text-grey">ID Ref: #<?php echo $eq['id']; ?></p>
                                                </td>
                                                <td class="is-size-7">
                                                    <span class="has-text-grey-light"><?php echo htmlspecialchars($eq['marca']); ?></span>
                                                    <p style="color: #cbd5e1;"><?php echo htmlspecialchars($eq['modelo']); ?></p>
                                                </td>
                                                <td class="has-text-centered">
                                                    <div class="is-flex is-justify-content-center">
                                                        <a href="editar_equipo.php?id=<?php echo $eq['id']; ?>" class="button is-small btn-action-outline mr-2" title="Editar">
                                                            <i class="fa-regular fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="gestion_recursos.php?action=eliminar_equipo&id=<?php echo $eq['id']; ?>" 
                                                           class="button is-small is-danger is-outlined" 
                                                           onclick="return confirm('¿Eliminar equipo definitivamente? Esto afectará los reportes ligados.');">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="column is-6-desktop">
                    <div class="box custom-card p-5">
                        <div class="level is-mobile mb-4">
                            <div class="level-left">
                                <h3 class="title is-4 mb-0">
                                    <i class="fa-solid fa-user-shield mr-2 theme-accent-text"></i> Especialistas
                                </h3>
                            </div>
                            <div class="level-right">
                                <a href="registrar_tecnico.php" class="button is-small btn-cyber-action">
                                    <span class="icon"><i class="fa-solid fa-user-plus"></i></span>
                                    <span>Asignar</span>
                                </a>
                            </div>
                        </div>

                        <div class="table-container">
                            <table class="table is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>Nombre Técnico</th>
                                        <th>Credencial de Enlace</th>
                                        <th class="has-text-centered">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($tecnicos)): ?>
                                        <tr>
                                            <td colspan="3" class="has-text-centered has-text-grey py-4">No hay especialistas registrados.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($tecnicos as $tec): ?>
                                            <tr>
                                                <td>
                                                    <strong class="has-text-white"><?php echo htmlspecialchars($tec['nombre']); ?></strong>
                                                    <p class="is-size-7 has-text-grey">Operador #<?php echo $tec['id']; ?></p>
                                                </td>
                                                <td class="is-size-7" style="color: #cbd5e1;"><?php echo htmlspecialchars($tec['correo']); ?></td>
                                                <td class="has-text-centered">
                                                    <div class="is-flex is-justify-content-center">
                                                        <a href="editar_tecnico.php?id=<?php echo $tec['id']; ?>" class="button is-small btn-action-outline mr-2" title="Modificar">
                                                            <i class="fa-solid fa-user-gear"></i>
                                                        </a>
                                                        <a href="gestion_recursos.php?action=eliminar_tecnico&id=<?php echo $tec['id']; ?>" 
                                                           class="button is-small is-danger is-outlined" 
                                                           onclick="return confirm('¿Dar de baja a este especialista del panel activo?');">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

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