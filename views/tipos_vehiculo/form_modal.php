<div class="modal fade" id="modalTipo" tabindex="-1" aria-labelledby="modalTipoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">

            <!-- Encabezado con línea divisoria roja suave -->
            <div class="modal-header border-0 pt-4 px-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid #fce8e8 !important;">
                <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="modalTipoLabel">
                    <i class="fas fa-car me-2" style="color: #d9383a;"></i><span id="lblTituloTipo">NUEVO TIPO</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formTipo" autocomplete="off" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" id="tipo_id_hidden" name="id" value="">

                    <div class="mb-3">
                        <label for="nombre_tipo" class="form-label fw-semibold small" style="color: #475569;">Nombre <span style="color: #d9383a;">*</span></label>
                        <input type="text" class="form-control" id="nombre_tipo" name="nombre" minlength="3" maxlength="50" required placeholder="Ej: Sedán, SUV, Camioneta" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                        <div class="invalid-feedback">Ingrese un nombre válido (3 a 50 caracteres).</div>
                    </div>

                    <div class="mb-3">
                        <label for="precio_base_dia" class="form-label fw-semibold small" style="color: #475569;">Precio Base / Día (S/) <span style="color: #d9383a;">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="99999999.99" class="form-control" id="precio_base_dia" name="precio_base_dia" required placeholder="0.00" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                        <div class="invalid-feedback">Ingrese un precio mayor a 0.</div>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion_tipo" class="form-label fw-semibold small" style="color: #475569;">Descripción</label>
                        <textarea class="form-control" id="descripcion_tipo" name="descripcion" rows="3" maxlength="500" placeholder="Descripción opcional del tipo de vehículo..." style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;"></textarea>
                        <div class="invalid-feedback">La descripción no puede exceder los 500 caracteres.</div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer border-0 pb-4 px-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn border-0 fw-semibold px-4 py-2" data-bs-dismiss="modal" style="background-color: #fce8e8; color: #d9383a; border-radius: 50px;">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                    <button type="submit" class="btn text-white fw-semibold px-4 py-2" id="btnGuardarTipo" form="formTipo" style="background-color: #d9383a; border-radius: 50px; border: none;">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>