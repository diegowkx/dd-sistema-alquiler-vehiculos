<div class="modal fade" id="modalRol" tabindex="-1" aria-labelledby="modalRolLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden; background-color: #ffffff;">

            <!-- Encabezado con línea divisoria roja suave -->
            <div class="modal-header border-0 pt-4 px-4 pb-3 d-flex align-items-center justify-content-between" style="border-bottom: 2px solid #fce8e8 !important;">
                <h5 class="modal-title fw-bold text-dark fs-5 mb-0" id="modalRolLabel">
                    <i class="fas fa-user-shield me-2" style="color: #d9383a;"></i><span id="lblTituloRol">NUEVO ROL</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formRol" autocomplete="off">
                <div class="modal-body p-4">
                    <input type="hidden" id="rol_id_hidden" name="id" value="">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">

                    <!-- Campo Nombre del Rol -->
                    <div class="mb-4">
                        <label for="nombre_rol" class="form-label fw-semibold small" style="color: #475569;">
                            Nombre del Rol <span style="color: #d9383a;">*</span>
                        </label>
                        <input type="text" class="form-control fw-bold text-uppercase" id="nombre_rol" name="nombre" placeholder="Ej: RECEPCIONISTA" maxlength="50" required style="border-radius: 12px; border: 1.5px solid #cbd5e1; background-color: #f8fafc; height: 42px; color: #1e293b;">
                    </div>

                    <!-- Sección Módulos y Permisos -->
                    <div class="card border-0 shadow-none p-3" style="border-radius: 16px; background-color: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0 small text-uppercase" style="color: #475569; letter-spacing: 0.5px;">
                                <i class="fas fa-th-large me-2" style="color: #d9383a;"></i>Módulos con Acceso
                            </h6>
                            <span class="badge rounded-pill px-3 py-2 fw-semibold" id="lblCantPermisos" style="background-color: #fce8e8; color: #d9383a;">
                                0 seleccionados
                            </span>
                        </div>

                        <div class="row g-3" id="rowPermisos" style="max-height: 320px; overflow-y: auto;">
                            <?php if (!empty($permisosDisponibles)): ?>
                                <?php foreach ($permisosDisponibles as $permiso): ?>
                                    <div class="col-md-6 d-flex align-items-stretch">
                                        <div class="card w-100 border-0 shadow-sm p-3 style-card-permiso" style="border-radius: 12px; background-color: #ffffff;">
                                            <div class="form-check d-flex align-items-start m-0 p-0">
                                                <input class="form-check-input chk-permiso flex-shrink-0 me-3 mt-1" type="checkbox" name="permisos[]" value="<?php echo htmlspecialchars($permiso['nombre']); ?>" id="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>" style="width: 1.2rem; height: 1.2rem; cursor: pointer;">
                                                
                                                <label class="form-check-label flex-grow-1" for="permiso_<?php echo htmlspecialchars($permiso['nombre']); ?>" style="cursor: pointer;">
                                                    <span class="fw-bold d-block text-uppercase" style="color: #1e293b; font-size: 0.85rem;">
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
                                <div class="col-12 text-center text-muted py-4 small">
                                    No hay módulos registrados en el sistema.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

                <!-- Pie del modal -->
                <div class="modal-footer border-0 pb-4 px-4 pt-2 d-flex justify-content-end gap-2">
                    <button type="button" class="btn border-0 fw-semibold px-4 py-2" data-bs-dismiss="modal" style="background-color: #fce8e8; color: #d9383a; border-radius: 50px;">
                        <i class="fas fa-times me-1"></i> Cerrar
                    </button>
                    <button type="submit" class="btn text-white fw-semibold px-4 py-2" id="btnGuardarRol" form="formRol" style="background-color: #d9383a; border-radius: 50px; border: none;">
                        <i class="fas fa-save me-1"></i> Guardar Rol
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>