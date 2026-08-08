<div class="modal fade" id="modalEstadia" tabindex="-1" aria-labelledby="modalEstadiaLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalEstadiaLabel">
                        <i class="fas fa-sign-in-alt me-2"></i> <span id="lblTituloEstadia">NUEVO CHECK-IN</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4" style="background-color: #f8fafc;">

                <!-- ===================== SECCIÓN CHECK-IN (crear) ===================== -->
                <div id="seccionCheckin">
                    <form id="formEstadia" autocomplete="off" novalidate>
                        <input type="hidden" id="modo_checkin" name="modo" value="reserva">

                        <!-- Toggle de modo -->
                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary active" id="btnModoReserva">
                                <i class="fas fa-calendar-check me-1"></i> Desde Reserva
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnModoWalkin">
                                <i class="fas fa-walking me-1"></i> Walk-in (sin reserva)
                            </button>
                        </div>

                        <!-- --- Modo: Desde Reserva --- --> 
                        <div id="bloqueReserva">
                            <div class="mb-3 position-relative">
                                <label for="buscar_reserva" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Buscar Reserva Confirmada <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border-2" id="buscar_reserva" placeholder="Nombre, documento o N° de habitación..." style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <input type="hidden" id="reserva_id" name="reserva_id">
                                <div id="resultadosReservaBox" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1080; display: none; max-height: 240px; overflow-y: auto; background-color: #ffffff;"></div>
                                <small id="reservaSeleccionadaTxt" class="form-text text-success fw-bold"></small>
                            </div>
                        </div>

                        <!-- --- Modo: Walk-in --- -->
                        <div id="bloqueWalkin" style="display: none;">
                            <div class="mb-3 position-relative">
                                <label for="buscar_cliente" class="form-label">Cliente *</label>
                                <input type="text" class="form-control" id="buscar_cliente" placeholder="Escriba nombre o número de documento...">
                                <input type="hidden" id="cliente_doc" name="cliente_doc">
                                <div id="resultadosClienteBox" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1080; display: none; max-height: 200px; overflow-y: auto; background-color: #ffffff;"></div>
                                <small id="clienteSeleccionadoTxt" class="form-text text-success fw-bold"></small>
                            </div>

                            <div class="mb-3">
                                <label for="numero_habitacion" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Habitación <span class="text-danger">*</span></label>
                                <select class="form-select border-2" id="numero_habitacion" name="numero_habitacion" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                    <option value="">-- Seleccionar habitación disponible --</option>
                                    <?php if (!empty($habitacionesDisponibles)): ?>
                                        <?php foreach ($habitacionesDisponibles as $hab): ?>
                                            <option value="<?php echo htmlspecialchars($hab['numero_habitacion']); ?>" data-precio="<?php echo htmlspecialchars((string)$hab['precio_base_noche']); ?>">
                                                <?php echo htmlspecialchars($hab['numero_habitacion']); ?> — <?php echo htmlspecialchars($hab['tipo_nombre']); ?> (Piso <?php echo (int)$hab['piso']; ?>) — S/ <?php echo number_format((float)$hab['precio_base_noche'], 2); ?>/noche
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Precio de noche pactado (común a ambos modos) -->
                        <div class="mb-3">
                            <label for="precio_noche_pactado" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Precio de Noche Pactado (S/) <span class="text-danger">*</span></label>
                            <input type="number" min="0" step="0.01" class="form-control border-2" id="precio_noche_pactado" name="precio_noche_pactado" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <small class="text-muted">Se autocompleta según la habitación elegida; puede ajustarse si hubo una negociación especial.</small>
                        </div>
                    </form>
                </div>

                <!-- ===================== SECCIÓN CARGOS (estadía existente EN_CURSO) ===================== -->
                <div id="seccionCargos" style="display: none;">
                    <div class="p-3 mb-3 rounded" style="background-color: #fff7ed; border: 1px solid #fed7aa;">
                        <div><strong id="resumenCliente"></strong></div>
                        <div class="text-muted small" id="resumenHabitacion"></div>
                        <div class="text-muted small" id="resumenCheckin"></div>
                    </div>

                    <h6 class="fw-bold" style="color: #ea580c;">Cargos Extra</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-end">P. Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tablaCargosBody"></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold" id="totalCargosTxt">S/ 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <form id="formAgregarCargo" class="row g-2 align-items-end" autocomplete="off">
                        <input type="hidden" id="estadia_id_cargo" value="">
                        <div class="col-md-6">
                            <label for="concepto_cargo" class="form-label small mb-1">Concepto</label>
                            <input type="text" class="form-control form-control-sm" id="concepto_cargo" maxlength="150" placeholder="Ej. Minibar, servicio a la habitación...">
                        </div>
                        <div class="col-md-2">
                            <label for="cantidad_cargo" class="form-label small mb-1">Cant.</label>
                            <input type="number" min="1" step="1" class="form-control form-control-sm" id="cantidad_cargo" value="1">
                        </div>
                        <div class="col-md-2">
                            <label for="precio_unitario_cargo" class="form-label small mb-1">P. Unit. (S/)</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="precio_unitario_cargo">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100" id="btnAgregarCargo"><i class="fas fa-plus"></i> Agregar</button>
                        </div>
                    </form>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarEstadia" form="formEstadia"><i class="fas fa-save me-1"></i> Registrar Check-In</button>
            </div>
        </div>
    </div>
</div>