$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    const modalUsuarioElement = document.getElementById('modalUsuario');
    const usuarioModal = modalUsuarioElement ? new bootstrap.Modal(modalUsuarioElement) : null;

    const formUsuario = document.getElementById('formUsuario');
    const lblTituloUsuario = document.getElementById('lblTituloUsuario');
    const inputDocumento = document.getElementById('documento_id');
    const inputPassword = document.getElementById('password');
    const reqPassword = document.getElementById('reqPassword');
    const hintPassword = document.getElementById('hintPassword');

    // ==========================================
    // RESTRICCIONES DE ENTRADA EN TIEMPO REAL
    // ==========================================
    $('#documento_id, #telefono').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    $('#nombres, #apellidos').on('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
    });

    $('#username').on('input', function () {
        this.value = this.value.replace(/[^a-zA-Z0-9_]/g, '');
    });

    // ==========================================
    // DATATABLE CONFIGURACIÓN
    // ==========================================
    const tabla = $('#datatable_usuarios').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=usuarios&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            { data: 'documento_id' },
            {
                data: null,
                render: function (row) {
                    return `<span class="fw-bold" style="color: #1e293b;">${row.nombres} ${row.apellidos}</span>`;
                }
            },
            { data: 'username' },
            {
                data: 'rol_name',
                render: function (data) {
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            { data: 'correo' },
            {
                data: 'activo',
                render: function (data) {
                    return data == 1
                        ? '<span class="badge bg-success">ACTIVO</span>'
                        : '<span class="badge bg-danger">INACTIVO</span>';
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonUsuario = encodeURIComponent(JSON.stringify(row));

                    let botones = `
                        <button type="button" class="btn btn-warning text-white btn-editar-usuario rounded-circle" data-usuario="${jsonUsuario}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                            <i class="fas fa-pencil-alt fs-6"></i>
                        </button>
                    `;

                    if (row.activo == 1) {
                        botones += `
                            <button type="button" class="btn btn-danger btn-desactivar-usuario rounded-circle" data-id="${row.documento_id}" data-nombre="${row.nombres} ${row.apellidos}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Desactivar">
                                <i class="fas fa-user-slash text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-usuario rounded-circle" data-id="${row.documento_id}" data-nombre="${row.nombres} ${row.apellidos}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Restaurar">
                                <i class="fas fa-user-check text-white fs-6"></i>
                            </button>
                        `;
                    }

                    return `<div class="d-flex align-items-center justify-content-center gap-2">${botones}</div>`;
                }
            }
        ],
        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            },
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron usuarios",
            emptyTable: "No hay usuarios en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    document.querySelectorAll('#pills-tab-usuarios .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-usuarios .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    function configurarModoPassword(esEdicion) {
        if (esEdicion) {
            if (reqPassword) reqPassword.classList.add('d-none');
            if (hintPassword) hintPassword.style.display = 'block';
            if (inputPassword) inputPassword.required = false;
        } else {
            if (reqPassword) reqPassword.classList.remove('d-none');
            if (hintPassword) hintPassword.style.display = 'none';
            if (inputPassword) inputPassword.required = true;
        }
    }

    const btnNuevo = document.querySelector('.btn-nuevo-usuario');
    if (btnNuevo && usuarioModal) {
        btnNuevo.addEventListener('click', function () {
            if (formUsuario) formUsuario.reset();

            const esEdicionInput = document.getElementById('es_edicion');
            if (esEdicionInput) esEdicionInput.value = '0';

            if (inputDocumento) inputDocumento.readOnly = false;
            configurarModoPassword(false);

            if (lblTituloUsuario) lblTituloUsuario.innerText = 'NUEVO USUARIO';

            usuarioModal.show();
        });
    }

    document.addEventListener('click', function (e) {

        const btnEditar = e.target.closest('.btn-editar-usuario');
        if (btnEditar && usuarioModal) {
            const rawData = btnEditar.getAttribute('data-usuario');
            const row = JSON.parse(decodeURIComponent(rawData));

            fetch(`?c=usuarios&a=obtener&documento_id=${encodeURIComponent(row.documento_id)}`)
                .then(res => res.json())
                .then(result => {
                    if (!result.success) {
                        Swal.fire('Error', result.message || 'No se pudo cargar el usuario', 'error');
                        return;
                    }
                    const data = result.data;

                    if (formUsuario) formUsuario.reset();

                    document.getElementById('es_edicion').value = '1';
                    document.getElementById('documento_id').value = data.documento_id;
                    document.getElementById('rol_name').value = data.rol_name;
                    document.getElementById('username').value = data.username;
                    document.getElementById('nombres').value = data.nombres;
                    document.getElementById('apellidos').value = data.apellidos;
                    document.getElementById('correo').value = data.correo;
                    document.getElementById('telefono').value = data.telefono || '';

                    if (inputDocumento) inputDocumento.readOnly = true;
                    configurarModoPassword(true);

                    if (lblTituloUsuario) lblTituloUsuario.innerText = 'EDITAR USUARIO: ' + data.username;

                    usuarioModal.show();
                })
                .catch(() => Swal.fire('Error', 'Error de conexión al cargar el usuario', 'error'));
        }

        const btnDesactivar = e.target.closest('.btn-desactivar-usuario');
        if (btnDesactivar) {
            const id = btnDesactivar.getAttribute('data-id');
            const nombre = btnDesactivar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Desactivar Usuario?',
                text: `¿Deseas desactivar a "${nombre}"? No podrá iniciar sesión mientras esté inactivo.`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('documento_id', id);

                    fetch('?c=usuarios&a=eliminar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Desactivado', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message || 'Error al desactivar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        const btnRestaurar = e.target.closest('.btn-restaurar-usuario');
        if (btnRestaurar) {
            const id = btnRestaurar.getAttribute('data-id');
            const nombre = btnRestaurar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Restaurar Usuario?',
                text: `¿Deseas volver a activar a "${nombre}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('documento_id', id);

                    fetch('?c=usuarios&a=restaurar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Restaurado', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message || 'Error al restaurar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }
    });

    // ==========================================
    // SUBMIT DEL FORMULARIO CON VALIDACIONES
    // ==========================================
    if (formUsuario) {
        formUsuario.addEventListener('submit', async function (e) {
            e.preventDefault();

            const esEdicion = document.getElementById('es_edicion').value === '1';
            const doc = document.getElementById('documento_id').value.trim();
            const username = document.getElementById('username').value.trim();
            const nombres = document.getElementById('nombres').value.trim();
            const apellidos = document.getElementById('apellidos').value.trim();
            const correo = document.getElementById('correo').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const password = document.getElementById('password').value;
            const rol = document.getElementById('rol_name').value;

            // Validaciones Frontend
            if (!doc || doc.length < 8 || doc.length > 15) {
                return Swal.fire('Atención', 'El documento debe contener entre 8 y 15 números.', 'warning');
            }

            if (!rol) {
                return Swal.fire('Atención', 'Debe seleccionar un rol para el usuario.', 'warning');
            }

            if (!username || username.length < 4 || username.length > 20) {
                return Swal.fire('Atención', 'El nombre de usuario debe tener entre 4 y 20 caracteres.', 'warning');
            }

            if (!esEdicion && (!password || password.length < 6)) {
                return Swal.fire('Atención', 'La contraseña es obligatoria y debe tener al menos 6 caracteres.', 'warning');
            }

            if (esEdicion && password.length > 0 && password.length < 6) {
                return Swal.fire('Atención', 'Si va a cambiar la contraseña, debe tener al menos 6 caracteres.', 'warning');
            }

            if (!nombres || !apellidos) {
                return Swal.fire('Atención', 'Los nombres y apellidos son obligatorios.', 'warning');
            }

            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(correo)) {
                return Swal.fire('Atención', 'Ingrese un correo electrónico válido.', 'warning');
            }

            if (telefono && (telefono.length < 7 || telefono.length > 15)) {
                return Swal.fire('Atención', 'El teléfono debe contener entre 7 y 15 dígitos.', 'warning');
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=usuarios&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'Error al procesar el usuario');
                }

                if (usuarioModal) usuarioModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});