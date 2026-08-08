<div class="modal fade" id="modalTipo" tabindex="-1" aria-labelledby="modalTipoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalTipoLabel">
                        <i class="fas fa-bed me-2"></i> <span id="lblTituloTipo">NUEVO TIPO</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formTipo" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="tipo_id_hidden" name="id" value="">

                    <div class="mb-3">
                        <label for="nombre_tipo" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control border-2" id="nombre_tipo" name="nombre" minlength="3" maxlength="50" required placeholder="Ej: Suite, Doble, Matrimonial" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        <div class="invalid-feedback">Ingrese un nombre válido (3 a 50 caracteres).</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="precio_base_noche" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Precio Base / Noche (S/) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" max="99999999.99" class="form-control border-2" id="precio_base_noche" name="precio_base_noche" required placeholder="0.00" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese un precio mayor a 0.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="capacidad_personas" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Capacidad (personas) <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="50" step="1" class="form-control border-2" id="capacidad_personas" name="capacidad_personas" value="1" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Mínimo 1 persona, máximo 50.</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="descripcion_tipo" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Descripción</label>
                        <textarea class="form-control border-2" id="descripcion_tipo" name="descripcion" rows="3" maxlength="500" placeholder="Descripción opcional del tipo de habitación..." style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                        <div class="invalid-feedback">La descripción no puede exceder los 500 caracteres.</div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarTipo" form="formTipo"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>