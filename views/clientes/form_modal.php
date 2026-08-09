<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #d9383a;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalClienteLabel">
                        <i class="fas fa-user me-2"></i> <span id="lblTituloCliente">NUEVO CLIENTE</span>
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCliente" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <input type="hidden" id="es_edicion" name="es_edicion" value="0">
                    <input type="hidden" id="numero_documento_original" name="numero_documento_original" value="">

                    <div class="row g-3">
                        <!-- Tipo de Documento -->
                        <div class="col-md-4">
                            <label for="tipo_documento" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Tipo de Documento <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="tipo_documento" name="tipo_documento" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carné de Extranjería</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un tipo de documento.</div>
                        </div>

                        <!-- N° Documento -->
                        <div class="col-md-8">
                            <label for="numero_documento" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">N° Documento <span class="text-danger">*</span></label>
                            <!-- Configurado por defecto para DNI (8 dígitos) -->
                            <input type="text" class="form-control border-2" id="numero_documento" name="numero_documento" minlength="8" maxlength="8" required placeholder="Ej: 12345678" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">El DNI debe tener exactamente 8 dígitos numéricos.</div>
                            <small class="text-muted d-block mt-1" id="hintDocumentoBloqueado" style="display:none;">
                                <i class="fas fa-info-circle"></i> ¿Se equivocó al digitarlo? Ciérrelo y use el botón <i class="fas fa-id-card"></i> "Corregir documento" desde la lista.
                            </small>
                        </div>

                        <!-- Razón Social / Nombre -->
                        <div class="col-12">
                            <label for="razon_social" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Razón Social / Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control border-2" id="razon_social" name="razon_social" maxlength="150" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Este campo es obligatorio (máx. 150 caracteres).</div>
                        </div>

                        <!-- Licencia de Conducir -->
                        <div class="col-md-6">
                            <label for="licencia_conducir" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Licencia de Conducir</label>
                            <input type="text" class="form-control border-2 text-uppercase" id="licencia_conducir" name="licencia_conducir" maxlength="30" placeholder="Ej: Q12345678" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Máximo 30 caracteres.</div>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6">
                            <label for="telefono" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Teléfono</label>
                            <input type="text" class="form-control border-2" id="telefono" name="telefono" maxlength="20" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese un teléfono válido.</div>
                        </div>

                        <!-- Correo -->
                        <div class="col-md-6">
                            <label for="correo" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Correo</label>
                            <input type="email" class="form-control border-2" id="correo" name="correo" maxlength="100" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">Ingrese un correo válido.</div>
                        </div>

                        <!-- País -->
                        <div class="col-md-6 position-relative">
                            <label for="inputPais" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">País <span class="text-danger">*</span></label>
                            
                            <!-- Input fijo donde el usuario escribe o hace clic -->
                            <input type="text" class="form-control border-2" id="inputPais" name="pais" value="Perú" required autocomplete="off" placeholder="Seleccione o escriba un país..." style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            
                            <!-- Contenedor flotante con scroll para la lista -->
                            <div id="listaPaisesFlotante" class="shadow-sm border rounded bg-white position-absolute w-100 mt-1" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1055; left: 0;">
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Perú</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Afganistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Albania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Alemania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Andorra</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Angola</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Antigua y Barbuda</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Arabia Saudita</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Argelia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Argentina</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Armenia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Australia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Austria</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Azerbaiyán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bahamas</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bangladés</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Barbados</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Baréin</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bélgica</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Belice</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Benín</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bielorrusia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Birmania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bolivia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bosnia y Herzegovina</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Botsuana</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Brasil</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Brunéi</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bulgaria</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Burkina Faso</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Burundi</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Bután</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Cabo Verde</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Camboya</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Camerún</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Canadá</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Catar</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Chad</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Chile</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">China</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Chipre</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Ciudad del Vaticano</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Colombia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Comoras</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Corea del Norte</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Corea del Sur</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Costa de Marfil</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Costa Rica</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Croacia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Cuba</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Dinamarca</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Dominica</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Ecuador</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Egipto</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">El Salvador</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Emiratos Árabes Unidos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Eritrea</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Eslovaquia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Eslovenia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">España</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Estados Unidos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Estonia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Etiopía</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Filipinas</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Finlandia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Fiyi</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Francia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Gabón</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Gambia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Georgia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Ghana</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Granada</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Grecia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Guatemala</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Guyana</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Guinea</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Guinea ecuatorial</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Guinea-Bisáu</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Haití</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Honduras</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Hungría</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">India</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Indonesia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Irak</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Irán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Irlanda</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Islandia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Islas Marshall</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Islas Salomón</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Israel</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Italia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Jamaica</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Japón</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Jordania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Kazajistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Kenia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Kirguistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Kiribati</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Kuwait</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Laos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Lesoto</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Letonia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Líbano</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Liberia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Libia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Liechtenstein</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Lituania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Luxemburgo</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Macedonia del Norte</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Madagascar</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Malasia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Malaui</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Maldivas</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Malí</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Malta</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Marruecos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Mauricio</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Mauritania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">México</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Micronesia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Moldavia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Mónaco</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Mongolia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Montenegro</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Mozambique</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Namibia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Nauru</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Nepal</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Nicaragua</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Níger</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Nigeria</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Noruega</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Nueva Zelanda</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Omán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Países Bajos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Pakistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Palaos</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Palestina</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Panamá</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Papúa Nueva Guinea</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Paraguay</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Polonia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Portugal</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Reino Unido</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">República Centroafricana</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">República Checa</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">República del Congo</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">República Democrática del Congo</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">República Dominicana</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Ruanda</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Rumanía</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Rusia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Samoa</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">San Cristóbal y Nieves</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">San Marino</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">San Vicente y las Granadinas</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Santa Lucía</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Santo Tomé y Príncipe</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Senegal</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Serbia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Seychelles</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Sierra Leona</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Singapur</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Siria</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Somalia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Sri Lanka</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Suazilandia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Sudáfrica</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Sudán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Sudán del Sur</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Suecia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Suiza</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Surinam</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Tailandia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Tanzania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Tayikistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Timor Oriental</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Togo</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Tonga</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Trinidad y Tobago</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Túnez</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Turkmenistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Turquía</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Tuvalu</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Ucrania</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Uganda</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Uruguay</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Uzbekistán</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Vanuatu</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Venezuela</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Vietnam</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Yemen</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Yibuti</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Zambia</div>
                                <div class="pais-item px-3 py-2 cursor-pointer border-bottom" style="cursor: pointer;">Zimbabue</div>
                            </div>
                            <div class="invalid-feedback">Seleccione un país.</div>
                        </div>
                        <!-- Dirección -->
                        <div class="col-md-12">
                            <label for="direccion" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Dirección</label>
                            <textarea class="form-control border-2" id="direccion" name="direccion" rows="2" style="border-radius: 8px; border-color: #cbd5e1;"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d; color: #ffffff;"><i class="fas fa-times me-1"></i> Cerrar</button>
                    <button type="submit" class="btn text-white" id="btnGuardarCliente" form="formCliente" style="background-color: #d9383a; border-color: #d9383a;"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Corregir Tipo/Número de Documento (error de digitación en el PK) -->
<div class="modal fade" id="modalCambiarDocumento" tabindex="-1" aria-labelledby="modalCambiarDocumentoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <div class="modal-header py-3 position-relative" style="background-color: #0b667c;">
                <div class="w-100 text-center">
                    <h4 class="modal-title fw-bold text-white mb-0" id="modalCambiarDocumentoLabel">
                        <i class="fas fa-id-card me-2"></i> Corregir Documento
                    </h4>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formCambiarDocumento" autocomplete="off" novalidate>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="alert small" style="background-color: #e6f0f3; color: #0b667c; border: 1px solid #b3d4dc; border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Esto corrige un error de digitación en el documento del cliente. El cambio se
                        aplicará automáticamente a todo su historial (reservas, estadías, comprobantes).
                    </div>

                    <input type="hidden" id="numero_documento_actual" name="numero_documento_actual" value="">

                    <div class="mb-3">
                        <label class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Documento Actual</label>
                        <input type="text" class="form-control" id="documento_actual_display" disabled style="border-radius: 8px;">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="nuevo_tipo_documento" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Nuevo Tipo <span class="text-danger">*</span></label>
                            <select class="form-select border-2" id="nuevo_tipo_documento" name="nuevo_tipo_documento" required style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carné de Extranjería</option>
                                <option value="PASAPORTE">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="nuevo_numero_documento" class="form-label fw-bold" style="color: #d9383a; font-size: 0.9rem;">Nuevo Número <span class="text-danger">*</span></label>
                            <!-- Configurado inicialmente para DNI -->
                            <input type="text" class="form-control border-2" id="nuevo_numero_documento" name="nuevo_numero_documento" minlength="8" maxlength="8" required placeholder="Ej: 12345678" style="border-radius: 8px; border-color: #cbd5e1; height: 42px;">
                            <div class="invalid-feedback">El DNI debe tener exactamente 8 dígitos numéricos.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn text-white" data-bs-dismiss="modal" style="background-color: #6c757d; border-color: #6c757d;"><i class="fas fa-times me-1"></i> Cancelar</button>
                    <button type="submit" class="btn text-white" id="btnCambiarDocumento" form="formCambiarDocumento" style="background-color: #0b667c; border-color: #0b667c;"><i class="fas fa-check me-1"></i> Corregir</button>
                </div>
            </form>
        </div>
    </div>
</div>