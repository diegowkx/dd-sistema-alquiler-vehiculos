<div class="modal fade" id="modalContrato" tabindex="-1" aria-labelledby="modalContratoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #d9383a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalContratoLabel">
                        <i class="fas fa-file-signature me-2"></i> <span id="lblTituloContrato">NUEVO CONTRATO</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formContrato" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">
                    <input type="hidden" id="contrato_id" name="id" value="">
                    <input type="hidden" id="cliente_doc" name="cliente_doc" value="">

                    <div class="row g-3">
                        <!-- Buscador de Cliente (autocompletado, reutiliza ?c=clientes&a=buscarAJAX) -->
                        <div class="col-md-6 position-relative">
                            <label for="buscadorCliente" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Cliente <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="buscadorCliente" placeholder="Buscar por documento o nombre..." required autocomplete="off" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div id="listaClientesFlotante" class="shadow-sm border rounded bg-white position-absolute w-100 mt-1" style="display: none; max-height: 220px; overflow-y: auto; z-index: 1055; left: 0;"></div>
                            <div class="invalid-feedback">Debe seleccionar un cliente de la lista.</div>
                        </div>

                        <!-- Vehículo (solo DISPONIBLE) -->
                        <div class="col-md-6">
                            <label for="placa_vehiculo" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Vehículo <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="placa_vehiculo" name="placa_vehiculo" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="">Seleccione un vehículo disponible...</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un vehículo disponible.</div>
                        </div>
                        <!-- Información del vehículo seleccionado -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">
                                Información del Vehículo
                            </label>

                            <div 
                                id="infoVehiculoSeleccionado"
                                class="form-control"
                                style="
                                    border-radius: 8px;
                                    border: 2px solid #cbd5e1;
                                    min-height: 42px;
                                    height: auto;
                                    background-color: #f8fafc;
                                "
                            >
                                <span class="text-muted">
                                    Seleccione un vehículo para ver su información.
                                </span>
                            </div>
                        </div>

                        <!-- Fecha/Hora de Salida -->
                        <div class="col-md-6">
                            <label for="fecha_hora_salida" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Fecha y Hora de Salida <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control border-2" id="fecha_hora_salida" name="fecha_hora_salida" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese la fecha y hora de salida.</div>
                        </div>

                        <!-- Fecha/Hora de Devolución Estimada -->
                        <div class="col-md-6">
                            <label for="fecha_hora_devolucion_estimada" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Devolución Estimada <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control border-2" id="fecha_hora_devolucion_estimada" name="fecha_hora_devolucion_estimada" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Debe ser posterior a la fecha de salida.</div>
                        </div>

                        <!-- Tarifa por día -->
                        <div class="col-md-4">
                            <label for="tarifa_dia_pactada" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Tarifa por Día (S/) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0.01" class="form-control border-2" id="tarifa_dia_pactada" name="tarifa_dia_pactada" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese una tarifa válida.</div>
                        </div>

                        <!-- Kilometraje de salida -->
                        <div class="col-md-4">
                            <label for="kilometraje_salida" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Kilometraje de Salida <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control border-2" id="kilometraje_salida" name="kilometraje_salida" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese el kilometraje actual del vehículo.</div>
                        </div>

                        <!-- Resumen de cálculo (solo lectura, se actualiza en vivo) -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Monto Estimado</label>
                            <div id="resumenCosto" class="form-control d-flex align-items-center fw-bold" style="border-radius: 8px; height: 42px; background-color: #fff3cd; border-color: #ffe08a; color: #7a5b00;">
                                S/ 0.00
                            </div>
                        </div>
                        <div class="col-12">
                            <small class="text-muted" id="detalleCosto">Complete fechas y tarifa para ver el cálculo (días completos + fracción proporcional de horas extra).</small>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <label for="observaciones_salida" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Observaciones de Salida</label>
                            <textarea class="form-control border-2" id="observaciones_salida" name="observaciones_salida" rows="2" placeholder="Estado del vehículo, rayones existentes, nivel de combustible, etc." style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d; color: #ffffff;"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn text-white" id="btnGuardarContrato" form="formContrato" style="background-color: #d9383a; border-color: #d9383a;"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>