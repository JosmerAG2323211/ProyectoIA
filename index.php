<?php
// index.php
require_once 'config/conexion.php';

// Consulta corregida para traer el equipo, el creador y el técnico asignado mediante LEFT JOIN
$query = "SELECT r.*, 
                 e.nombre AS equipo, 
                 u.nombre AS usuario_reporta, 
                 t.nombre AS tecnico 
          FROM reportes_fallas r
          JOIN equipos e ON r.equipo_id = e.id
          JOIN usuarios u ON r.usuario_id = u.id
          LEFT JOIN usuarios t ON r.tecnico_id = t.id
          ORDER BY r.creado_en DESC";
$stmt = $pdo->query($query);
$reportes = $stmt->fetchAll();

// Conteo rápido para las tarjetas de métricas
$total_fallas = count($reportes);
$criticas = 0;
$abiertas = 0;
foreach ($reportes as $r) {
    if ($r['prioridad'] === 'Crítica') $criticas++;
    if ($r['estado'] === 'Abierto') $abiertas++;
}
?>
<!DOCTYPE html>
<html lang="es" class="has-navbar-fixed-top">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mantenimiento IA</title>
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            background-color: #120d1e;
            min-height: 100vh;
            overflow-x: hidden;
        }
        #gan-canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
            opacity: 0.25;
        }
        .main-content-area {
            position: relative;
            z-index: 2;
            padding: 1.5rem 1rem;
        }
        
        /* Efecto de tarjeta ciberpunk translúcido unificado */
        .custom-card {
            background-color: rgba(26, 21, 44, 0.45) !important;
            backdrop-filter: blur(12px) saturate(160%);
            -webkit-backdrop-filter: blur(12px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 12px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .custom-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        /* Estilos de Tabla e Historial Adaptable */
        .table {
            background-color: transparent !important;
            color: #f5f5f5 !important;
            width: 100%;
        }
        .table thead th {
            color: var(--dynamic-core-color, #00e5ff) !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1) !important;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            vertical-align: middle !important;
        }
        
        /* Contenedor con scroll táctil para la tabla */
        .table-container {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 10px;
        }

        .input, .select select {
            background-color: rgba(255,255,255,0.04) !important;
            border-color: rgba(255,255,255,0.08) !important;
            color: #fff !important;
        }

        #theme-selector option {
            background-color: #1a152c !important;
            color: #ffffff !important;
        }
        
        :root {
            --dynamic-core-color: #00e5ff;
        }
        .theme-accent-text {
            color: var(--dynamic-core-color) !important;
            transition: color 0.3s ease;
        }
        .theme-accent-border {
            border-left: 4px solid var(--dynamic-core-color) !important;
            transition: border-color 0.3s ease;
        }
        .theme-accent-bg {
            background-color: var(--dynamic-core-color) !important;
            color: #120d1e !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Textos del encabezado principal unificados */
        .cyber-title {
            letter-spacing: 2px;
            font-weight: 800;
            text-transform: uppercase;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }
        
        /* CORRECCIÓN: Contraste de subtítulo mejorado y adaptativo */
        .cyber-subtitle {
            letter-spacing: 1px;
            color: #cbd5e1 !important;
            font-weight: 400;
        }
        .cyber-subtitle span {
            color: var(--dynamic-core-color);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        /* Botones de acción translúcidos ciberpunk */
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
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.05);
            transform: translateY(-1px);
        }
        .btn-cyber-action .icon {
            color: var(--dynamic-core-color) !important;
            transition: color 0.3s ease;
        }

        .btn-editar-custom {
            background-color: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-editar-custom:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--dynamic-core-color) !important;
        }

        @media screen and (max-width: 1023px) {
            /* CORRECCIÓN: Contenedor del selector móvil para que no flote suelto */
            .navbar-menu {
                background-color: rgba(20, 16, 36, 0.95) !important;
                backdrop-filter: blur(15px);
                padding: 1rem;
                box-shadow: 0 8px 16px rgba(0,0,0,0.5);
            }
            .navbar-item .field, .navbar-item .control, .navbar-item .select {
                width: 100% !important;
            }
            .navbar-item .select select {
                width: 100% !important;
                height: 2.5rem !important;
                background: rgba(30, 25, 50, 0.8) !important;
            }
        }

        @media screen and (max-width: 768px) {
            .cyber-title {
                font-size: 1.75rem !important;
                text-align: center;
            }
            .cyber-subtitle {
                font-size: 0.85rem !important;
                text-align: center;
                margin-bottom: 1rem;
            }
            .buttons {
                justify-content: center !important;
            }
            .btn-cyber-action {
                width: 100%;
            }
            .table-container {
                margin: 0 -0.5rem;
                padding-bottom: 0.75rem;
            }
            .table td, .table th {
                font-size: 0.8rem !important;
                padding: 0.5em 0.6em !important;
            }
            #ai-chat-window {
                width: calc(100% - 30px) !important;
                right: 15px !important;
                left: 15px !important;
                bottom: 85px !important;
                height: 440px !important;
            }
        }
    </style>
</head>
<body>
    <script>
    // Esto se ejecuta antes de que se pinte la página
    (function() {
        document.documentElement.setAttribute('data-theme', 'dark');
        
        // Opcional: Esto también asegura que el scrollbar se mantenga oscuro
        const metaThemeColor = document.createElement('meta');
        metaThemeColor.name = "theme-color";
        metaThemeColor.content = "#070a13";
        document.head.appendChild(metaThemeColor);
    })();
    </script>
    <?php include 'componentes/sidebar.php'; ?>
    
    <div id="gan-canvas-container"></div>
    
    <nav class="navbar is-fixed-top" role="navigation" aria-label="main navigation" style="background: rgba(20, 16, 36, 0.75) !important; backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);">
        <div class="container">
            <div class="navbar-brand">
                <a class="navbar-item has-text-weight-bold theme-accent-text" href="index.php" style="font-size: 1.25rem; letter-spacing: 1px;">
                    <i class="fa-solid fa-bolt mr-2"></i> MANTENIMIENTO RAG
                </a>
                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarCyber">
                    <span aria-hidden="true" style="color: #fff;"></span>
                    <span aria-hidden="true" style="color: #fff;"></span>
                    <span aria-hidden="true" style="color: #fff;"></span>
                </a>
            </div>

            <div id="navbarCyber" class="navbar-menu">
                <div class="navbar-end is-flex is-align-items-center pr-4">
                    <div class="navbar-item" style="width: 100%;">
                        <div class="field mb-0" style="width: 100%;">
                            <div class="control has-icons-left">
                                <div class="select is-small is-rounded">
                                    <select id="theme-selector" style="background: #1a152c; border-color: rgba(255,255,255,0.2); color: #fff;">
                                        <option value="cyan">Géneris Cyan</option>
                                        <option value="neon-purple">Núcleo Fucsia</option>
                                        <option value="matrix-green">Núcleo Matriz</option>
                                    </select>
                                </div>
                                <span class="icon is-small is-left theme-accent-text">
                                    <i class="fa-solid fa-palette"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="section main-content-area">
        <div class="container">
            
            <div class="columns is-vcentered mb-6">
                <div class="column">
                    <h1 class="title has-text-white is-2 cyber-title">Panel Principal</h1>
                    <h2 class="subtitle is-6 mt-1 cyber-subtitle">Bitácora Técnica e <span>Integración Inteligente</span></h2>
                </div>
                <div class="column is-narrow">
                    <div class="buttons">
                        <a href="registrar_equipo.php" class="button btn-cyber-action">
                            <span class="icon"><i class="fa-solid fa-plus"></i></span>
                            <span>Registrar Equipo</span>
                        </a>
                        <a href="reportar_falla.php" class="button btn-cyber-action">
                            <span class="icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                            <span>Reportar Falla</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] === 'deleted'): ?>
                    <div class="notification is-success custom-card theme-accent-border mb-4 py-3">
                        <i class="fa-solid fa-circle-check mr-2"></i> Reporte técnico removido exitosamente del sistema.
                    </div>
                <?php elseif($_GET['status'] === 'error'): ?>
                    <div class="notification is-danger custom-card mb-4 py-3">
                        <i class="fa-solid fa-circle-xmark mr-2"></i> Error operacional: No se pudo eliminar el reporte solicitado.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="columns mb-6">
                <div class="column">
                    <div class="custom-card p-5 has-text-centered">
                        <p class="heading has-text-grey-light">Total Reportes</p>
                        <p class="title has-text-white is-3 mb-0"><?php echo $total_fallas; ?></p>
                    </div>
                </div>
                <div class="column">
                    <div class="custom-card p-5 has-text-centered" style="border-left: 4px solid #ff3860;">
                        <p class="heading has-text-danger-light">Fallas Críticas (IA)</p>
                        <p class="title has-text-danger is-3 mb-0"><?php echo $criticas; ?></p>
                    </div>
                </div>
                <div class="column">
                    <div class="custom-card p-5 has-text-centered theme-accent-border">
                        <p class="heading has-text-info-light">Casos Activos</p>
                        <p class="title theme-accent-text is-3 mb-0"><?php echo $abiertas; ?></p>
                    </div>
                </div>
            </div>

            <div class="custom-card p-5">
                <div class="level mb-4 is-mobile is-flex-wrap-wrap">
                    <div class="level-left mb-2">
                        <h3 class="title is-4" style="color:#fff; letter-spacing: 0.5px;">Historial de Reportes</h3>
                    </div>
                    <div class="level-right">
                        <div class="field">
                            <p class="control has-icons-left">
                                <input id="tabla-buscador" class="input is-small is-rounded" type="text" placeholder="Buscar...">
                                <span class="icon is-small is-left theme-accent-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="tabla-reportes" class="table is-hoverable is-fullwidth">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Equipo</th>
                                <th>Técnico</th> 
                                <th>Descripción de Falla</th>
                                <th class="has-text-centered">Prioridad IA</th>
                                <th>Diagnóstico Generado</th>
                                <th>Estado</th>
                                <th class="has-text-centered">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportes)): ?>
                                <tr class="no-data-row">
                                    <td colspan="8" class="has-text-centered py-6">
                                        <span class="icon is-large has-text-grey mb-3" style="font-size: 2rem;">
                                            <i class="fa-regular fa-folder-open"></i>
                                        </span>
                                        <p class="has-text-grey mt-2">No hay fallas reportadas todavía.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportes as $rep): ?>
                                    <tr>
                                        <td class="is-size-7"><?php echo date('d/m/Y H:i', strtotime($rep['creado_en'])); ?></td>
                                        <td><strong class="has-text-white"><?php echo htmlspecialchars($rep['equipo']); ?></strong></td>
                                        <td class="has-text-grey-light"><?php echo !empty($rep['tecnico']) ? htmlspecialchars($rep['tecnico']) : '<i>Sin asignar</i>'; ?></td>
                                        <td class="is-size-7"><?php echo htmlspecialchars($rep['descripcion_falla']); ?></td>
                                        <td class="has-text-centered">
                                            <?php 
                                            $tag_class = 'is-light';
                                            if($rep['prioridad'] === 'Crítica') $tag_class = 'is-danger';
                                            if($rep['prioridad'] === 'Urgente') $tag_class = 'is-warning';
                                            if($rep['prioridad'] === 'Rutinaria') $tag_class = 'is-success is-light';
                                            ?>
                                            <span class="tag <?php echo $tag_class; ?> is-rounded"><?php echo htmlspecialchars($rep['prioridad']); ?></span>
                                        </td>
                                        <td class="is-size-7"><?php echo htmlspecialchars($rep['diagnostico_ia'] ?? 'No generado'); ?></td>
                                        <td>
                                            <span class="tag <?php echo ($rep['estado'] === 'Abierto') ? 'is-info' : 'is-dark'; ?>">
                                                <?php echo htmlspecialchars($rep['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="has-text-centered">
                                            <div class="is-flex is-justify-content-center">
                                                <a href="editar_reporte.php?id=<?php echo $rep['id']; ?>" class="button is-small btn-editar-custom theme-accent-text mr-2">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                <a href="eliminar_reporte.php?id=<?php echo $rep['id']; ?>" class="button is-small is-danger is-outlined" onclick="return confirm('¿Eliminar reporte?');">
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
    </main>

    <button id="ai-assistant-btn" class="button is-large theme-accent-bg" style="position: fixed; bottom: 25px; right: 25px; z-index: 99; border-radius: 50%; width: 60px; height: 60px; box-shadow: 0 4px 20px rgba(0, 229, 255, 0.3); border: 2px solid rgba(255,255,255,0.2);">
        <span class="icon is-medium"><i class="fa-solid fa-robot fa-lg"></i></span>
    </button>

    <div id="ai-chat-window" class="box custom-card" style="position: fixed; bottom: 95px; right: 25px; width: calc(100% - 50px); max-width: 380px; height: 500px; z-index: 98; display: none; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.6); border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden; padding: 0;">
        <div style="background: rgba(255, 255, 255, 0.03); padding: 12px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05);" class="is-flex is-align-items-center is-justify-content-space-between">
            <div class="is-flex is-align-items-center">
                <span class="icon theme-accent-text mr-2"><i class="fa-solid fa-brain"></i></span>
                <div>
                    <p class="has-text-white has-text-weight-bold is-size-6 mb-0">Asistente de Diagnóstico</p>
                    <p class="has-text-success is-size-7 mb-0"><i class="fa-solid fa-circle is-size-7 mr-1"></i> Motor RAG Conectado</p>
                </div>
            </div>
            <button id="close-chat" class="delete is-small"></button>
        </div>

        <div id="chat-messages" style="flex-grow: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
            <div style="align-self: flex-start; background: rgba(255,255,255,0.05); color: #f5f5f5; padding: 10px 14px; border-radius: 4px 14px 14px 14px; max-width: 85%; font-size: 0.85rem;">
                Saludos, técnico. Tengo acceso a los manuales y al historial de fallas. ¿En qué módulo o diagnóstico necesitas asistencia hoy?
            </div>
        </div>

        <div style="padding: 15px; background: rgba(0,0,0,0.2); border-top: 1px solid rgba(255,255,255,0.05);">
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input id="chat-input" class="input is-small is-rounded" type="text" placeholder="Pregunta sobre una falla o equipo...">
                </div>
                <div class="control">
                    <button id="send-chat-btn" class="button is-small is-rounded theme-accent-bg">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <script>
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
            color: 0x00e5ff,
            size: 1.1,
            transparent: true,
            opacity: 0.9,
            blending: THREE.AdditiveBlending
        });

        // Cargar tema inicial guardado
        const savedColor = localStorage.getItem('selected-theme');
        if (savedColor === 'neon-purple') {
            material.color.setHex(0xff007f);
            document.documentElement.style.setProperty('--dynamic-core-color', '#ff007f');
            document.getElementById('theme-selector').value = 'neon-purple';
        } else if (savedColor === 'matrix-green') {
            material.color.setHex(0x39ff14);
            document.documentElement.style.setProperty('--dynamic-core-color', '#39ff14');
            document.getElementById('theme-selector').value = 'matrix-green';
        } else {
            document.getElementById('theme-selector').value = 'cyan';
        }

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

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });

        // --- CONTROL DEL ASISTENTE DE IA (CHAT) ---
        const aiBtn = document.getElementById('ai-assistant-btn');
        const chatWindow = document.getElementById('ai-chat-window');
        const closeChat = document.getElementById('close-chat');
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-chat-btn');
        const chatMessages = document.getElementById('chat-messages');

        aiBtn.addEventListener('click', () => {
            chatWindow.style.display = (chatWindow.style.display === 'none' || chatWindow.style.display === '') ? 'flex' : 'none';
        });
        closeChat.addEventListener('click', () => chatWindow.style.display = 'none');

        function enviarMensaje() {
            const texto = chatInput.value.trim();
            if(texto === "") return;

            const userDiv = document.createElement('div');
            userDiv.style = "align-self: flex-end; background: var(--dynamic-core-color); color: #120d1e; padding: 10px 14px; border-radius: 14px 14px 4px 14px; max-width: 85%; font-size: 0.85rem; font-weight: 500; transition: background-color 0.3s; margin-bottom: 10px;";
            userDiv.textContent = texto;
            chatMessages.appendChild(userDiv);
            
            chatInput.value = "";
            chatInput.disabled = true;
            sendBtn.disabled = true;
            chatMessages.scrollTop = chatMessages.scrollHeight;

            const loadingDiv = document.createElement('div');
            loadingDiv.style = "align-self: flex-start; background: rgba(255,255,255,0.05); color: #f5f5f5; padding: 10px 14px; border-radius: 4px 14px 14px 14px; max-width: 85%; font-size: 0.85rem; border-left: 2px solid var(--dynamic-core-color); margin-bottom: 10px;";
            loadingDiv.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> Procesando vector... Analizando manual técnico para: "<em>${texto}</em>".`;
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            fetch('ajax_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje: texto })
            })
            .then(response => {
                if (!response.ok) throw new Error("Fallo en la respuesta del servidor.");
                return response.json();
            })
            .then(data => {
                chatMessages.removeChild(loadingDiv);
                const iaDiv = document.createElement('div');
                iaDiv.style = "align-self: flex-start; background: rgba(255,255,255,0.05); color: #f5f5f5; padding: 10px 14px; border-radius: 4px 14px 14px 14px; max-width: 85%; font-size: 0.85rem; border-left: 2px solid var(--dynamic-core-color); margin-bottom: 10px; white-space: pre-wrap; line-height: 1.5;";
                iaDiv.innerHTML = `<i class="fa-solid fa-robot mr-1" style="color: var(--dynamic-core-color);"></i> ${data.respuesta}`;
                chatMessages.appendChild(iaDiv);
            })
            .catch(error => {
                if (chatMessages.contains(loadingDiv)) chatMessages.removeChild(loadingDiv);
                const errorDiv = document.createElement('div');
                errorDiv.style = "align-self: flex-start; background: rgba(255, 56, 96, 0.1); color: #ff3860; padding: 10px 14px; border-radius: 4px 14px 14px 14px; max-width: 85%; font-size: 0.85rem; border-left: 2px solid #ff3860; margin-bottom: 10px;";
                errorDiv.innerHTML = `<i class="fa-solid fa-triangle-exclamation mr-1"></i> Error de enlace vectorial con el servidor PHP.`;
                chatMessages.appendChild(errorDiv);
            })
            .finally(() => {
                chatInput.disabled = false;
                sendBtn.disabled = false;
                chatInput.focus();
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
        }

        sendBtn.addEventListener('click', enviarMensaje);
        chatInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') enviarMensaje(); });


        // --- CAMBIADOR DE COLOR DINÁMICO COMPLETO ---
        const themeSelector = document.getElementById('theme-selector');
        if(themeSelector) {
            themeSelector.addEventListener('change', (e) => {
                const color = e.target.value;
                let hexColor = 0x00e5ff; 
                let cssColor = '#00e5ff';

                if (color === 'neon-purple') {
                    hexColor = 0xff007f;
                    cssColor = '#ff007f';
                } else if (color === 'matrix-green') {
                    hexColor = 0x39ff14;
                    cssColor = '#39ff14';
                }

                material.color.setHex(hexColor);
                document.documentElement.style.setProperty('--dynamic-core-color', cssColor);
                localStorage.setItem('selected-theme', color);
            });
        }

        // --- MOTOR DE BÚSQUEDA REACTIVO EN TIEMPO REAL ---
        const buscador = document.getElementById('tabla-buscador');
        const tabla = document.getElementById('tabla-reportes');
        
        if (buscador && tabla) {
            buscador.addEventListener('keyup', function() {
                const term = this.value.toLowerCase();
                const rows = tabla.querySelectorAll('tbody tr:not(.no-data-row)');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // --- HAMBURGUESA BÁSICA PARA MÓVILES ---
        document.addEventListener('DOMContentLoaded', () => {
            const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
            if ($navbarBurgers.length > 0) {
                $navbarBurgers.forEach( el => {
                    el.addEventListener('click', () => {
                        const target = el.dataset.target;
                        const $target = document.getElementById(target);
                        el.classList.toggle('is-active');
                        $target.classList.toggle('is-active');
                    });
                });
            }
        });
    </script>
</body>
</html>