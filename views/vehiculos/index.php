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
                                    <h4 class="card-title mb-0 fw-bold text-dark">Gestión de Vehículos</h4>
                                    <button type="button" class="btn text-white btn-nuevo-vehiculo rounded-pill px-4 py-2 fw-semibold" style="background-color: #d9383a; border: none;">
                                        <i class="fas fa-plus me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nuevo Vehículo</span>
                                    </button>
                                </div>

                                <!-- Pestañas de estado con estilo en línea directo -->
                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-vehiculos" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-4 py-2 tab-estado fw-bold rounded-pill" data-estado="ACTIVO" type="button" style="background-color: #d9383a; color: #ffffff;">
                                                <i class="fas fa-car me-1"></i> Activos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-4 py-2 tab-estado fw-bold rounded-pill" data-estado="INACTIVO" type="button" style="background-color: #f1f5f9; color: #64748b;">
                                                <i class="fas fa-car-crash me-1"></i> Inactivos
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <table id="datatable_vehiculos" class="table table-striped table-hover align-middle w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="min-width: 50px;">Img</th>
                                            <th>Placa</th>
                                            <th>Marca / Modelo</th>
                                            <th class="text-center">Tipo</th>
                                            <th>Color</th>
                                            <th class="text-center">Año</th>
                                            <th class="text-end">KM</th>
                                            <th class="text-end">Precio/Día</th>
                                            <th class="text-center">Estado</th>
                                            <th style="min-width: 150px;">Observaciones</th>
                                            <th class="text-center" style="min-width: 130px;">Acciones</th>
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

        <?php include __DIR__ . '/../layouts/footer.php'; ?>
    </div>

    <?php include __DIR__ . '/../layouts/scripts.php'; ?>

    <?php include __DIR__ . '/form_modal.php'; ?>

    <script src="<?php echo BASE_URL; ?>/views/vehiculos/vehiculos.js?v=<?= time() ?>"></script>
</body>
</html>