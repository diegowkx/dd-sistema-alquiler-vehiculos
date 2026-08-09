<!-- Modal Nuevo / Editar Vehículo -->
<div class="modal fade" id="modalVehiculo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">
            
            <!-- Encabezado Modal con línea divisoria roja suave -->
            <div class="modal-header border-0 pt-4 px-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid #fce8e8 !important;">
                <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="lblTituloVehiculo">NUEVO VEHÍCULO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formVehiculo" autocomplete="off">
                <div class="modal-body p-4">
                    
                    <!-- Campos de control hidden -->
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">
                    <input type="hidden" id="placa_original" name="placa_original" value="">
                    <input type="hidden" id="imagen_url" name="imagen_url" value="assets/vehiculos/default.png">

                    <div class="row g-4">
                        <!-- Columna Izquierda: Formulario -->
                        <div class="col-md-7">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label for="placa" class="form-label fw-semibold small" style="color: #475569;">Placa <span style="color: #d9383a;">*</span></label>
                                    <input type="text" class="form-control text-uppercase" id="placa" name="placa" placeholder="Ej: ABC-123" required maxlength="10" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-6">
                                    <label for="tipo_nombre" class="form-label fw-semibold small" style="color: #475569;">Tipo de Vehículo <span style="color: #d9383a;">*</span></label>
                                    <select class="form-select" id="tipo_nombre" name="tipo_nombre" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                        <option value="">-- Seleccionar --</option>
                                        <?php if (!empty($tiposDisponibles) && is_array($tiposDisponibles)): ?>
                                            <?php foreach ($tiposDisponibles as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo['tipo_nombre'] ?? $tipo['nombre'] ?? '') ?>">
                                                    <?= htmlspecialchars($tipo['tipo_nombre'] ?? $tipo['nombre'] ?? '') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="marca" class="form-label fw-semibold small" style="color: #475569;">Marca <span style="color: #d9383a;">*</span></label>
                                    <input type="text" class="form-control" id="marca" name="marca" placeholder="Ej: Toyota" required maxlength="50" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-6">
                                    <label for="modelo" class="form-label fw-semibold small" style="color: #475569;">Modelo <span style="color: #d9383a;">*</span></label>
                                    <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ej: Corolla" required maxlength="50" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-4">
                                    <label for="anio" class="form-label fw-semibold small" style="color: #475569;">Año <span style="color: #d9383a;">*</span></label>
                                    <input type="number" class="form-control" id="anio" name="anio" placeholder="<?= date('Y') ?>" required min="1900" max="<?= (int)date('Y') + 1 ?>" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-4">
                                    <label for="color" class="form-label fw-semibold small" style="color: #475569;">Color <span style="color: #d9383a;">*</span></label>
                                    <input type="text" class="form-control" id="color" name="color" placeholder="Negro" required maxlength="30" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-4">
                                    <label for="kilometraje" class="form-label fw-semibold small" style="color: #475569;">Km <span style="color: #d9383a;">*</span></label>
                                    <input type="number" class="form-control" id="kilometraje" name="kilometraje" value="0" required min="0" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                </div>

                                <div class="col-md-12">
                                    <label for="estado_disponibilidad" class="form-label fw-semibold small" style="color: #475569;">Estado Disponibilidad</label>
                                    <select class="form-select" id="estado_disponibilidad" name="estado_disponibilidad" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                                        <option value="DISPONIBLE">DISPONIBLE</option>
                                        <option value="ALQUILADO">ALQUILADO</option>
                                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                                    </select>
                                </div>

                                <!-- Opciones Imagen -->
                                <div class="col-md-12">
                                    <div class="p-3" style="background-color: #f1f5f9; border-radius: 14px; border: 1px solid #e2e8f0;">
                                        <label class="form-label fw-semibold small mb-2" style="color: #475569;">Origen de Imagen</label>
                                        <div class="d-flex gap-4 mb-2 align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="radio" name="tipoImagen" id="tipoLocal" value="local" checked style="accent-color: #d9383a; width: 18px; height: 18px; cursor: pointer;">
                                                <label class="small fw-semibold mb-0" for="tipoLocal" style="color: #475569; cursor: pointer;">Galería local</label>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="radio" name="tipoImagen" id="tipoUrl" value="url" style="accent-color: #d9383a; width: 18px; height: 18px; cursor: pointer;">
                                                <label class="small fw-semibold mb-0" for="tipoUrl" style="color: #475569; cursor: pointer;">URL Web externa</label>
                                            </div>
                                        </div>

                                        <div id="contenedorLocal">
                                            <select class="form-select" id="selectImagenLocal" style="border-radius: 10px; border: 1.5px solid #cbd5e1; background-color: #ffffff; padding: 8px 12px;">
                                                <?php if (!empty($imagenesDisponibles) && is_array($imagenesDisponibles)): ?>
                                                    <?php foreach ($imagenesDisponibles as $img): ?>
                                                        <option value="<?= htmlspecialchars($img) ?>">
                                                            <?= htmlspecialchars(basename($img)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="assets/vehiculos/default.png">default.png</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>

                                        <div id="contenedorUrl" class="d-none">
                                            <input type="url" class="form-control" id="inputImagenUrl" placeholder="https://ejemplo.com/imagen.jpg" style="border-radius: 10px; border: 1.5px solid #cbd5e1; background-color: #ffffff; padding: 8px 12px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label for="observaciones" class="form-label fw-semibold small" style="color: #475569;">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2" maxlength="500" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;"></textarea>
                                </div>

                            </div>
                        </div>

                        <!-- Columna Derecha: Vista Previa -->
                        <div class="col-md-5 d-flex flex-column align-items-center justify-content-center">
                            <label class="form-label fw-semibold small text-center mb-3" style="color: #d9383a;">Vista Previa</label>
                            <div class="w-100 d-flex align-items-center justify-content-center" style="background-color: #f1f5f9; border-radius: 20px; padding: 20px; min-height: 240px; border: 1px solid #e2e8f0;">
                                <img id="previewImagen" src="<?= BASE_URL ?>/assets/vehiculos/default.png" class="img-fluid" style="max-height: 180px; object-fit: contain; border-radius: 12px;">
                            </div>
                        </div>
                    </div>

                </div>
                
                <!-- Pie del modal -->
                <div class="modal-footer border-0 pb-4 px-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn border-0 fw-semibold px-4 py-2" data-bs-dismiss="modal" style="background-color: #fce8e8; color: #d9383a; border-radius: 50px;">Cancelar</button>
                    <button type="submit" class="btn text-white fw-semibold px-4 py-2" style="background-color: #d9383a; border-radius: 50px; border: none;">Guardar Vehículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Placa -->
<div class="modal fade" id="modalCambiarPlaca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">
            <div class="modal-header border-0 pt-4 px-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid #fce8e8 !important;">
                <h5 class="modal-title fw-bold text-dark fs-5 mb-0"><i class="fas fa-id-card me-2" style="color: #d9383a;"></i>Corregir / Cambiar Placa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCambiarPlaca" autocomplete="off">
                <div class="modal-body p-4">
                    <input type="hidden" id="placa_actual" name="placa_actual">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color: #475569;">Placa Actual:</label>
                        <input type="text" id="placa_actual_display" class="form-control fw-bold border-0" readonly style="border-radius: 12px; background-color: #f1f5f9; padding: 10px 14px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small" style="color: #475569;">Nueva Placa <span style="color: #d9383a;">*</span></label>
                        <input type="text" id="nueva_placa" name="nueva_placa" class="form-control text-uppercase" placeholder="Ej: ABC-123" required maxlength="10" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; padding: 10px 14px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn border-0 fw-semibold px-4 py-2" data-bs-dismiss="modal" style="background-color: #fce8e8; color: #d9383a; border-radius: 50px;">Cancelar</button>
                    <button type="submit" class="btn text-white fw-semibold px-4 py-2" style="background-color: #d9383a; border-radius: 50px; border: none;"><i class="fas fa-check me-1"></i> Actualizar Placa</button>
                </div>
            </form>
        </div>
    </div>
</div>