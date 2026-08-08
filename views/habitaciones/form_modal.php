<div class="modal fade" id="modalHabitacion" tabindex="-1" aria-labelledby="modalHabitacionLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalHabitacionLabel">
                        <i class="fas fa-door-open me-2"></i> <span id="lblTituloHabitacion">NUEVA HABITACIÓN</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formHabitacion" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">
                    <input type="hidden" id="numero_habitacion_original" name="numero_habitacion_original" value="">

                    <div class="row g-3">
                        <!-- N° Habitación -->
                        <div class="col-md-6">
                            <label for="numero_habitacion" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">N° Habitación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="numero_habitacion" name="numero_habitacion" minlength="1" maxlength="10" required placeholder="Ej: 101, A-1" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese un número válido (1 a 10 caracteres alfanuméricos).</div>
                        </div>

                        <!-- Piso -->
                        <div class="col-md-6">
                            <label for="piso" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Piso <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="99" step="1" class="form-control border-2" id="piso" name="piso" value="1" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">El piso debe ser un número entero entre 1 y 99.</div>
                        </div>

                        <!-- Tipo de Habitación -->
                        <div class="col-md-12">
                            <label for="tipo_nombre" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Tipo de Habitación <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="tipo_nombre" name="tipo_nombre" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="">-- Seleccionar tipo --</option>
                                <?php if (!empty($tiposDisponibles)): ?>
                                    <?php foreach ($tiposDisponibles as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo['nombre']); ?>">
                                            <?php echo htmlspecialchars($tipo['nombre']); ?> — S/ <?php echo number_format((float)$tipo['precio_base_noche'], 2); ?> / noche
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un tipo de habitación.</div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-md-12">
                            <label for="observaciones" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Observaciones</label>
                            <textarea class="form-control border-2" id="observaciones" name="observaciones" rows="3" maxlength="500" placeholder="Ej: Cerca al ascensor, vista a la calle..." style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                            <div class="invalid-feedback">Las observaciones no pueden superar los 500 caracteres.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarHabitacion" form="formHabitacion"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>