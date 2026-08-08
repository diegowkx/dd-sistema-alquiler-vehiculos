<!-- Modal: Nuevo Comprobante -->
<div class="modal fade" id="modalFacturar" tabindex="-1" aria-labelledby="modalFacturarLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalFacturarLabel">
                        <i class="fas fa-file-invoice me-2"></i> NUEVO COMPROBANTE
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formFacturar" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="ref_id" name="ref_id" value="">

                    <!-- Origen -->
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">¿Qué desea facturar? <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="origen" id="origen_estadia" value="estadia" checked>
                                <label class="form-check-label" for="origen_estadia">Estadía finalizada</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="origen" id="origen_reserva" value="reserva">
                                <label class="form-check-label" for="origen_reserva">Adelanto de reserva</label>
                            </div>
                        </div>
                    </div>

                    <!-- Buscador de referencia -->
                    <div class="mb-3 position-relative">
                        <label for="buscar_referencia" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Buscar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control border-2" id="buscar_referencia" autocomplete="off" placeholder="Buscar por cliente, documento o N° de habitación..." style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        <div id="resultadosReferenciaBox" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1080; max-height: 220px; overflow-y: auto; display: none; background-color: #ffffff;"></div>
                    </div>

                    <!-- Resumen de lo seleccionado -->
                    <div class="alert alert-light border d-none" id="resumenReferencia">
                        <div class="row small">
                            <div class="col-md-6"><strong>Cliente:</strong> <span id="resCliente"></span></div>
                            <div class="col-md-6"><strong>Habitación:</strong> <span id="resHabitacion"></span></div>
                            <div class="col-md-6"><strong>Fechas:</strong> <span id="resFechas"></span></div>
                            <div class="col-md-6"><strong>Monto referencial:</strong> <span id="resMonto"></span></div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Tipo Comprobante -->
                        <div class="col-md-6">
                            <label for="tipo_comprobante" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Tipo de Comprobante <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="tipo_comprobante" name="tipo_comprobante" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="BOLETA">Boleta</option>
                                <option value="FACTURA">Factura (solo clientes con RUC)</option>
                            </select>
                        </div>

                        <!-- Método de Pago -->
                        <div class="col-md-6">
                            <label for="metodo_pago" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="metodo_pago" name="metodo_pago" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TARJETA">Tarjeta</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="MIXTO">Mixto</option>
                            </select>
                        </div>

                        <!-- Descuento (solo aplica a estadías) -->
                        <div class="col-md-6" id="wrapDescuento">
                            <label for="descuento" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Descuento (S/)</label>
                            <input type="number" min="0" step="0.01" class="form-control border-2" id="descuento" name="descuento" value="0.00" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <small class="text-muted" id="hintDescuento"></small>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12">
                            <label for="observaciones" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Observaciones</label>
                            <textarea class="form-control border-2" id="observaciones" name="observaciones" rows="2" maxlength="500" style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarComprobante" form="formFacturar"><i class="fas fa-save me-1"></i> Generar Comprobante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Ver / Descargar Comprobante (PDF) -->
<div class="modal fade" id="modalVerComprobante" tabindex="-1" aria-labelledby="modalVerComprobanteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalVerComprobanteLabel">
                        <i class="fas fa-file-pdf me-2"></i> <span id="lblTituloComprobante">COMPROBANTE</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" style="background-color: #525659;">
                <iframe id="iframeComprobante" src="" style="width: 100%; height: 70vh; border: none;"></iframe>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-danger d-none" id="btnAnularComprobante"><i class="fas fa-ban me-1"></i> Anular Comprobante</button>
                <div class="d-flex gap-2 ms-auto">
                    <a href="#" id="linkDescargarComprobante" target="_blank" class="btn btn-success"><i class="fas fa-download me-1"></i> Descargar PDF</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>