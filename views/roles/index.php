<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head/head_meta.php'; ?>
    <?php include __DIR__ . '/../layouts/head/head_css.php'; ?>
</head>
<body>

    <!-- Preloader start -->
    <?php include __DIR__ . '/../layouts/preloader.php'; ?>
    <!-- Preloader end -->

    <!-- Main wrapper start -->
    <div id="main-wrapper">

        <?php include __DIR__ . '/../layouts/topbar.php'; ?>
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

        <!-- Content body start -->
        <div class="content-body">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <!-- Card Header -->
                            <div class="card-header d-flex flex-column align-items-start gap-3">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <h4 class="card-title mb-0">Gestión de Roles y Permisos</h4>
                                    <button type="button" class="btn btn-primary btn-nuevo-rol">
                                        <i class="fas fa-plus me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nuevo Rol</span>
                                    </button>
                                </div>

                                <!-- Tabs activos / inactivos -->
                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-roles" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-3 py-2 tab-estado fw-bold" data-estado="ACTIVO" type="button" style="font-size: 0.95rem;">
                                                <i class="fas fa-user-shield me-1"></i> Roles Activos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="INACTIVO" type="button" style="font-size: 0.95rem;">
                                                <i class="fas fa-user-slash me-1"></i> Roles Inactivos
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
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
        <!-- Content body end -->

        <?php include __DIR__ . '/../layouts/footer.php'; ?>

    </div>
    <!-- Main wrapper end -->

    <?php include __DIR__ . '/../layouts/scripts.php'; ?>

    <!-- Modal de Roles -->
    <?php include __DIR__ . '/form_modal.php'; ?>

    <!-- Script JS de Roles -->
    <script src="<?php echo BASE_URL; ?>/views/roles/rol.js"></script>
</body>
</html>