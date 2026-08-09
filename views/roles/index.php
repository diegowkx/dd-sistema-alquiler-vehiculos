<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head/head_meta.php'; ?>
    <?php include __DIR__ . '/../layouts/head/head_css.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../layouts/preloader.php'; ?>

    <div id="main-wrapper">
        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

        <div class="content-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                            
                            <!-- Cabecera de la vista con línea divisoria roja suave -->
                            <div class="card-header d-flex flex-column align-items-start gap-3 bg-white pt-4 px-4 pb-3 border-0" style="border-bottom: 2px solid #fce8e8 !important;">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <h4 class="card-title mb-0 fw-bold text-dark">Gestión de Roles y Permisos</h4>
                                    <button type="button" class="btn text-white btn-nuevo-rol rounded-pill px-4 py-2 fw-semibold" style="background-color: #d9383a; border: none;">
                                        <i class="fas fa-plus me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nuevo Rol</span>
                                    </button>
                                </div>

                                <!-- Pestañas de estado de roles -->
                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-roles" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-4 py-2 tab-estado fw-bold rounded-pill" data-estado="ACTIVO" type="button" style="background-color: #d9383a; color: #ffffff;">
                                                <i class="fas fa-user-shield me-1"></i> Roles Activos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-4 py-2 tab-estado fw-bold rounded-pill" data-estado="INACTIVO" type="button" style="background-color: #f1f5f9; color: #64748b;">
                                                <i class="fas fa-user-slash me-1"></i> Roles Inactivos
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="datatable_roles" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre del Rol</th>
                                                <th>Módulos con Acceso</th>
                                                <th>Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <?php include __DIR__ . '/../layouts/scripts.php'; ?>

    <?php include __DIR__ . '/form_modal.php'; ?>

    <script src="<?php echo BASE_URL; ?>/views/roles/rol.js"></script>
</body>
</html>