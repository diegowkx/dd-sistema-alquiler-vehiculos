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
                                    <h4 class="card-title mb-0">Facturación de Contratos</h4>
                                </div>

                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-comprobantes" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-3 py-2 tab-vista fw-bold text-white" data-vista="PENDIENTES" type="button" style="background-color: #6d28d9; border-color: #6d28d9;">
                                                <i class="fas fa-hourglass-half me-1"></i> Pendientes de Facturar
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-vista text-dark fw-bold" data-vista="EMITIDOS" type="button">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Comprobantes Emitidos
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive" id="wrapPendientes">
                                    <table id="datatable_pendientes" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Vehículo</th>
                                                <th>Cliente</th>
                                                <th>Salida</th>
                                                <th>Devolución Real</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="table-responsive" id="wrapEmitidos" style="display:none;">
                                    <table id="datatable_emitidos" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Comprobante</th>
                                                <th>Fecha Emisión</th>
                                                <th>Cliente</th>
                                                <th>Monto Total</th>
                                                <th>Método Pago</th>
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

    <?php include __DIR__ . '/comprobante_modal.php'; ?>

    <script src="<?php echo BASE_URL; ?>/views/comprobantes/comprobantes.js"></script>
</body>
</html>