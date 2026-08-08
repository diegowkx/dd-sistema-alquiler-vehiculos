<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- 1. Modal Header -->
            <div class="modal-header py-3 position-relative" style="background-color: #ff6b4a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalRolLabel">
                        <i class="fas fa-user-shield me-2"></i> <span id="lblTituloRol">NUEVO ROL</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formRol" autocomplete="off">
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="rol_id_hidden" name="id" value="">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <!-- 2. Nombre del Rol -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background-color: #ffffff;">
                        <div class="card-body p-3">
                            <label for="nombre_rol" class="form-label fw-bold mb-1" style="color: #ea580c; font-size: 0.9rem;">
                                <i class="fas fa-tag me-1"></i> Nombre del Rol <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control border-2 fw-bold text-uppercase" id="nombre_rol" name="nombre"
                                   placeholder="Ej: RECEPCIONISTA" maxlength="50" required
                                   style="border-radius: 8px; color: #1e293b; border-color: #cbd5e1; height: 42px;">
                        </div>
                    </div>

                    <!-- 3. Permisos / Módulos -->
                    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden; background-color: #ffffff;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                            <h6 class="fw-bold mb-0" style="color: #ea580c; font-size: 0.95rem;">
                                <i class="fas fa-th-large me-2"></i> Módulos a los que tendrá acceso
                            </h6>
                            <span class="badge fs-6 rounded-pill px-3 py-2 fw-bold shadow-sm" id="lblCantPermisos" style="background-color: #ff6b4a; color: #ffffff;">0 seleccionados</span>
                        </div>

                        <div class="card-body p-3">
                            <!-- Agregado row-gap y h-100 para emparejar alturas de tarjetas -->
                            <div class="row g-3" id="rowPermisos" style="max-height: 350px; overflow-y: auto;">
                                <?php if (!empty($permisosDisponibles)): ?>
                                    <?php foreach ($permisosDisponibles as $permiso): ?>
                                        <div class="col-md-6 d-flex align-items-stretch">
                                            <div class="card w-100 border p-3 style-card-permiso" style="border-color: #e2e8f0 !important; border-radius: 10px; background-color: #fff;">
                                                <div class="form-check d-flex align-items-start m-0 p-0">
                                                    <!-- Checkbox con flex-shrink-0 para evitar deformaciones -->
                                                    <input class="form-check-input chk-permiso flex-shrink-0 me-3 mt-1" type="checkbox"
                                                           name="permisos[]"
                                                           value="<?php echo htmlspecialchars($permiso['nombre']); ?>"
                                                           id="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>"
                                                           style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                                                    
                                                    <label class="form-check-label flex-grow-1" for="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>" style="cursor: pointer;">
                                                        <span class="fw-bold d-block text-uppercase" style="color: #1e293b; font-size: 0.88rem; line-height: 1.2;">
                                                            <?php echo htmlspecialchars($permiso['nombre']); ?>
                                                        </span>
                                                        <?php if (!empty($permiso['descripcion'])): ?>
                                                            <small class="d-block text-muted mt-1" style="font-size: 0.78rem; line-height: 1.3;">
                                                                <?php echo htmlspecialchars($permiso['descripcion']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center text-muted py-4">
                                        No hay módulos registrados en el sistema.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer del Modal -->
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarRol" form="formRol">
                        <i class="fas fa-save me-1"></i> Guardar Rol
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>