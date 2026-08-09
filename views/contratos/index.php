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
                                    <h4 class="card-title mb-0">Gestión de Contratos de Alquiler</h4>
                                    <button type="button" class="btn text-white btn-nuevo-contrato" style="background-color: #d9383a; border-color: #d9383a;">
                                        <i class="fas fa-plus me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nuevo Contrato</span>
                                    </button>
                                </div>

                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-contratos" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-3 py-2 tab-estado fw-bold text-white" data-estado="ACTIVO" type="button" style="background-color: #d9383a; border-color: #d9383a;">
                                                <i class="fas fa-file-signature me-1"></i> Activos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="FINALIZADO" type="button">
                                                <i class="fas fa-flag-checkered me-1"></i> Finalizados
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="CANCELADO" type="button">
                                                <i class="fas fa-ban me-1"></i> Cancelados
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable_contratos" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Vehículo</th>
                                                <th>Cliente</th>
                                                <th>Salida</th>
                                                <th>Devolución Estimada</th>
                                                <th>Tarifa/Día</th>
                                                <th>Monto Estimado</th>
                                                <th>Firmado</th>
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
    <?php include __DIR__ . '/liquidacion_modal.php'; ?>

    <script src="<?php echo BASE_URL; ?>/views/contratos/contratos.js"></script>
    <script src="<?php echo BASE_URL; ?>/views/contratos/liquidacion.js"></script>
</body>
</html>