<!-- Modal Flotante de Perfil -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            
            <div class="modal-header py-3 position-relative" style="background-color: #d9383a;">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold text-white mb-0" id="profileModalLabel">
                        <i class="fas fa-user-cog me-2"></i>Mi Perfil
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="profileForm" autocomplete="off">
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="row g-3">
                        <!-- Campos de Solo Lectura -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">N° Documento</label>
                            <input type="text" id="prof_documento_id" class="form-control bg-light border-2" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Usuario</label>
                            <input type="text" id="prof_username" class="form-control bg-light border-2" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Nombres</label>
                            <input type="text" id="prof_nombres" class="form-control bg-light border-2" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Apellidos</label>
                            <input type="text" id="prof_apellidos" class="form-control bg-light border-2" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Rol</label>
                            <input type="text" id="prof_rol_name" class="form-control bg-light border-2" readonly disabled style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>

                        <div class="col-12"><hr class="my-2" style="border-color: #cbd5e1;"></div>

                        <!-- Campos Editables -->
                        <div class="col-md-6">
                            <label for="prof_correo" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" id="prof_correo" name="correo" class="form-control border-2" required placeholder="correo@ejemplo.com" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-6">
                            <label for="prof_telefono" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Teléfono / Celular</label>
                            <input type="text" id="prof_telefono" name="telefono" class="form-control border-2" maxlength="15" inputmode="numeric" placeholder="Solo números" autocomplete="tel" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                        </div>
                        <div class="col-md-12">
                            <label for="prof_password" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="prof_password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password" style="border-radius: 8px 0 0 8px; border: 1px solid #cbd5e1; height: 42px; border-right: none;">
                                <button class="btn" type="button" id="toggleProfPassword" style="border-radius: 0 8px 8px 0; border: 1px solid #cbd5e1; border-left: none; background-color: #ffffff; color: #d9383a; height: 42px; display: flex; align-items: center; justify-content: center; padding: 0 16px;">
                                    <i class="fas fa-eye" id="toggleProfIcon"></i>
                                </button>
                            </div>
                            <!-- Medidor de fortaleza -->
                            <div class="progress mt-2" style="height: 5px;">
                                <div id="prof-password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="prof-password-strength-text" class="form-text mt-1 d-block text-muted">
                                Si se deja en blanco, se conservará la contraseña actual.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d;"><i class="fas fa-times me-1"></i> Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #d9383a; border-color: #d9383a;"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>