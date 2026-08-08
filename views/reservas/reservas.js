$(document).ready(function () {
    let estadoFiltro = 'CONFIRMADA';

    const modalReservaElement = document.getElementById('modalReserva');
    const reservaModal = modalReservaElement ? new bootstrap.Modal(modalReservaElement) : null;
    const formReserva = document.getElementById('formReserva');
    const lblTituloReserva = document.getElementById('lblTituloReserva');
    const selectHabitacion = document.getElementById('numero_habitacion');
    const inputBuscarCliente = document.getElementById('buscar_cliente');
    const inputClienteDoc = document.getElementById('cliente_doc');
    const resultadosClienteBox = document.getElementById('resultadosClienteBox');
    const clienteSeleccionadoTxt = document.getElementById('clienteSeleccionadoTxt');

    function badgeEstadoReserva(estado) {
        const clases = {
            'CONFIRMADA': 'bg-success',
            'CHECKED_IN': 'bg-primary',
            'CANCELADA': 'bg-danger',
            'NO_SHOW': 'bg-dark'
        };
        return `<span class="badge ${clases[estado] || 'bg-secondary'}">${estado.replace('_', ' ')}</span>`;
    }

    const tabla = $('#datatable_reservas').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=reservas&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            // 1. Cliente
            {
                data: null,
                render: d => `<span class="fw-bold">${d.cliente_nombre}</span><br><small class="text-muted">${d.cliente_tipo_doc}: ${d.cliente_doc}</small>`
            },
            // 2. Habitación
            {
                data: null,
                render: d => `${d.numero_habitacion} <span class="badge bg-secondary">${d.habitacion_tipo}</span>`
            },
            // 3. Check-In
            { data: 'fecha_checkin_prevista' },
            // 4. Check-Out
            { data: 'fecha_checkout_prevista' },
            // 5. Adelanto
            {
                data: 'monto_adelanto',
                render: d => `S/ ${parseFloat(d).toFixed(2)}`
            },
            // 6. Estado
            {
                data: 'estado',
                render: d => badgeEstadoReserva(d)
            },
            // 7. Registrado por
            { data: 'usuario_username' },
            // 8. Acciones
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonRow = encodeURIComponent(JSON.stringify(row));
                    let botones = '';
                    if (row.estado === 'CONFIRMADA') {
                        const jsonRow = encodeURIComponent(JSON.stringify(row));
                        botones += `
                            <button type="button" class="btn btn-warning text-white btn-editar-reserva rounded-circle" data-reserva="${jsonRow}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Editar">
                                <i class="fas fa-pencil-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-secondary text-white btn-noshow-reserva rounded-circle" data-id="${row.id}" data-checkin="${row.fecha_checkin_prevista}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Marcar No-Show">
                                <i class="fas fa-user-slash fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-cancelar-reserva rounded-circle" data-id="${row.id}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Cancelar">
                                <i class="fas fa-times text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `<span class="text-muted small">Sin acciones</span>`;
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
            zeroRecords: "No se encontraron reservas",
            emptyTable: "No hay reservas en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // Pestañas de filtro por estado
    document.querySelectorAll('#pills-tab-reservas .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-reservas .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // Buscador de cliente con autocompletado (?c=clientes&a=buscarAJAX&q)
    let debounceTimer = null;
    if (inputBuscarCliente && resultadosClienteBox) {
        inputBuscarCliente.addEventListener('input', function () {
            inputClienteDoc.value = '';
            clearTimeout(debounceTimer);
            const termino = this.value.trim();
            
            if (termino.length < 2) {
                resultadosClienteBox.innerHTML = '';
                resultadosClienteBox.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('?c=clientes&a=buscarAJAX&q=' + encodeURIComponent(termino))
                    .then(res => res.json())
                    .then(data => {
                        const lista = data.data || data; 
                        
                        if (!lista || !lista.length) {
                            resultadosClienteBox.innerHTML = '<div class="list-group-item text-muted small">No se encontraron clientes</div>';
                            resultadosClienteBox.style.display = 'block';
                            return;
                        }

                        resultadosClienteBox.innerHTML = lista.map(c => `
                            <button type="button" class="list-group-item list-group-item-action btn-opcion-cliente"
                                data-doc="${c.numero_documento || c.doc}" data-nombre="${c.razon_social || c.nombre}" data-tipo="${c.tipo_documento || 'DNI'}">
                                <strong>${c.razon_social || c.nombre}</strong><br>
                                <small class="text-muted">${c.tipo_documento || 'Doc'}: ${c.numero_documento || c.doc}</small>
                            </button>
                        `).join('');
                        resultadosClienteBox.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Error buscando cliente:', err);
                        resultadosClienteBox.style.display = 'none';
                    });
            }, 300);
        });
    }

    document.addEventListener('click', function (e) {
        // Selección de cliente desde el buscador
        const btnOpcionCliente = e.target.closest('.btn-opcion-cliente');
        if (btnOpcionCliente) {
            inputClienteDoc.value = btnOpcionCliente.getAttribute('data-doc');
            inputBuscarCliente.value = btnOpcionCliente.getAttribute('data-nombre');
            clienteSeleccionadoTxt.innerText = 'Documento: ' + btnOpcionCliente.getAttribute('data-tipo') + ' ' + btnOpcionCliente.getAttribute('data-doc');
            resultadosClienteBox.style.display = 'none';
        } else if (!e.target.closest('#resultadosClienteBox') && !e.target.closest('#buscar_cliente')) {
            if (resultadosClienteBox) resultadosClienteBox.style.display = 'none';
        }

        // Editar reserva
        const btnEditar = e.target.closest('.btn-editar-reserva');
        if (btnEditar && reservaModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-reserva')));

            if (formReserva) formReserva.reset();
            document.getElementById('es_edicion').value = '1';
            document.getElementById('id_reserva').value = data.id;

            inputClienteDoc.value = data.cliente_doc;
            inputBuscarCliente.value = data.cliente_nombre;
            inputBuscarCliente.disabled = true;
            clienteSeleccionadoTxt.innerText = 'Documento: ' + data.cliente_tipo_doc + ' ' + data.cliente_doc;

            if (selectHabitacion) {
                if (![...selectHabitacion.options].some(o => o.value === data.numero_habitacion)) {
                    const opt = document.createElement('option');
                    opt.value = data.numero_habitacion;
                    opt.text = data.numero_habitacion + ' — ' + data.habitacion_tipo;
                    selectHabitacion.appendChild(opt);
                }
                selectHabitacion.value = data.numero_habitacion;
                selectHabitacion.disabled = true;
            }

            document.getElementById('fecha_checkin_prevista').value = data.fecha_checkin_prevista;
            document.getElementById('fecha_checkout_prevista').value = data.fecha_checkout_prevista;
            document.getElementById('monto_adelanto').value = parseFloat(data.monto_adelanto).toFixed(2);
            document.getElementById('observaciones').value = data.observaciones || '';

            if (lblTituloReserva) lblTituloReserva.innerText = 'EDITAR RESERVA #' + data.id;
            reservaModal.show();
        }

        // Cancelar reserva
        const btnCancelar = e.target.closest('.btn-cancelar-reserva');
        if (btnCancelar) {
            const id = btnCancelar.getAttribute('data-id');

            Swal.fire({
                title: '¿Cancelar Reserva?',
                text: `¿Deseas cancelar la reserva #${id}? La habitación quedará disponible.`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Cancelar',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('estado', 'CANCELADA');
                    fetch('?c=reservas&a=cambiarEstado', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Cancelada', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al cancelar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // Marcar No-Show
        const btnNoShow = e.target.closest('.btn-noshow-reserva');
        if (btnNoShow) {
            const id = btnNoShow.getAttribute('data-id');
            const checkinStr = btnNoShow.getAttribute('data-checkin');

            // Validar en cliente antes de mostrar el SweetAlert
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            const checkinDate = new Date(checkinStr + 'T00:00:00');

            if (checkinDate > hoy) {
                Swal.fire('Atención', 'No se puede marcar como No-Show una reserva cuya fecha de check-in aún no ha llegado.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Marcar como No-Show?',
                text: `¿El cliente de la reserva #${id} no se presentó?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#495057',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, marcar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('estado', 'NO_SHOW');
                    fetch('?c=reservas&a=cambiarEstado', { method: 'POST', body: formData })
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
    });

    // Abrir modal de creación
    const btnNuevo = document.querySelector('.btn-nueva-reserva');
    if (btnNuevo && reservaModal) {
        btnNuevo.addEventListener('click', function () {
            if (formReserva) formReserva.reset();
            document.getElementById('es_edicion').value = '0';
            document.getElementById('id_reserva').value = '';
            if (inputClienteDoc) inputClienteDoc.value = '';
            if (inputBuscarCliente) inputBuscarCliente.disabled = false;
            if (clienteSeleccionadoTxt) clienteSeleccionadoTxt.innerText = '';
            if (selectHabitacion) selectHabitacion.disabled = false;
            if (lblTituloReserva) lblTituloReserva.innerText = 'NUEVA RESERVA';
            reservaModal.show();
        });
    }

    // Envío del formulario (Guardar / Actualizar)
    if (formReserva) {
        formReserva.addEventListener('submit', async function (e) {
            e.preventDefault();

            const esEdicion = document.getElementById('es_edicion').value === '1';

            if (!inputClienteDoc.value && !esEdicion) {
                Swal.fire('Atención', 'Debe seleccionar un cliente de la lista de resultados.', 'warning');
                return;
            }

            // Validaciones de fechas en cliente
            const checkinVal = document.getElementById('fecha_checkin_prevista').value;
            const checkoutVal = document.getElementById('fecha_checkout_prevista').value;
            const adelantoVal = document.getElementById('monto_adelanto').value;
            const observacionesVal = document.getElementById('observaciones').value;

            if (!checkinVal || !checkoutVal) {
                Swal.fire('Atención', 'Debe completar las fechas de check-in y check-out.', 'warning');
                return;
            }

            const checkinDate = new Date(checkinVal + 'T00:00:00');
            const checkoutDate = new Date(checkoutVal + 'T00:00:00');
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (checkinDate < hoy) {
                Swal.fire('Atención', 'La fecha de check-in prevista no puede ser anterior a la fecha actual.', 'warning');
                return;
            }

            if (checkoutDate <= checkinDate) {
                Swal.fire('Atención', 'La fecha de check-out debe ser posterior a la de check-in.', 'warning');
                return;
            }

            if (isNaN(adelantoVal) || parseFloat(adelantoVal) < 0) {
                Swal.fire('Atención', 'El monto de adelanto debe ser mayor o igual a 0.', 'warning');
                return;
            }

            if (observacionesVal.length > 1000) {
                Swal.fire('Atención', 'Las observaciones no pueden exceder los 1000 caracteres.', 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=reservas&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar la reserva');

                if (reservaModal) reservaModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});