$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    const modalHabitacionElement = document.getElementById('modalHabitacion');
    const habitacionModal = modalHabitacionElement ? new bootstrap.Modal(modalHabitacionElement) : null;
    const formHabitacion = document.getElementById('formHabitacion');
    const lblTituloHabitacion = document.getElementById('lblTituloHabitacion');
    const inputNumero = document.getElementById('numero_habitacion');

    function badgeEstadoDisponibilidad(estado) {
        const clases = {
            'DISPONIBLE': 'bg-success',
            'OCUPADA': 'bg-danger',
            'RESERVADA': 'bg-warning text-dark',
            'MANTENIMIENTO': 'bg-secondary',
            'LIMPIEZA': 'bg-info text-dark'
        };
        return `<span class="badge ${clases[estado] || 'bg-secondary'}">${estado}</span>`;
    }

    const tabla = $('#datatable_habitaciones').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=habitaciones&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            // 1. N° Habitación
            { 
                data: 'numero_habitacion', 
                render: d => `<span class="fw-bold" style="color:#1e293b;">${d}</span>` 
            },
            // 2. Piso
            { 
                data: 'piso', 
                render: d => `Piso ${d}` 
            },
            // 3. Tipo
            { 
                data: 'tipo_nombre', 
                render: d => `<span class="badge bg-secondary">${d}</span>` 
            },
            // 4. Precio Base
            { 
                data: 'precio_base_noche', 
                render: d => `S/ ${parseFloat(d).toFixed(2)}` 
            },
            // 5. Capacidad
            { 
                data: 'capacidad_personas', 
                render: d => d ? `<i class="fas fa-user me-1 text-muted"></i>${d} pers.` : '<span class="text-muted">-</span>' 
            },
            // 6. Estado
            {
                data: null,
                render: function (row) {
                    if (row.activo == 0) return '<span class="badge bg-dark">INACTIVA</span>';
                    return badgeEstadoDisponibilidad(row.estado_disponibilidad);
                }
            },
            // 7. Observaciones
            { 
                data: 'observaciones', 
                render: d => d ? `<span class="text-truncate d-inline-block" style="max-width: 180px;" title="${d}">${d}</span>` : '<span class="text-muted small">Sin observaciones</span>' 
            },
            // 8. Acciones
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonRow = encodeURIComponent(JSON.stringify(row));
                    let botones = '';
                    if (row.activo == 1) {
                        botones += `
                            <button type="button" class="btn btn-warning text-white btn-editar-habitacion rounded-circle" data-habitacion="${jsonRow}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #FFA41D; border-color: #FFA41D;" title="Editar">
                                <i class="fas fa-pencil-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-info text-white btn-cambiar-estado rounded-circle" data-numero="${row.numero_habitacion}" data-estado="${row.estado_disponibilidad}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #d4957c; border-color: #d4957c;" title="Cambiar Estado">
                                <i class="fas fa-sync-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-desactivar-habitacion rounded-circle" data-numero="${row.numero_habitacion}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #e72e2e; border-color: #e72e2e;" title="Desactivar">
                                <i class="fas fa-times text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-habitacion rounded-circle" data-numero="${row.numero_habitacion}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2ab418; border-color: #2ab418;" title="Restaurar">
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
            zeroRecords: "No se encontraron habitaciones",
            emptyTable: "No hay habitaciones en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // Pestañas de filtro (Activas / Inactivas)
    document.querySelectorAll('#pills-tab-habitaciones .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-habitaciones .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // Abrir modal de creación
    const btnNuevo = document.querySelector('.btn-nueva-habitacion');
    if (btnNuevo && habitacionModal) {
        btnNuevo.addEventListener('click', function () {
            if (formHabitacion) formHabitacion.reset();
            const inputEsEdicion = document.getElementById('es_edicion');
            if (inputEsEdicion) inputEsEdicion.value = '0';
            if (inputNumero) inputNumero.readOnly = false;
            if (lblTituloHabitacion) lblTituloHabitacion.innerText = 'NUEVA HABITACIÓN';
            habitacionModal.show();
        });
    }

    // Delegación de eventos para botones dinámicos en la tabla
    document.addEventListener('click', function (e) {
        // Botón Editar
        const btnEditar = e.target.closest('.btn-editar-habitacion');
        if (btnEditar && habitacionModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-habitacion')));

            if (formHabitacion) formHabitacion.reset();
            
            const inputEsEdicion = document.getElementById('es_edicion');
            if (inputEsEdicion) inputEsEdicion.value = '1';

            if (inputNumero) {
                inputNumero.value = data.numero_habitacion;
                inputNumero.readOnly = true;
            }

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val || '';
            };

            setVal('piso', data.piso);
            setVal('id_tipo_habitacion', data.id_tipo_habitacion);
            setVal('precio_base_noche', data.precio_base_noche);
            setVal('capacidad_personas', data.capacidad_personas);
            setVal('observaciones', data.observaciones);

            if (lblTituloHabitacion) lblTituloHabitacion.innerText = 'EDITAR HABITACIÓN: ' + data.numero_habitacion;
            habitacionModal.show();
        }

        // Botón Cambiar Estado de Disponibilidad
        const btnEstado = e.target.closest('.btn-cambiar-estado');
        if (btnEstado) {
            const numero = btnEstado.getAttribute('data-numero');
            const estadoActual = btnEstado.getAttribute('data-estado');

            Swal.fire({
                title: `Cambiar estado — Habitación ${numero}`,
                input: 'select',
                inputOptions: {
                    DISPONIBLE: 'DISPONIBLE',
                    OCUPADA: 'OCUPADA',
                    RESERVADA: 'RESERVADA',
                    MANTENIMIENTO: 'MANTENIMIENTO',
                    LIMPIEZA: 'LIMPIEZA'
                },
                inputValue: estadoActual,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const formData = new FormData();
                    formData.append('numero_habitacion', numero);
                    formData.append('estado_disponibilidad', result.value);

                    fetch('?c=habitaciones&a=updateEstado', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Actualizado', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message || 'Error al actualizar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // Botón Desactivar
        const btnDesactivar = e.target.closest('.btn-desactivar-habitacion');
        if (btnDesactivar) {
            const numero = btnDesactivar.getAttribute('data-numero');

            Swal.fire({
                title: '¿Desactivar Habitación?',
                text: `¿Deseas desactivar la habitación ${numero}?`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('numero_habitacion', numero);
                    fetch('?c=habitaciones&a=eliminar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Desactivada', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al desactivar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // Botón Restaurar
        const btnRestaurar = e.target.closest('.btn-restaurar-habitacion');
        if (btnRestaurar) {
            const numero = btnRestaurar.getAttribute('data-numero');

            Swal.fire({
                title: '¿Restaurar Habitación?',
                text: `¿Deseas volver a activar la habitación ${numero}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('numero_habitacion', numero);
                    fetch('?c=habitaciones&a=restaurar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Restaurada', data.message, 'success');
                            } else {
                                Swal.fire('Error', data.message || 'Error al restaurar', 'error');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }
    });

    // Envío del formulario (Guardar / Actualizar)
    if (formHabitacion) {
        formHabitacion.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('?c=habitaciones&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar la habitación');

                if (habitacionModal) habitacionModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});