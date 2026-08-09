<!DOCTYPE html>
<html lang="es">
<head>
    <?php include __DIR__ . '/../layouts/head/head_meta.php'; ?>
    <?php include __DIR__ . '/../layouts/head/head_css.php'; ?>
    
    <!-- Estilos Inline para consistencia de botones de filtro -->
    <style>
        .btn-filtro-categoria {
            border-color: #d9383a !important;
            color: #d9383a !important;
            transition: all 0.2s ease-in-out;
        }
        .btn-filtro-categoria:hover, 
        .btn-filtro-categoria.active {
            background-color: #d9383a !important;
            border-color: #d9383a !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(217, 56, 58, 0.25);
        }
    </style>
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

                <!-- Header y Controles -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-4" style="background-color: #ffffff;">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div>
                                        <h3 class="fw-bold mb-1" style="color: #0f172a;">
                                            <i class="fas fa-car me-2" style="color: #d9383a;"></i>Catálogo de Vehículos
                                        </h3>
                                        <p class="mb-0" style="color: #64748b;">Explora la variedad de vehículos disponibles</p>
                                    </div>
                                    
                                    <!-- Buscador -->
                                    <div class="input-group rounded-pill border shadow-sm overflow-hidden" style="max-width: 320px; padding: 3px 8px; border-color: #e2e8f0 !important; background-color: #ffffff;">
                                        <span class="input-group-text bg-transparent border-0 ps-2" style="color: #94a3b8;">
                                            <i class="fas fa-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="buscar_producto" 
                                               class="form-control bg-transparent border-0 shadow-none ps-2 pe-3" 
                                               placeholder="Buscar por marca, modelo o placa..." 
                                               style="font-size: 0.875rem; color: #334155;">
                                    </div>
                                </div>

                                <!-- Filtros por Categoría/Tipo -->
                                <?php if (!empty($categorias)): ?>
                                    <div class="d-flex flex-wrap gap-2 mt-4" id="contenedor-filtros-categorias">
                                        <button class="btn btn-outline-primary rounded-pill px-4 btn-filtro-categoria active" data-categoria="TODOS">
                                            <i class="fas fa-car-side me-1"></i> Todos
                                        </button>
                                        <?php foreach ($categorias as $catNombre): ?>
                                            <button class="btn btn-outline-primary rounded-pill px-4 btn-filtro-categoria" data-categoria="<?php echo htmlspecialchars($catNombre, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($catNombre, ENT_QUOTES, 'UTF-8'); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Tarjetas de Vehículos -->
                <div class="row" id="grid_productos">
                    <!-- Los vehículos se renderizan dinámicamente vía JS -->
                </div>

                <!-- Estado vacío sin resultados -->
                <div class="row d-none" id="sin_resultados">
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-car-crash mb-3" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                        <h5 class="fw-bold" style="color: #64748b;">No se encontraron vehículos disponibles</h5>
                    </div>
                </div>

            </div>
        </div>
        <!-- Content body end -->

        <?php include __DIR__ . '/../layouts/footer.php'; ?>

    </div>
    <!-- Main wrapper end -->

    <?php include __DIR__ . '/../layouts/scripts.php'; ?>
    <?php include __DIR__ . '/modal.php'; ?>

    <!-- Pasar la constante BASE_URL a JavaScript -->
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>

    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/views/catalogo/catalogo.js?v=<?= time() ?>"></script>
    
    <!-- Asegurar la remoción del preloader al cargar -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const preloader = document.getElementById('preloader');
            if(preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
</body>
</html>