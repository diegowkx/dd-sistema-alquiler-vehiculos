<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">

            <!-- Encabezado con línea divisoria roja suave -->
            <div class="modal-header border-0 pt-4 px-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid #fce8e8 !important;">
                <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="modalUsuarioLabel">
                    <i class="fas fa-user-cog me-2" style="color: #d9383a;"></i><span id="lblTituloUsuario">NUEVO USUARIO</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUsuario" autocomplete="off" novalidate>
                <div class="modal-body p-4">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="documento_id" class="form-label fw-semibold small" style="color: #475569;">Documento (DNI/CE) <span style="color: #d9383a;">*</span></label>
                            <input type="text" class="form-control" id="documento_id" name="documento_id" minlength="8" maxlength="15" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" placeholder="Ej: 70112233" autocomplete="off">
                        </div>

                        <div class="col-md-6">
                            <label for="rol_name" class="form-label fw-semibold small" style="color: #475569;">Rol <span style="color: #d9383a;">*</span></label>
                            <select class="form-select" id="rol_name" name="rol_name" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;">
                                <option value="">-- Seleccionar rol --</option>
                                <?php if (!empty($rolesDisponibles)): ?>
                                    <?php foreach ($rolesDisponibles as $rol): ?>
                                        <option value="<?php echo htmlspecialchars($rol['nombre']); ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label fw-semibold small" style="color: #475569;">Usuario <span style="color: #d9383a;">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" minlength="4" maxlength="20" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" placeholder="Ej: admin" autocomplete="username">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small" style="color: #475569;">Contraseña <span style="color: #d9383a;" id="reqPassword">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" minlength="6" autocomplete="new-password" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;">
                            <small class="text-muted" id="hintPassword" style="display:none;">Deja en blanco para no cambiarla</small>
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label fw-semibold small" style="color: #475569;">Nombres <span style="color: #d9383a;">*</span></label>
                            <input type="text" class="form-control" id="nombres" name="nombres" maxlength="100" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" autocomplete="given-name">
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label fw-semibold small" style="color: #475569;">Apellidos <span style="color: #d9383a;">*</span></label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" maxlength="100" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" autocomplete="family-name">
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-semibold small" style="color: #475569;">Correo <span style="color: #d9383a;">*</span></label>
                            <input type="email" class="form-control" id="correo" name="correo" maxlength="100" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" placeholder="correo@ejemplo.com" autocomplete="email">
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-semibold small" style="color: #475569;">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" minlength="7" maxlength="15" style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px;" placeholder="Ej: 987654321" autocomplete="tel">
                        </div>
                    </div>
                </div>

                <!-- Pie del modal -->
                <div class="modal-footer border-0 pb-4 px-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn border-0 fw-semibold px-4 py-2" data-bs-dismiss="modal" style="background-color: #fce8e8; color: #d9383a; border-radius: 50px;">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                    <button type="submit" class="btn text-white fw-semibold px-4 py-2" id="btnGuardarUsuario" form="formUsuario" style="background-color: #d9383a; border-radius: 50px; border: none;">
                        <i class="fas fa-save me-1"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>