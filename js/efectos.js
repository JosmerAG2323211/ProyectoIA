// js/efectos.js

document.addEventListener("DOMContentLoaded", () => {
    // ==========================================================================
    // 1. Control del Switch de Neblina (Fondo)
    // ==========================================================================
    const fogToggle = document.getElementById('fog-toggle');
    const body = document.body;
    
    if (localStorage.getItem('nebula-fog-enabled') === 'false') {
        body.classList.remove('with-fog');
        if(fogToggle) fogToggle.checked = false;
    }
    
    if(fogToggle) {
        fogToggle.addEventListener('change', () => {
            if (fogToggle.checked) {
                body.classList.add('with-fog');
                localStorage.setItem('nebula-fog-enabled', 'true');
            } else {
                body.classList.remove('with-fog');
                localStorage.setItem('nebula-fog-enabled', 'false');
            }
        });
    }

    // ==========================================================================
    // 2. Renderizador 3D Avanzado de Partículas (AI.G en Impact)
    // ==========================================================================
    const container = document.getElementById('gan-canvas-container');
    if (!container) return; 

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.z = 60;

    const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    container.appendChild(renderer.domElement);

    // Variables globales para el sistema de partículas
    let geometry = new THREE.BufferGeometry();
    let particleCount = 0;
    let positions, originalPositions;
    let particleSystem;

    // Inicializamos el mouse interactivo
    const mouse = { x: 0, y: 0, targetX: 0, targetY: 0, radius: 22 };
    window.addEventListener('mousemove', (event) => {
        mouse.targetX = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.targetY = -(event.clientY / window.innerHeight) * 2 + 1;
    });

    // Material de partículas Premium (Azul Cian de alta densidad)
    const material = new THREE.PointsMaterial({
        color: 0x00e5ff, 
        size: 0.65, 
        transparent: true,
        opacity: 0.9,
        blending: THREE.AdditiveBlending
    });

    // Función principal para escanear y generar la tipografía Impact de manera segura
    function inicializarTextoParticulas() {
        const textCanvas = document.createElement('canvas');
        const textCtx = textCanvas.getContext('2d');
        textCanvas.width = 450;
        textCanvas.height = 150;

        // Cambiado el texto a "AI.G" con la fuente Impact forzada al extremo
        textCtx.font = 'bold 85px Impact, "Arial Black", sans-serif';
        textCtx.fillStyle = '#ffffff';
        textCtx.textAlign = 'center';
        textCtx.textBaseline = 'middle';
        textCtx.fillText('AI.G', textCanvas.width / 2, textCanvas.height / 2);

        const imgData = textCtx.getImageData(0, 0, textCanvas.width, textCanvas.height);
        const puntosValidos = [];

        const escalaX = 0.22;
        const escalaY = 0.22;

        // Escaneamos invirtiendo el eje X para que no salga en espejo (De derecha a izquierda)
        for (let y = 0; y < textCanvas.height; y += 2) {
            for (let x = 0; x < textCanvas.width; x += 2) {
                const index = (x + y * textCanvas.width) * 4;
                const alpha = imgData.data[index + 3];
                
                if (alpha > 128) { 
                    puntosValidos.push({
                        x: ((textCanvas.width - x) - textCanvas.width / 2) * escalaX,  
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

        // Arrancamos el bucle una vez creadas las partículas
        animate();
    }

    // Bucle de renderizado cinemático
    function animate() {
        requestAnimationFrame(animate);

        mouse.x += (mouse.targetX * 55 - mouse.x) * 0.08;
        mouse.y += (mouse.targetY * 30 - mouse.y) * 0.08;

        if (geometry && geometry.attributes.position) {
            const posAttr = geometry.attributes.position;
            
            for (let i = 0; i < particleCount; i++) {
                let idx = i * 3;
                let px = originalPositions[idx];
                let py = originalPositions[idx+1];

                let dx = mouse.x - px;
                let dy = mouse.y - py;
                let dist = Math.sqrt(dx * dx + dy * dy);

                if (dist < mouse.radius) {
                    let force = (mouse.radius - dist) / mouse.radius;
                    posAttr.array[idx] -= (dx / dist) * force * 2.2;
                    posAttr.array[idx+1] -= (dy / dist) * force * 2.2;
                } else {
                    posAttr.array[idx] += (px - posAttr.array[idx]) * 0.07;
                    posAttr.array[idx+1] += (py - posAttr.array[idx+1]) * 0.07;
                }
            }
            posAttr.needsUpdate = true;
        }
        
        if (particleSystem) {
            particleSystem.rotation.y = Math.sin(Date.now() * 0.0004) * 0.04;
            particleSystem.rotation.x = Math.cos(Date.now() * 0.0004) * 0.02;
        }

        renderer.render(scene, camera);
    }

    // El truco maestro: Esperamos 150ms para asegurar que el navegador cargó "Impact"
    setTimeout(() => {
        inicializarTextoParticulas();
    }, 150);

    // Ajuste elástico al cambiar el tamaño de la ventana
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
});