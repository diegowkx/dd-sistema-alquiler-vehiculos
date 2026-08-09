<div class="modal fade" id="modalLiquidacion" tabindex="-1" aria-labelledby="modalLiquidacionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #0b667c;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalLiquidacionLabel">
                        <i class="fas fa-file-invoice me-2"></i> Liquidación del Contrato #<span id="lblContratoIdLiquidacion"></span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <div class="alert small mb-3" id="infoContratoLiquidacion" style="background-color: #e6f0f3; color: #0b667c; border: 1px solid #b3d4dc; border-radius: 8px;"></div>

                <div id="avisoLiquidacionFirmada" class="alert alert-success small" style="display:none; border-radius: 8px;">
                    <i class="fas fa-lock me-1"></i> Esta liquidación ya está firmada y no puede modificarse.
                </div>

                <div class="row g-4">
                    <!-- Columna izquierda: datos de la liquidación -->
                    <div class="col-md-6">
                        <h6 class="fw-bold" style="color:#0b667c;">Datos de Devolución</h6>
                        <form id="formLiquidacion" autocomplete="off" novalidate>
                            <input type="hidden" id="liq_contrato_id" name="contrato_id" value="">

                            <div class="mb-3">
                                <label for="fecha_hora_devolucion_real" class="form-label fw-bold" style="color:#0b667c; font-size:0.9rem;">Fecha y Hora de Devolución Real <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control border-2" id="fecha_hora_devolucion_real" name="fecha_hora_devolucion_real" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                            </div>

                            <div class="mb-3">
                                <label for="kilometraje_llegada" class="form-label fw-bold" style="color:#0b667c; font-size:0.9rem;">Kilometraje de Llegada <span class="text-danger">*</span></label>
                                <input type="number" min="0" class="form-control border-2" id="kilometraje_llegada" name="kilometraje_llegada" required style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                            </div>

                            <div class="mb-3" id="wrapResponsableRetraso" style="display:none;">
                                <label for="responsable_retraso" class="form-label fw-bold" style="color:#0b667c; font-size:0.9rem;">Hubo Retraso — ¿Responsable? <span class="text-danger">*</span></label>
                                <select class="form-select border-2" id="responsable_retraso" name="responsable_retraso" style="border-radius:8px; border-color:#cbd5e1; height:42px;">
                                    <option value="NINGUNO">Sin retraso</option>
                                    <option value="CLIENTE">Cliente (se le cobra el tiempo extra)</option>
                                    <option value="EMPRESA">Empresa (no se le cobra al cliente)</option>
                                </select>
                            </div>
                            <input type="hidden" id="responsable_retraso_hidden" name="responsable_retraso_hidden" value="NINGUNO">

                            <div class="mb-3">
                                <label for="observaciones_llegada" class="form-label fw-bold" style="color:#0b667c; font-size:0.9rem;">Observaciones de Llegada</label>
                                <textarea class="form-control border-2" id="observaciones_llegada" name="observaciones_llegada" rows="3" placeholder="Daños nuevos, limpieza, combustible faltante, etc." style="border-radius:8px; border-color:#cbd5e1;"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Columna derecha: cargos de alquiler -->
                    <div class="col-md-6">
                        <h6 class="fw-bold" style="color:#0b667c;">Cargos Extra de Alquiler <span class="fw-normal text-muted small">(opcional)</span></h6>

                        <form id="formCargo" class="row g-2 align-items-end mb-3" autocomplete="off">
                            <div class="col-6">
                                <label class="form-label small fw-bold mb-1">Concepto</label>
                                <input type="text" class="form-control form-control-sm" id="cargo_concepto" maxlength="150" placeholder="Ej: Tapicería manchada" style="border-radius:6px;">
                            </div>
                            <div class="col-2">
                                <label class="form-label small fw-bold mb-1">Cant.</label>
                                <input type="number" min="1" value="1" class="form-control form-control-sm" id="cargo_cantidad" style="border-radius:6px;">
                            </div>
                            <div class="col-2">
                                <label class="form-label small fw-bold mb-1">P. Unit.</label>
                                <input type="number" min="0.01" step="0.01" class="form-control form-control-sm" id="cargo_precio" style="border-radius:6px;">
                            </div>
                            <div class="col-2">
                                <button type="submit" class="btn btn-sm text-white w-100" id="btnAgregarCargo" style="background-color:#0b667c; border-color:#0b667c; border-radius:6px;">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive" style="max-height: 220px; overflow-y:auto;">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th class="text-end">Cant.</th>
                                        <th class="text-end">P. Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="tablaCargosBody">
                                    <tr><td colspan="5" class="text-muted text-center small">Sin cargos registrados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Resumen -->
                <div class="row">
                    <div class="col-12">
                        <div class="p-3" style="background-color:#fff3cd; border:1px solid #ffe08a; border-radius:8px;">
                            <div class="d-flex justify-content-between small">
                                <span>Monto por alquiler (días/horas):</span>
                                <span id="resumenMontoAlquiler" class="fw-bold">S/ 0.00</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Total cargos extra:</span>
                                <span id="resumenTotalCargos" class="fw-bold">S/ 0.00</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold" style="color:#7a5b00;">TOTAL LIQUIDACIÓN:</span>
                                <span id="resumenTotalFinal" class="fw-bold" style="color:#7a5b00;">S/ 0.00</span>
                            </div>
                            <small class="text-muted d-block mt-1" id="detalleCalculoLiquidacion"></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <a href="#" id="btnPdfLiquidacion" target="_blank" class="btn text-white" style="background-color:#0b667c; border-color:#0b667c;">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color:#6c757d; border-color:#6c757d; color:#ffffff;">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn text-white" id="btnGuardarLiquidacion" style="background-color:#d9383a; border-color:#d9383a;">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                    <div id="contenedorFirmarLiquidacion" class="d-inline-block">
                        <button type="button" class="btn text-white" id="btnFirmarLiquidacion" disabled style="background-color:#2ab418; border-color:#2ab418;">
                            <i class="fas fa-signature me-1"></i> Firmado
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>