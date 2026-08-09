$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    const modalTipoElement = document.getElementById('modalTipo');
    const tipoModal = modalTipoElement ? new bootstrap.Modal(modalTipoElement) : null;
    const formTipo = document.getElementById('formTipo');
    const lblTituloTipo = document.getElementById('lblTituloTipo');

    const limpiarValidacionesFormulario = () => {
        if (!formTipo) return;
        formTipo.classList.remove('was-validated');
        formTipo.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    };

    const tabla = $('#datatable_tipos').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=tipos_vehiculo&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'nombre', render: d => `<span class="fw-bold" style="color:#1e293b;">${d}</span>` },
            { 
                data: 'descripcion', 
                render: d => d ? `<span class="text-muted small">${d}</span>` : '<em class="text-muted small">Sin descripción</em>' 
            },
            { data: 'precio_base_dia', render: d => `S/ ${parseFloat(d).toFixed(2)}` },
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
                    const jsonRow = encodeURIComponent(JSON.stringify(row));
                    let botones = '';
                    if (row.activo == 1) {
                        botones += `
                            <button type="button" class="btn btn-warning text-white btn-editar-tipo rounded-circle" data-tipo="${jsonRow}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #FFA41D; border-color: #FFA41D;" title="Editar">
                                <i class="fas fa-pencil-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-desactivar-tipo rounded-circle" data-id="${row.id}" data-nombre="${row.nombre}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #e72e2e; border-color: #e72e2e;" title="Desactivar">
                                <i class="fas fa-times text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-tipo rounded-circle" data-id="${row.id}" data-nombre="${row.nombre}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Restaurar">
                                <i class="fas fa-check text-white fs-6"></i>
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
            zeroRecords: "No se encontraron tipos de vehículo",
            emptyTable: "No hay tipos en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    document.querySelectorAll('#pills-tab-tipos .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-tipos .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    const btnNuevo = document.querySelector('.btn-nuevo-tipo');
    if (btnNuevo && tipoModal) {
        btnNuevo.addEventListener('click', function () {
            if (formTipo) formTipo.reset();
            limpiarValidacionesFormulario();
            document.getElementById('tipo_id_hidden').value = '';
            if (lblTituloTipo) lblTituloTipo.innerText = 'NUEVO TIPO';
            tipoModal.show();
        });
    }

    document.addEventListener('click', function (e) {
        const btnEditar = e.target.closest('.btn-editar-tipo');
        if (btnEditar && tipoModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-tipo')));

            if (formTipo) formTipo.reset();
            limpiarValidacionesFormulario();

            document.getElementById('tipo_id_hidden').value = data.id;
            document.getElementById('nombre_tipo').value = data.nombre;
            document.getElementById('precio_base_dia').value = data.precio_base_dia;
            document.getElementById('descripcion_tipo').value = data.descripcion || '';
            if (lblTituloTipo) lblTituloTipo.innerText = 'EDITAR TIPO: ' + data.nombre;

            tipoModal.show();
        }

        const btnDesactivar = e.target.closest('.btn-desactivar-tipo');
        if (btnDesactivar) {
            const id = btnDesactivar.getAttribute('data-id');
            const nombre = btnDesactivar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Desactivar Tipo?',
                text: `¿Deseas desactivar "${nombre}"?`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=tipos_vehiculo&a=eliminar', { method: 'POST', body: formData })
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

        const btnRestaurar = e.target.closest('.btn-restaurar-tipo');
        if (btnRestaurar) {
            const id = btnRestaurar.getAttribute('data-id');
            const nombre = btnRestaurar.getAttribute('data-nombre');

            Swal.fire({
                title: '¿Restaurar Tipo?',
                text: `¿Deseas volver a activar "${nombre}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=tipos_vehiculo&a=restaurar', { method: 'POST', body: formData })
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

    if (formTipo) {
        formTipo.addEventListener('submit', async function (e) {
            e.preventDefault();

            const nombre = document.getElementById('nombre_tipo').value.trim();
            const precio = parseFloat(document.getElementById('precio_base_dia').value);
            const descripcion = document.getElementById('descripcion_tipo').value.trim();

            if (nombre.length < 3 || nombre.length > 50) {
                formTipo.classList.add('was-validated');
                Swal.fire('Atención', 'El nombre debe contener entre 3 y 50 caracteres.', 'warning');
                return;
            }

            if (isNaN(precio) || precio <= 0) {
                formTipo.classList.add('was-validated');
                Swal.fire('Atención', 'El precio base por día debe ser mayor a S/ 0.00.', 'warning');
                return;
            }

            if (descripcion.length > 500) {
                formTipo.classList.add('was-validated');
                Swal.fire('Atención', 'La descripción no puede superar los 500 caracteres.', 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=tipos_vehiculo&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();

                if (!data.success) {
                    Swal.fire('Atención', data.message || 'Error al procesar el tipo de vehículo', 'warning');
                    return;
                }

                if (tipoModal) tipoModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
            }
        });
    }
});