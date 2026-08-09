<div class="modal fade" id="modalComprobante" tabindex="-1" aria-labelledby="modalComprobanteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #6d28d9;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalComprobanteLabel">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Emitir Comprobante — Contrato #<span id="lblContratoIdComprobante"></span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formComprobante" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="cmp_contrato_id" name="contrato_id" value="">

                    <div class="alert small mb-3" id="infoContratoComprobante" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; border-radius: 8px;"></div>

                    <div class="row g-4">
                        <!-- Columna izquierda: cargos + resumen -->
                        <div class="col-md-6">
                            <h6 class="fw-bold" style="color:#6d28d9;">Detalle a Facturar</h6>

                            <div class="table-responsive mb-2" style="max-height: 180px; overflow-y:auto;">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>Concepto</th>
                                            <th class="text-end">Cant.</th>
                                            <th class="text-end">P. Unit.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCargosComprobanteBody">
                                        <tr><td colspan="4" class="text-muted text-center small">Sin cargos extra registrados</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="p-3" style="background-color:#fff3cd; border:1px solid #ffe08a; border-radius:8px;">
                                <div class="d-flex justify-content-between small">
                                    <span>Subtotal alquiler:</span>
                                    <span id="resumenSubtotalAlquiler" class="fw-bold">S/ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span>Cargos extra:</span>
                                    <span id="resumenCargosExtra" class="fw-bold">S/ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between small align-items-center mt-1">
                                    <label for="descuento" class="mb-0">Descuento (S/):</label>
                                    <input type="number" min="0" step="0.01" class="form-control form-control-sm text-end" id="descuento" name="descuento" value="0" style="width:110px; border-radius:6px;">
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold" style="color:#7a5b00;">TOTAL A PAGAR (inc. IGV):</span>
                                    <span id="resumenTotalComprobante" class="fw-bold" style="color:#7a5b00;">S/ 0.00</span>
                                </div>
                                <small class="text-muted d-block mt-1" id="detalleDesgloseIgv"></small>
                            </div>
                        </div>

                        <!-- Columna derecha: datos del comprobante -->
                        <div class="col-md-6">
                            <h6 class="fw-bold" style="color:#6d28d9;">Datos del Comprobante</h6>

                            <div class="row g-3">
                                <div class="col-6">
                                    <label for="tipo_comprobante" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-select border-2" id="tipo_comprobante" name="tipo_comprobante" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                        <option value="BOLETA">Boleta</option>
                                        <option value="FACTURA">Factura</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="metodo_pago" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Método de Pago <span class="text-danger">*</span></label>
                                    <select class="form-select border-2" id="metodo_pago" name="metodo_pago" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TARJETA">Tarjeta</option>
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="MIXTO">Mixto</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label for="cliente_tipo_doc" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Tipo Doc. Cliente <span class="text-danger">*</span></label>
                                    <select class="form-select border-2" id="cliente_tipo_doc" name="cliente_tipo_doc" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">Carné de Extranjería</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                    </select>
                                    <small class="text-muted">Para FACTURA el cliente debe tener RUC.</small>
                                </div>
                                <div class="col-6">
                                    <label for="cliente_num_doc" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">N° Documento <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-2" id="cliente_num_doc" name="cliente_num_doc" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                    <small id="msgErrorDocumento" class="text-danger fw-bold" style="display:none;"></small>
                                </div>

                                <div class="col-12">
                                    <label for="cliente_razon_social" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Razón Social / Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-2" id="cliente_razon_social" name="cliente_razon_social" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                </div>

                                <div class="col-12">
                                    <label for="cliente_direccion" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Dirección</label>
                                    <input type="text" class="form-control border-2" id="cliente_direccion" name="cliente_direccion" style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                </div>

                                <div class="col-12">
                                    <label for="observaciones" class="form-label fw-bold" style="color:#6d28d9; font-size:0.9rem;">Observaciones</label>
                                    <textarea class="form-control border-2" id="observaciones" name="observaciones" rows="2" style="border-radius:8px; border-color:#cbd5e1;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d; color: #ffffff;"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn text-white" id="btnEmitirComprobante" style="background-color: #6d28d9; border-color: #6d28d9;"><i class="fas fa-file-invoice-dollar me-1"></i> Emitir Comprobante</button>
                </div>
            </form>
        </div>
    </div>
</div>