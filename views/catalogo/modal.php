<!-- Modal de Detalles del Vehículo -->
<div class="modal fade" id="modalDetallesVehiculo" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: #ffffff;">
            
            <!-- Header Rojo Corporativo -->
            <div class="modal-header text-white border-0 py-3 px-4" style="background-color: #d9383a;">
                <h5 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2" id="modalDetallesLabel">
                    <i class="fas fa-car-side fs-5"></i> Detalles del Vehículo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Modal -->
            <div class="modal-body p-4" style="background-color: #ffffff;">
                <div class="row g-4 align-items-center">
                    
                    <!-- Imagen Principal con borde sutil -->
                    <div class="col-md-6 text-center">
                        <div class="p-1 bg-white border rounded-4 shadow-sm">
                            <img id="det_imagen" src="" alt="Vehículo" class="img-fluid rounded-4 w-100" style="max-height: 250px; object-fit: cover;">
                        </div>
                    </div>

                    <!-- Datos del Vehículo -->
                    <div class="col-md-6">
                        <!-- Badges Tipo / Estado -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span id="det_tipo" class="badge text-uppercase px-3 py-2 rounded-pill text-white fw-semibold" style="background-color: #d9383a; font-size: 0.75rem; letter-spacing: 0.5px;"></span>
                            <span id="det_estado" class="badge px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"></span>
                        </div>

                        <!-- Título y Placa -->
                        <h3 id="det_titulo" class="fw-bold mb-1" style="color: #0f172a; font-size: 1.4rem;"></h3>
                        <p class="small mb-3 d-flex align-items-center gap-1" style="color: #64748b;">
                            <i class="fas fa-id-card text-muted"></i> Placa: <strong id="det_placa" style="color: #334155;"></strong>
                        </p>

                        <!-- Grid 2x2: Todos los campos IGUALES (Fondo gris neutro ultra limpio) -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                    <small class="d-block fw-medium mb-1" style="color: #64748b; font-size: 0.725rem;">
                                        <i class="fas fa-calendar-alt me-1" style="color: #94a3b8;"></i>Año
                                    </small>
                                    <span id="det_anio" class="fw-bold d-block" style="color: #0f172a; font-size: 0.95rem;"></span>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                    <small class="d-block fw-medium mb-1" style="color: #64748b; font-size: 0.725rem;">
                                        <i class="fas fa-palette me-1" style="color: #94a3b8;"></i>Color
                                    </small>
                                    <span id="det_color" class="fw-bold d-block" style="color: #0f172a; font-size: 0.95rem;"></span>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                    <small class="d-block fw-medium mb-1" style="color: #64748b; font-size: 0.725rem;">
                                        <i class="fas fa-tachometer-alt me-1" style="color: #94a3b8;"></i>Kilometraje
                                    </small>
                                    <span id="det_km" class="fw-bold d-block" style="color: #0f172a; font-size: 0.95rem;"></span>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="p-2.5 px-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                    <small class="d-block fw-medium mb-1" style="color: #64748b; font-size: 0.725rem;">
                                        <i class="fas fa-tag me-1" style="color: #94a3b8;"></i>Precio por día
                                    </small>
                                    <span id="det_precio" class="fw-bold d-block" style="color: #0f172a; font-size: 0.95rem;"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones / Equipamiento -->
                        <div>
                            <small class="d-block mb-1 fw-medium" style="color: #64748b; font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1" style="color: #94a3b8;"></i>Observaciones / Equipamiento:
                            </small>
                            <p id="det_observaciones" class="small mb-0 p-2.5 px-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important; color: #334155; font-size: 0.85rem; min-height: 42px;"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Elegante -->
            <div class="modal-footer border-0 pb-4 pe-4 pt-0" style="background-color: #ffffff;">
                <button type="button" class="btn text-white rounded-pill px-4 fw-semibold shadow-sm" style="background-color: #d9383a; border: none; font-size: 0.9rem;" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>