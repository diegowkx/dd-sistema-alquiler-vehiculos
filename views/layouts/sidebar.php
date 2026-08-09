<div class="deznav">
    <!-- REGLAS DE CSS PARA ANULAR EL NARANJA/AZUL NATIVO Y APLICAR HOVER EN ROJO -->
    <style>
        /* 1. RESET GENERAL DE NAVEGACIÓN */
        .deznav .metismenu li > a {
            background-color: transparent !important;
            color: #7e7e7e !important;
            transition: all 0.2s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
            padding: 10px 16px !important;
        }

        /* Estilo base para el contenedor del icono (MÁS GRANDE) */
        .deznav .metismenu li > a i {
            color: #7e7e7e !important;
            background: transparent !important;
            border: 2px solid transparent !important;
            border-radius: 50% !important;
            width: 52px !important;            /* Diámetro del círculo */
            height: 52px !important;           /* Diámetro del círculo */
            min-width: 52px !important;        /* Evita que se deforme */
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 24px !important;        /* Tamaño del icono */
            margin-right: 14px !important;
            box-shadow: none !important;
        }

        /* Forzar el tamaño del vector/glifo de FontAwesome */
        .deznav .metismenu li > a i::before {
            display: inline-block !important;
            opacity: 1 !important;
            visibility: visible !important;
            font-size: 24px !important;
        }

        /* 2. ESTADO HOVER / CLICK (Cápsula blanca con sombra, texto e icono rojo) */
        .deznav .metismenu li:not(.mm-active) > a:hover,
        .deznav .metismenu li:not(.mm-active) > a:focus,
        .deznav .metismenu li:not(.mm-active) > a:active {
            background-color: #ffffff !important;
            color: #d9383a !important;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.07) !important;
            border-radius: 40px !important;
        }

        .deznav .metismenu li:not(.mm-active) > a:hover .nav-text,
        .deznav .metismenu li:not(.mm-active) > a:focus .nav-text,
        .deznav .metismenu li:not(.mm-active) > a:active .nav-text {
            color: #d9383a !important;
            font-weight: 600 !important;
        }

        .deznav .metismenu li:not(.mm-active) > a:hover i,
        .deznav .metismenu li:not(.mm-active) > a:focus i,
        .deznav .metismenu li:not(.mm-active) > a:active i {
            background-color: #ffffff !important;
            color: #d9383a !important;
            border: 2px solid #d9383a !important;
        }

        /* 3. ESTADO ACTIVO SELECCIONADO (Pastilla roja suave #fce8e8) */
        .deznav .metismenu li.mm-active > a {
            background-color: #fce8e8 !important;
            color: #d9383a !important;
            border-radius: 40px !important;
        }

        .deznav .metismenu li.mm-active > a .nav-text {
            color: #d9383a !important;
            font-weight: 700 !important;
        }

        .deznav .metismenu li.mm-active > a i {
            background-color: #ffffff !important;
            color: #d9383a !important;
            border: 2px solid #d9383a !important;
        }

        /* 4. BLOQUEAR CAPAS Y PSEUDOELEMENTOS NATIVOS DE LA PLANTILLA */
        .deznav .metismenu a::before,
        .deznav .metismenu a::after {
            background-color: transparent !important;
            background: none !important;
            display: none !important;
        }
    </style>

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

            <!-- 4. TIPOS DE VEHÍCULOS -->
            <?php if (tienePermisoSidebar('TIPOS_VEHICULOS')): ?>
                <li class="<?= ($currentController === 'tipos_vehiculos' || $currentController === 'tipos_vehiculo') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=tipos_vehiculo" aria-expanded="false">
                        <i class="fas fa-tags"></i>
                        <span class="nav-text">Tipos Vehículos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 5. VEHÍCULOS -->
            <?php if (tienePermisoSidebar('VEHICULOS')): ?>
                <li class="<?= ($currentController === 'vehiculos') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=vehiculos" aria-expanded="false">
                        <i class="fas fa-car"></i>
                        <span class="nav-text">Vehículos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 6. CLIENTES (ARRENDATARIOS) -->
            <?php if (tienePermisoSidebar('CLIENTES')): ?>
                <li class="<?= ($currentController === 'clientes') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=clientes" aria-expanded="false">
                        <i class="fas fa-address-book"></i>
                        <span class="nav-text">Clientes</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 7. CONTRATOS -->
            <?php if (tienePermisoSidebar('CONTRATOS')): ?>
                <li class="<?= ($currentController === 'contratos') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=contratos" aria-expanded="false">
                        <i class="fas fa-file-contract"></i>
                        <span class="nav-text">Contratos</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 8. FACTURACIÓN -->
            <?php if (tienePermisoSidebar('FACTURACION')): ?>
                <li class="<?= ($currentController === 'comprobantes') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=comprobantes" aria-expanded="false">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span class="nav-text">Facturación</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- 9. CATÁLOGO -->
            <?php if (tienePermisoSidebar('CATALOGO')): ?>
                <li class="<?= ($currentController === 'catalogo') ? 'mm-active' : '' ?>">
                    <a class="ai-icon" href="?c=catalogo" aria-expanded="false">
                        <i class="fas fa-car-side"></i>
                        <span class="nav-text">Catálogo</span>
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