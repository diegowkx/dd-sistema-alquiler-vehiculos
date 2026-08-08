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
                        <div class="card">
                            <div class="card-header d-flex flex-column align-items-start gap-3">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <h4 class="card-title mb-0">Gestión de Reservas</h4>
                                    <button type="button" class="btn btn-primary btn-nueva-reserva">
                                        <i class="fas fa-plus me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nueva Reserva</span>
                                    </button>
                                </div>

                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-reservas" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-3 py-2 tab-estado fw-bold" data-estado="CONFIRMADA" type="button">
                                                <i class="fas fa-calendar-check me-1"></i> Confirmadas
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="CHECKED_IN" type="button">
                                                <i class="fas fa-sign-in-alt me-1"></i> Check-In
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="CANCELADA" type="button">
                                                <i class="fas fa-ban me-1"></i> Canceladas
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="NO_SHOW" type="button">
                                                <i class="fas fa-user-slash me-1"></i> No-Show
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable_reservas" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th>Habitación</th>
                                                <th>Check-In</th>
                                                <th>Check-Out</th>
                                                <th>Adelanto</th>
                                                <th>Estado</th>
                                                <th>Registrado por</th>
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

    <script src="<?php echo BASE_URL; ?>/views/reservas/reservas.js"></script>
</body>
</html>