<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

            <?php 
            // Controller y Action actual para determinar navegación activa
            $currentController = strtolower((string)($_GET['c'] ?? $_GET['controller'] ?? 'dashboard'));
            $currentAction = strtolower((string)($_GET['a'] ?? $_GET['action'] ?? 'index'));

            // Helper para validar permisos cargados en sesión
            if (!function_exists('tienePermisoSidebar')) {
                function tienePermisoSidebar(string $permiso): bool {
                    $permisosSession = $_SESSION['permisos'] ?? [];
                    if (!is_array($permisosSession)) {
                        return false;
                    }
                    return in_array(strtoupper(trim($permiso)), $permisosSession, true);
                }
            }
            ?>

            <!-- 1. DASHBOARD / INICIO -->
            <?php if (tienePermisoSidebar('DASHBOARD')): ?>
                <li class="<?= ($currentController === 'dashboard') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=dashboard" aria-expanded="false">
                        <i class="fas fa-th-large"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="<?= ($currentController === 'dashboard_default' || $currentController === 'inicio') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=dashboard_default" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Inicio</span>
                    </a>
                </li>
            <?php endif; ?>
    
            <!-- 2. GESTIÓN DE ROLES -->
            <?php if (tienePermisoSidebar('ROLES')): ?>
                <li class="<?= ($currentController === 'roles') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=roles" aria-expanded="false">
                        <i class="fas fa-user-shield"></i>
                        <span class="nav-text">Roles</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 3. USUARIOS -->
            <?php if (tienePermisoSidebar('USUARIOS')): ?>
                <li class="<?= ($currentController === 'usuarios') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=usuarios" aria-expanded="false">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 4. TIPOS DE HABITACIÓN -->
            <?php if (tienePermisoSidebar('TIPOS_HABITACION')): ?>
                <li class="<?= in_array($currentController, ['tipos_habitacion', 'tiposhabitacion', 'tipos'], true) ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=tipos_habitacion" aria-expanded="false">
                        <i class="fas fa-layer-group"></i>
                        <span class="nav-text">Tipos Habitación</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 5. HABITACIONES -->
            <?php if (tienePermisoSidebar('HABITACIONES')): ?>
                <li class="<?= ($currentController === 'habitaciones') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=habitaciones" aria-expanded="false">
                        <i class="fas fa-door-open"></i>
                        <span class="nav-text">Habitaciones</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 6. CLIENTES / HUÉSPEDES -->
            <?php if (tienePermisoSidebar('CLIENTES')): ?>
                <li class="<?= ($currentController === 'clientes') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=clientes" aria-expanded="false">
                        <i class="fas fa-address-book"></i>
                        <span class="nav-text">Clientes</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 7. RESERVAS -->
            <?php if (tienePermisoSidebar('RESERVAS')): ?>
                <li class="<?= ($currentController === 'reservas') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=reservas" aria-expanded="false">
                        <i class="fas fa-calendar-check"></i>
                        <span class="nav-text">Reservas</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 8. ESTADÍAS (Check-In / Check-Out) -->
            <?php if (tienePermisoSidebar('ESTADIAS')): ?>
                <li class="<?= ($currentController === 'estadias') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=estadias" aria-expanded="false">
                        <i class="fas fa-concierge-bell"></i>
                        <span class="nav-text">Estadías</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 9. FACTURACIÓN Y COMPROBANTES SUNAT -->
            <?php if (tienePermisoSidebar('FACTURACION')): ?>
                <li class="<?= ($currentController === 'facturacion') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=facturacion" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="nav-text">Facturación</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>

        <div class="copyright">
            <p>Copyright © <?= date('Y') ?> Todos los derechos reservados</p>
            <p class="fs-12">Hecho por Team DD</p>
        </div>
    </div>
</div>