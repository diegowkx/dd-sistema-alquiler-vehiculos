$(document).ready(function () {
    let estadoFiltro = 'EN_CURSO';

    const modalEstadiaElement = document.getElementById('modalEstadia');
    const estadiaModal = modalEstadiaElement ? new bootstrap.Modal(modalEstadiaElement) : null;
    const lblTituloEstadia = document.getElementById('lblTituloEstadia');
    const btnGuardarEstadia = document.getElementById('btnGuardarEstadia');

    const seccionCheckin = document.getElementById('seccionCheckin');
    const seccionCargos = document.getElementById('seccionCargos');

    const formEstadia = document.getElementById('formEstadia');
    const modoInput = document.getElementById('modo_checkin');
    const btnModoReserva = document.getElementById('btnModoReserva');
    const btnModoWalkin = document.getElementById('btnModoWalkin');
    const bloqueReserva = document.getElementById('bloqueReserva');
    const bloqueWalkin = document.getElementById('bloqueWalkin');

    const inputBuscarReserva = document.getElementById('buscar_reserva');
    const inputReservaId = document.getElementById('reserva_id');
    const resultadosReservaBox = document.getElementById('resultadosReservaBox');
    const reservaSeleccionadaTxt = document.getElementById('reservaSeleccionadaTxt');

    const inputBuscarCliente = document.getElementById('buscar_cliente');
    const inputClienteDoc = document.getElementById('cliente_doc');
    const resultadosClienteBox = document.getElementById('resultadosClienteBox');
    const clienteSeleccionadoTxt = document.getElementById('clienteSeleccionadoTxt');

    const selectHabitacion = document.getElementById('numero_habitacion');
    const inputPrecioNoche = document.getElementById('precio_noche_pactado');

    const tablaCargosBody = document.getElementById('tablaCargosBody');
    const totalCargosTxt = document.getElementById('totalCargosTxt');
    const estadiaIdCargo = document.getElementById('estadia_id_cargo');
    const formAgregarCargo = document.getElementById('formAgregarCargo');

    function badgeEstadoEstadia(estado) {
        const clases = {
            'EN_CURSO': 'bg-primary',
            'FINALIZADA': 'bg-success',
            'ANULADA': 'bg-danger'
        };
        return `<span class="badge ${clases[estado] || 'bg-secondary'}">${estado.replace('_', ' ')}</span>`;
    }

    const tabla = $('#datatable_estadias').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=estadias&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: d => `<span class="fw-bold">${d.cliente_nombre}</span><br><small class="text-muted">${d.cliente_tipo_doc}: ${d.cliente_doc}</small>`
            },
            {
                data: null,
                render: d => `${d.numero_habitacion} <span class="badge bg-secondary">${d.habitacion_tipo}</span>`
            },
            { data: 'fecha_hora_checkin' },
            {
                data: 'fecha_hora_checkout',
                render: d => d || '<span class="text-muted">—</span>'
            },
            {
                data: 'precio_noche_pactado',
                render: d => `S/ ${parseFloat(d).toFixed(2)}`
            },
            {
                data: 'total_cargos',
                render: d => `S/ ${parseFloat(d).toFixed(2)}`
            },
            {
                data: 'estado',
                render: d => badgeEstadoEstadia(d)
            },
            { data: 'usuario_checkin' },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    let botones = '';
                    if (row.estado === 'EN_CURSO') {
                        botones += `
                            <button type="button" class="btn btn-info text-white btn-cargos-estadia rounded-circle" data-id="${row.id}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Ver / Agregar cargos">
                                <i class="fas fa-receipt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-success text-white btn-checkout-estadia rounded-circle" data-id="${row.id}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Finalizar (Check-Out)">
                                <i class="fas fa-check fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-anular-estadia rounded-circle" data-id="${row.id}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Anular">
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
            zeroRecords: "No se encontraron estadías",
            emptyTable: "No hay estadías en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // Pestañas de filtro por estado
    document.querySelectorAll('#pills-tab-estadias .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-estadias .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // --- Toggle de modo (Reserva / Walk-in) ---
    function activarModoReserva() {
        modoInput.value = 'reserva';
        bloqueReserva.style.display = '';
        bloqueWalkin.style.display = 'none';
        btnModoReserva.classList.add('active', 'btn-outline-primary');
        btnModoReserva.classList.remove('btn-outline-secondary');
        btnModoWalkin.classList.remove('active', 'btn-outline-primary');
        btnModoWalkin.classList.add('btn-outline-secondary');
    }
    function activarModoWalkin() {
        modoInput.value = 'walkin';
        bloqueReserva.style.display = 'none';
        bloqueWalkin.style.display = '';
        btnModoWalkin.classList.add('active', 'btn-outline-primary');
        btnModoWalkin.classList.remove('btn-outline-secondary');
        btnModoReserva.classList.remove('active', 'btn-outline-primary');
        btnModoReserva.classList.add('btn-outline-secondary');
    }
    if (btnModoReserva) btnModoReserva.addEventListener('click', activarModoReserva);
    if (btnModoWalkin) btnModoWalkin.addEventListener('click', activarModoWalkin);

    function resetFormEstadia() {
        if (formEstadia) formEstadia.reset();
        activarModoReserva();
        inputReservaId.value = '';
        if (reservaSeleccionadaTxt) reservaSeleccionadaTxt.innerText = '';
        if (resultadosReservaBox) { resultadosReservaBox.innerHTML = ''; resultadosReservaBox.style.display = 'none'; }
        inputClienteDoc.value = '';
        if (clienteSeleccionadoTxt) clienteSeleccionadoTxt.innerText = '';
        if (resultadosClienteBox) { resultadosClienteBox.innerHTML = ''; resultadosClienteBox.style.display = 'none'; }
        if (inputPrecioNoche) inputPrecioNoche.value = '';
    }

    // --- Buscador de Reservas CONFIRMADA (?c=estadias&a=buscarReservasAJAX) ---
    let debounceReserva = null;
    if (inputBuscarReserva && resultadosReservaBox) {
        inputBuscarReserva.addEventListener('input', function () {
            inputReservaId.value = '';
            clearTimeout(debounceReserva);
            const termino = this.value.trim();

            if (termino.length < 2) {
                resultadosReservaBox.innerHTML = '';
                resultadosReservaBox.style.display = 'none';
                return;
            }

            debounceReserva = setTimeout(() => {
                fetch('?c=estadias&a=buscarReservasAJAX&q=' + encodeURIComponent(termino))
                    .then(res => res.json())
                    .then(data => {
                        const lista = data.data || [];

                        if (!lista.length) {
                            resultadosReservaBox.innerHTML = '<div class="list-group-item text-muted small">No se encontraron reservas confirmadas</div>';
                            resultadosReservaBox.style.display = 'block';
                            return;
                        }

                        resultadosReservaBox.innerHTML = lista.map(r => `
                            <button type="button" class="list-group-item list-group-item-action btn-opcion-reserva"
                                data-id="${r.id}"
                                data-precio="${r.precio_base_noche}"
                                data-texto="${r.cliente_nombre} — Hab. ${r.numero_habitacion} — ${r.fecha_checkin_prevista} a ${r.fecha_checkout_prevista}">
                                <strong>${r.cliente_nombre}</strong> <small class="text-muted">(${r.cliente_tipo_doc}: ${r.cliente_doc})</small><br>
                                <small class="text-muted">Hab. ${r.numero_habitacion} — ${r.habitacion_tipo} · ${r.fecha_checkin_prevista} → ${r.fecha_checkout_prevista} · Adelanto S/ ${parseFloat(r.monto_adelanto).toFixed(2)}</small>
                            </button>
                        `).join('');
                        resultadosReservaBox.style.display = 'block';
                    })
                    .catch(err => {
                        console.error('Error buscando reservas:', err);
                        resultadosReservaBox.style.display = 'none';
                    });
            }, 300);
        });
    }

    // --- Buscador de Clientes (walk-in), igual patrón que Reservas (?c=clientes&a=buscarAJAX) ---
    let debounceCliente = null;
    if (inputBuscarCliente && resultadosClienteBox) {
        inputBuscarCliente.addEventListener('input', function () {
            inputClienteDoc.value = '';
            clearTimeout(debounceCliente);
            const termino = this.value.trim();

            if (termino.length < 2) {
                resultadosClienteBox.innerHTML = '';
                resultadosClienteBox.style.display = 'none';
                return;
            }

            debounceCliente = setTimeout(() => {
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

    // Autocompletar precio al elegir habitación en modo walk-in
    if (selectHabitacion) {
        selectHabitacion.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const precio = opt ? opt.getAttribute('data-precio') : null;
            if (precio && inputPrecioNoche) inputPrecioNoche.value = parseFloat(precio).toFixed(2);
        });
    }

    function renderCargos(cargos, total) {
        if (!cargos.length) {
            tablaCargosBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted small">Sin cargos registrados</td></tr>';
        } else {
            tablaCargosBody.innerHTML = cargos.map(c => `
                <tr>
                    <td>${c.concepto}</td>
                    <td class="text-center">${c.cantidad}</td>
                    <td class="text-end">S/ ${parseFloat(c.precio_unitario).toFixed(2)}</td>
                    <td class="text-end">S/ ${parseFloat(c.subtotal).toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-cargo" data-id="${c.id}" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
        totalCargosTxt.innerText = `S/ ${parseFloat(total).toFixed(2)}`;
    }

    function abrirModalCargos(id) {
        fetch('?c=estadias&a=obtener&id=' + id)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire('Error', data.message || 'No se pudo cargar la estadía', 'error');
                    return;
                }
                const e = data.data;

                seccionCheckin.style.display = 'none';
                seccionCargos.style.display = '';
                btnGuardarEstadia.style.display = 'none';

                document.getElementById('resumenCliente').innerText = `${e.cliente_nombre} (${e.cliente_tipo_doc}: ${e.cliente_doc})`;
                document.getElementById('resumenHabitacion').innerText = `Habitación ${e.numero_habitacion} — ${e.habitacion_tipo} — S/ ${parseFloat(e.precio_noche_pactado).toFixed(2)}/noche`;
                document.getElementById('resumenCheckin').innerText = `Check-In: ${e.fecha_hora_checkin}`;

                estadiaIdCargo.value = e.id;
                renderCargos(e.cargos, e.total_cargos);

                if (lblTituloEstadia) lblTituloEstadia.innerText = 'CARGOS — ESTADÍA #' + e.id;
                if (estadiaModal) estadiaModal.show();
            })
            .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
    }

    document.addEventListener('click', function (e) {
        // Selección de reserva desde el buscador
        const btnOpcionReserva = e.target.closest('.btn-opcion-reserva');
        if (btnOpcionReserva) {
            inputReservaId.value = btnOpcionReserva.getAttribute('data-id');
            inputBuscarReserva.value = btnOpcionReserva.getAttribute('data-texto');
            if (reservaSeleccionadaTxt) reservaSeleccionadaTxt.innerText = 'Reserva #' + btnOpcionReserva.getAttribute('data-id') + ' seleccionada';
            const precio = btnOpcionReserva.getAttribute('data-precio');
            if (precio && inputPrecioNoche) inputPrecioNoche.value = parseFloat(precio).toFixed(2);
            resultadosReservaBox.style.display = 'none';
        } else if (!e.target.closest('#resultadosReservaBox') && !e.target.closest('#buscar_reserva')) {
            if (resultadosReservaBox) resultadosReservaBox.style.display = 'none';
        }

        // Selección de cliente desde el buscador (walk-in)
        const btnOpcionCliente = e.target.closest('.btn-opcion-cliente');
        if (btnOpcionCliente) {
            inputClienteDoc.value = btnOpcionCliente.getAttribute('data-doc');
            inputBuscarCliente.value = btnOpcionCliente.getAttribute('data-nombre');
            clienteSeleccionadoTxt.innerText = 'Documento: ' + btnOpcionCliente.getAttribute('data-tipo') + ' ' + btnOpcionCliente.getAttribute('data-doc');
            resultadosClienteBox.style.display = 'none';
        } else if (!e.target.closest('#resultadosClienteBox') && !e.target.closest('#buscar_cliente')) {
            if (resultadosClienteBox) resultadosClienteBox.style.display = 'none';
        }

        // Ver / agregar cargos
        const btnCargos = e.target.closest('.btn-cargos-estadia');
        if (btnCargos) {
            abrirModalCargos(btnCargos.getAttribute('data-id'));
        }

        // Eliminar un cargo
        const btnEliminarCargo = e.target.closest('.btn-eliminar-cargo');
        if (btnEliminarCargo) {
            const idCargo = btnEliminarCargo.getAttribute('data-id');
            Swal.fire({
                title: '¿Eliminar cargo?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', idCargo);
                    formData.append('estadia_id', estadiaIdCargo.value);
                    fetch('?c=estadias&a=eliminarCargo', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                renderCargos(data.data.cargos, data.data.total);
                                tabla.ajax.reload(null, false);
                            } else {
                                Swal.fire('Atención', data.message || 'No se pudo eliminar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // Finalizar estadía (checkout)
        const btnCheckout = e.target.closest('.btn-checkout-estadia');
        if (btnCheckout) {
            const id = btnCheckout.getAttribute('data-id');
            Swal.fire({
                title: '¿Finalizar Estadía?',
                text: `¿Deseas hacer el check-out de la estadía #${id}? La habitación quedará disponible.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=estadias&a=checkout', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Listo', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al finalizar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        // Anular estadía
        const btnAnular = e.target.closest('.btn-anular-estadia');
        if (btnAnular) {
            const id = btnAnular.getAttribute('data-id');
            Swal.fire({
                title: '¿Anular Estadía?',
                text: `Esto revertirá la reserva (si existía) y liberará la habitación. #${id}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=estadias&a=anular', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Anulada', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al anular', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }
    });

    // Abrir modal de creación (Nuevo Check-In)
    const btnNuevo = document.querySelector('.btn-nuevo-checkin');
    if (btnNuevo && estadiaModal) {
        btnNuevo.addEventListener('click', function () {
            resetFormEstadia();
            seccionCheckin.style.display = '';
            seccionCargos.style.display = 'none';
            btnGuardarEstadia.style.display = '';
            if (lblTituloEstadia) lblTituloEstadia.innerText = 'NUEVO CHECK-IN';
            estadiaModal.show();
        });
    }

    // Envío del formulario de Check-In
    if (formEstadia) {
        formEstadia.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (modoInput.value === 'reserva' && !inputReservaId.value) {
                Swal.fire('Atención', 'Debe seleccionar una reserva de la lista de resultados.', 'warning');
                return;
            }
            if (modoInput.value === 'walkin' && !inputClienteDoc.value) {
                Swal.fire('Atención', 'Debe seleccionar un cliente de la lista de resultados.', 'warning');
                return;
            }
            if (modoInput.value === 'walkin' && !selectHabitacion.value) {
                Swal.fire('Atención', 'Debe seleccionar una habitación.', 'warning');
                return;
            }
            if (!inputPrecioNoche.value || parseFloat(inputPrecioNoche.value) <= 0) {
                Swal.fire('Atención', 'Debe indicar un precio de noche válido.', 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=estadias&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar el check-in');

                if (estadiaModal) estadiaModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    // Agregar cargo extra
    if (formAgregarCargo) {
        formAgregarCargo.addEventListener('submit', async function (e) {
            e.preventDefault();

            const concepto = document.getElementById('concepto_cargo').value.trim();
            const cantidad = document.getElementById('cantidad_cargo').value;
            const precioUnitario = document.getElementById('precio_unitario_cargo').value;

            if (!concepto) {
                Swal.fire('Atención', 'Ingrese un concepto para el cargo.', 'warning');
                return;
            }
            if (!cantidad || parseInt(cantidad) < 1) {
                Swal.fire('Atención', 'La cantidad debe ser mayor a 0.', 'warning');
                return;
            }
            if (precioUnitario === '' || parseFloat(precioUnitario) < 0) {
                Swal.fire('Atención', 'Ingrese un precio unitario válido.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('estadia_id', estadiaIdCargo.value);
            formData.append('concepto', concepto);
            formData.append('cantidad', cantidad);
            formData.append('precio_unitario', precioUnitario);

            try {
                const res = await fetch('?c=estadias&a=agregarCargo', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'No se pudo agregar el cargo');

                renderCargos(data.data.cargos, data.data.total);
                formAgregarCargo.reset();
                document.getElementById('cantidad_cargo').value = 1;
                tabla.ajax.reload(null, false);
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});