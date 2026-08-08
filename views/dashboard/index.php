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

        <!-- Sidebar start -->
        <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
        <!-- Sidebar end -->
        
        <!-- Content body start -->
        <div class="content-body">
            <div class="container-fluid">
                <!-- Header -->
                <div class="mb-sm-4 d-flex flex-wrap align-items-center text-head">
                    <h2 class="mb-3 me-auto">Dashboard Hotelero</h2>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Inicio</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Dashboard</a></li>
                        </ol>
                    </div>
                </div> 

                <!-- FILTRO DE FECHAS GLOBAL -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                                <h5 class="mb-0 fw-bold"><i class="fas fa-filter text-primary me-2"></i>Filtro de Estadías por Fecha de Check-out</h5>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <input type="date" id="fecha_inicio" class="form-control w-auto" value="<?php echo date('Y-m-d'); ?>">
                                    <span class="fw-bold">a</span>
                                    <input type="date" id="fecha_fin" class="form-control w-auto" value="<?php echo date('Y-m-d'); ?>">
                                    <button type="button" id="btn_filtrar" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARDS DE RESUMEN HOTELERO -->
                <div class="row">
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-start border-primary border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted fw-bold">TOTAL ESTADÍAS FINALIZADAS</span>
                                        <h2 class="mt-2 mb-0 fw-bold text-primary" id="lbl_total_ventas">0</h2>
                                    </div>
                                    <div class="bg-soft-primary p-3 rounded-circle">
                                        <i class="fas fa-door-open fs-1 text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-md-6 mb-4">
                        <div class="card border-start border-success border-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted fw-bold">INGRESO TOTAL RECAUDADO</span>
                                        <h2 class="mt-2 mb-0 fw-bold text-success" id="lbl_ingreso_total">S/ 0.00</h2>
                                    </div>
                                    <div class="bg-soft-success p-3 rounded-circle">
                                        <i class="fas fa-cash-register fs-1 text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE OCUPACIÓN ACTUAL DE HABITACIONES -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title fw-bold">Estado y Ocupación Actual de Habitaciones</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable_productos" class="table table-striped custom-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Nº Habitación</th>
                                                <th>Tipo</th>
                                                <th>Piso</th>
                                                <th>Disponibilidad</th>
                                                <th>Precio Noche</th>
                                                <th>Cliente Actual</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLA DE HISTORIAL DE IPs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-0 pb-0">
                                <h4 class="card-title fw-bold">Historial de Accesos e IPs Registradas</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable_ips" class="table table-striped custom-table w-100">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Nombre Completo</th>
                                                <th>Rol</th>
                                                <th>Dirección IP</th>
                                                <th>Fecha y Hora</th>
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

        <!-- MODAL DETALLE DE IP -->
        <div class="modal fade" id="modalIpDetalle" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Detalle del Registro de IP</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>ID Registro:</strong> <span id="det_ip_id">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Username:</strong> <span id="det_ip_usuario">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Nombre Completo:</strong> <span id="det_ip_nombre">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Correo:</strong> <span id="det_ip_correo">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Rol:</strong> <span id="det_ip_rol" class="badge bg-primary">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Dirección IP:</strong> <span id="det_ip_address" class="fw-bold text-dark">-</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Fecha de Acceso:</strong> <span id="det_ip_fecha">-</span>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer start -->
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
        <!-- Footer end -->

    </div>
    <!-- Main wrapper end -->

    <!-- Scripts -->
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>
    <script src="<?php echo BASE_URL; ?>/views/dashboard/dashboard.js"></script>
</body>
</html>