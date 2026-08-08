<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalUsuarioLabel">
                        <i class="fas fa-user-cog me-2"></i> <span id="lblTituloUsuario">NUEVO USUARIO</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formUsuario" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="documento_id" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Documento (DNI/CE) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="documento_id" name="documento_id" minlength="8" maxlength="15" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" placeholder="Ej: 70112233" autocomplete="off">
                        </div>

                        <div class="col-md-6">
                            <label for="rol_name" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Rol <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="rol_name" name="rol_name" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="">-- Seleccionar rol --</option>
                                <?php if (!empty($rolesDisponibles)): ?>
                                    <?php foreach ($rolesDisponibles as $rol): ?>
                                        <option value="<?php echo htmlspecialchars($rol['nombre']); ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Usuario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="username" name="username" minlength="4" maxlength="20" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" placeholder="Ej: admin" autocomplete="username">
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Contraseña <span class="text-danger" id="reqPassword">*</span></label>
                            <input type="password" class="form-control border-2" id="password" name="password" minlength="6" autocomplete="new-password" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <small class="text-muted" id="hintPassword" style="display:none;">Deja en blanco para no cambiarla</small>
                        </div>

                        <div class="col-md-6">
                            <label for="nombres" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="nombres" name="nombres" maxlength="100" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" autocomplete="given-name">
                        </div>

                        <div class="col-md-6">
                            <label for="apellidos" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="apellidos" name="apellidos" maxlength="100" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" autocomplete="family-name">
                        </div>

                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Correo <span class="text-danger">*</span></label>
                            <input type="email" class="form-control border-2" id="correo" name="correo" maxlength="100" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" placeholder="correo@ejemplo.com" autocomplete="email">
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-bold" style="color: #ea580c; font-size: 0.9rem;">Teléfono</label>
                            <input type="text" class="form-control border-2" id="telefono" name="telefono" minlength="7" maxlength="15" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;" placeholder="Ej: 987654321" autocomplete="tel">
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarUsuario" form="formUsuario"><i class="fas fa-save me-1"></i> Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>