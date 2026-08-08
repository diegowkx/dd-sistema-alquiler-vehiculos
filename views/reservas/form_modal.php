<div class="modal fade" id="modalReserva" tabindex="-1" aria-labelledby="modalReservaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalReservaLabel">
                        <i class="fas fa-calendar-plus me-2"></i> <span id="lblTituloReserva">NUEVA RESERVA</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formReserva" autocomplete="on" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">
                    <input type="hidden" id="id_reserva" name="id" value="">

                    <div class="row g-3">
                        <!-- Cliente (buscador con autocompletado) -->
                        <div class="col-md-12 position-relative">
                            <label for="buscar_cliente" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Cliente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="buscar_cliente" name="name" placeholder="Escriba nombre o número de documento..." autocomplete="name" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <input type="hidden" id="cliente_doc" name="cliente_doc">
                            <!-- Contenedor flotante para los resultados -->
                            <div id="resultadosClienteBox" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1080; display: none; max-height: 200px; overflow-y: auto; background-color: #ffffff;"></div>         
                            <small id="clienteSeleccionadoTxt" class="form-text text-success fw-bold"></small>
                        </div>  

                        <!-- Habitación -->
                        <div class="col-md-12">
                            <label for="numero_habitacion" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Habitación <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="numero_habitacion" name="numero_habitacion" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="">-- Seleccionar habitación disponible --</option>
                                <?php if (!empty($habitacionesDisponibles)): ?>
                                    <?php foreach ($habitacionesDisponibles as $hab): ?>
                                        <option value="<?php echo htmlspecialchars($hab['numero_habitacion']); ?>">
                                            <?php echo htmlspecialchars($hab['numero_habitacion']); ?> — <?php echo htmlspecialchars($hab['tipo_nombre']); ?> (Piso <?php echo (int)$hab['piso']; ?>) — S/ <?php echo number_format((float)$hab['precio_base_noche'], 2); ?>/noche
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar una habitación disponible.</div>
                        </div>

                        <!-- Fechas (Tipos DATE según tu BD: fecha_checkin_prevista, fecha_checkout_prevista) -->
                        <div class="col-md-6">
                            <label for="fecha_checkin_prevista" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Check-In Previsto <span class="text-danger">*</span></label>
                            <input type="date" class="form-control border-2" id="fecha_checkin_prevista" name="fecha_checkin_prevista" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_checkout_prevista" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Check-Out Previsto <span class="text-danger">*</span></label>
                            <input type="date" class="form-control border-2" id="fecha_checkout_prevista" name="fecha_checkout_prevista" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>

                        <!-- Adelanto (DECIMAL(10, 2) según tu BD) -->
                        <div class="col-md-12">
                            <label for="monto_adelanto" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Monto de Adelanto (S/)</label>
                            <input type="number" min="0" step="0.01" inputmode="decimal" class="form-control border-2" id="monto_adelanto" name="monto_adelanto" value="0.00" autocomplete="transaction-amount" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <small class="text-muted">Quedará registrado para descontarse luego del comprobante final.</small>
                        </div>

                        <!-- Observaciones (TEXT según tu BD) -->
                        <div class="col-md-12">
                            <label for="observaciones" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Observaciones</label>
                            <textarea class="form-control border-2" id="observaciones" name="observaciones" rows="3" maxlength="1000" style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarReserva" form="formReserva"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>