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
                    <h2 class="mb-3 me-auto">Panel de Inicio - Recepción</h2>
                    <div>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Inicio</a></li>
                            <li class="breadcrumb-item active"><a href="javascript:void(0)">Dashboard</a></li>
                        </ol>
                    </div>
                </div> 

                <!-- TARJETA DE BIENVENIDA -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card bg-primary text-white shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h3 class="text-white fw-bold mb-2">¡Bienvenido al Sistema Hotelero!</h3>
                                        <p class="mb-0 text-white-50 fs-6">
                                            Utiliza el menú lateral para gestionar las reservas, registrar entradas/salidas (check-in / check-out) y administrar los servicios de las habitaciones.
                                        </p>
                                    </div>
                                    <div class="bg-white p-3 rounded-circle text-primary">
                                        <i class="fas fa-concierge-bell fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCESOS RÁPIDOS OPCIONALES -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="bg-soft-primary text-primary p-3 rounded-circle d-inline-flex mb-3" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                                    <i class="fas fa-calendar-check fs-4"></i>
                                </div>
                                <h5 class="fw-bold">Gestión de Reservas</h5>
                                <p class="text-muted small">Consulta el estado de las reservas actuales y futuras de los huéspedes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="bg-soft-success text-success p-3 rounded-circle d-inline-flex mb-3" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                                    <i class="fas fa-bed fs-4"></i>
                                </div>
                                <h5 class="fw-bold">Estado de Habitaciones</h5>
                                <p class="text-muted small">Revisa rápidamente cuáles habitaciones se encuentran disponibles o limpiándose.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="bg-soft-info text-info p-3 rounded-circle d-inline-flex mb-3" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                                    <i class="fas fa-user-friends fs-4"></i>
                                </div>
                                <h5 class="fw-bold">Registro de Clientes</h5>
                                <p class="text-muted small">Visualiza y da de alta a los huéspedes que ingresan al establecimiento.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- Content body end -->

        <!-- Footer start -->
        <?php include __DIR__ . '/../layouts/footer.php'; ?>
        <!-- Footer end -->

    </div>
    <!-- Main wrapper end -->

    <!-- Scripts -->
    <?php include __DIR__ . '/../layouts/scripts.php'; ?>
    <script src="<?php echo BASE_URL; ?>/views/dashboard_default/dashboard_default.js"></script>
</body>
</html>