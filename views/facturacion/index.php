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
                                    <h4 class="card-title mb-0">Facturación</h4>
                                    <button type="button" class="btn btn-primary btn-nuevo-comprobante">
                                        <i class="fas fa-file-invoice me-1"></i>
                                        <span class="d-none d-sm-inline ms-1">Nuevo Comprobante</span>
                                    </button>
                                </div>

                                <div class="w-100 text-start">
                                    <ul class="nav nav-pills gap-2" id="pills-tab-facturacion" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active px-3 py-2 tab-estado fw-bold" data-estado="EMITIDO" type="button">
                                                <i class="fas fa-file-invoice-dollar me-1"></i> Emitidos
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link px-3 py-2 tab-estado text-dark fw-bold" data-estado="ANULADO" type="button">
                                                <i class="fas fa-ban me-1"></i> Anulados
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="datatable_facturacion" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Comprobante</th>
                                                <th>Cliente</th>
                                                <th>Origen</th>
                                                <th>Fecha Emisión</th>
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

    <?php include __DIR__ . '/form_modal.php'; ?>

    <script src="<?php echo BASE_URL; ?>/views/facturacion/facturacion.js"></script>
</body>
</html>