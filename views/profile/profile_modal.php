<!-- Modal Flotante de Perfil -->
<div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileModalLabel"><i class="fas fa-user-cog me-2"></i>Mi Perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="profileForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Campos de Solo Lectura -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">N° Documento</label>
                            <input type="text" id="prof_documento_id" class="form-control bg-light" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Usuario</label>
                            <input type="text" id="prof_username" class="form-control bg-light" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombres</label>
                            <input type="text" id="prof_nombres" class="form-control bg-light" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Apellidos</label>
                            <input type="text" id="prof_apellidos" class="form-control bg-light" readonly disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Rol</label>
                            <input type="text" id="prof_rol_name" class="form-control bg-light" readonly disabled>
                        </div>

                        <div class="col-12"><hr class="my-2"></div>

                        <!-- Campos Editables -->
                        <div class="col-md-6">
                            <label for="prof_correo" class="form-label fw-bold">Correo Electrónico <span class="text-danger">*</span></label>
                            <input type="email" id="prof_correo" name="correo" class="form-control" required placeholder="correo@ejemplo.com">
                        </div>
                        <div class="col-md-6">
                            <label for="prof_telefono" class="form-label fw-bold">Teléfono / Celular</label>
                            <input type="text" id="prof_telefono" name="telefono" class="form-control" maxlength="15" inputmode="numeric" placeholder="Solo números" autocomplete="tel">
                        </div>
                        <div class="col-md-12">
                            <label for="prof_password" class="form-label fw-bold">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" id="prof_password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual" autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="toggleProfPassword">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>