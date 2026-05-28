<button class="button is-link" id="toggle-sidebar-btn" style="position: fixed; top: 12px; left: 15px; z-index: 1000; background-color: var(--dynamic-core-color, #00e5ff) !important; color: #120d1e; border: none; font-weight: bold; box-shadow: 0 0 10px var(--dynamic-core-color);">
    <i class="fa-solid fa-bars"></i>
</button>

<div class="custom-sidebar" id="main-sidebar">
    <div class="sidebar-brand mb-5">
        <h3 class="title is-5 theme-accent-text" style="letter-spacing: 1px;">
            <i class="fa-solid fa-bolt mr-2"></i>MANTENIMIENTO RAG
        </h3>
        <hr style="background-color: rgba(255,255,255,0.08); height: 1px; margin: 15px 0;">
    </div>
    
    <p class="menu-label has-text-grey">Navegación</p>
    <ul class="menu-list">
        <li><a href="index.php" class="has-text-white"><i class="fa-solid fa-chart-line mr-2 theme-accent-text"></i> Panel Principal</a></li>
    </ul>

    <p class="menu-label has-text-grey">Operaciones</p>
    <ul class="menu-list">
        <li><a href="registrar_equipo.php" class="has-text-white"><i class="fa-solid fa-square-plus mr-2"></i> Registrar Equipo</a></li>
        <li><a href="reportar_falla.php" class="has-text-white"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Reportar Falla</a></li>
    </ul>

    <p class="menu-label has-text-grey">Administración</p>
    <ul class="menu-list">
        <li><a href="registrar_tecnico.php" class="has-text-white"><i class="fa-solid fa-user-gear mr-2"></i> Agregar Técnico</a></li>
    </ul>

    <p class="menu-label has-text-grey">Configuracion</p>
    <ul class="menu-list">
        <li><a href="gestion_recursos.php" class="has-text-white"><i class="fa-solid fa-sliders mr-2 theme-accent-text"></i> Gestión de Recursos</a></li>
    </ul>
</div>

<style>
    .custom-sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100vh;
        background-color: rgba(18, 13, 30, 0.96);
        backdrop-filter: blur(20px);
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        z-index: 999;
        padding: 80px 25px 25px 25px;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px rgba(0,0,0,0.5);
    }
    .custom-sidebar.is-active {
        left: 0;
    }
    .custom-sidebar .menu-list a {
        padding: 10px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    .custom-sidebar .menu-list a:hover {
        background-color: rgba(255, 255, 255, 0.04);
        color: var(--dynamic-core-color, #00e5ff) !important;
        padding-left: 18px;
    }
    /* Separar el navbar superior de Bulma para que el botón de menú encaje de forma balanceada */
    .navbar-brand {
        padding-left: 55px !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('main-sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar-btn');

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('is-active');
            
            const icon = toggleBtn.querySelector('i');
            if(sidebar.classList.contains('is-active')) {
                icon.className = 'fa-solid fa-xmark';
            } else {
                icon.className = 'fa-solid fa-bars';
            }
        });

        // Cerrar al clickear fuera
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('is-active') && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('is-active');
                toggleBtn.querySelector('i').className = 'fa-solid fa-bars';
            }
        });
    });
</script>