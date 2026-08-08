$(document).ready(function () {
    let estadoFiltro = 'EMITIDO';

    const modalFacturarElement = document.getElementById('modalFacturar');
    const facturarModal = modalFacturarElement ? new bootstrap.Modal(modalFacturarElement) : null;
    const formFacturar = document.getElementById('formFacturar');

    const modalVerElement = document.getElementById('modalVerComprobante');
    const verModal = modalVerElement ? new bootstrap.Modal(modalVerElement) : null;
    const iframeComprobante = document.getElementById('iframeComprobante');
    const linkDescargar = document.getElementById('linkDescargarComprobante');
    const btnAnularComprobante = document.getElementById('btnAnularComprobante');
    const lblTituloComprobante = document.getElementById('lblTituloComprobante');

    const inputBuscarReferencia = document.getElementById('buscar_referencia');
    const inputRefId = document.getElementById('ref_id');
    const resultadosReferenciaBox = document.getElementById('resultadosReferenciaBox');
    const resumenReferencia = document.getElementById('resumenReferencia');
    const resCliente = document.getElementById('resCliente');
    const resHabitacion = document.getElementById('resHabitacion');
    const resFechas = document.getElementById('resFechas');
    const resMonto = document.getElementById('resMonto');
    const inputDescuento = document.getElementById('descuento');
    const hintDescuento = document.getElementById('hintDescuento');

    function origenSeleccionado() {
        const radio = document.querySelector('input[name="origen"]:checked');
        return radio ? radio.value : 'estadia';
    }

    function actualizarDescuentoSegunOrigen() {
        if (origenSeleccionado() === 'reserva') {
            inputDescuento.value = '0.00';
            inputDescuento.disabled = true;
            hintDescuento.innerText = 'No aplica a comprobantes de adelanto.';
        } else {
            inputDescuento.disabled = false;
            hintDescuento.innerText = '';
        }
    }

    function badgeEstadoComprobante(estado) {
        return estado === 'EMITIDO'
            ? '<span class="badge bg-success">EMITIDO</span>'
            : '<span class="badge bg-danger">ANULADO</span>';
    }

    function abrirVisorComprobante(id, titulo, estado) {
        lblTituloComprobante.innerText = titulo;
        iframeComprobante.src = `?c=facturacion&a=descargarPdf&id=${id}&modo=ver`;
        linkDescargar.href = `?c=facturacion&a=descargarPdf&id=${id}&modo=descargar`;

        if (estado === 'EMITIDO') {
            btnAnularComprobante.classList.remove('d-none');
            btnAnularComprobante.setAttribute('data-id', id);
        } else {
            btnAnularComprobante.classList.add('d-none');
        }

        if (verModal) verModal.show();
    }

    const tabla = $('#datatable_facturacion').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=facturacion&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: d => `<span class="fw-bold">${d.tipo_comprobante}</span><br><small class="text-muted">${d.serie}-${d.numero}</small>`
            },
            {
                data: null,
                render: d => `${d.cliente_razon_social}<br><small class="text-muted">${d.cliente_tipo_doc}: ${d.cliente_num_doc}</small>`
            },
            {
                data: null,
                render: d => {
                    const origen = d.tipo_origen === 'ESTADIA' ? 'Estadía' : 'Adelanto Reserva';
                    return `${origen}${d.numero_habitacion ? ' — Hab. ' + d.numero_habitacion : ''}`;
                }
            },
            { data: 'fecha_emision' },
            {
                data: 'monto_total',
                render: d => `S/ ${parseFloat(d).toFixed(2)}`
            },
            { data: 'metodo_pago' },
            {
                data: 'estado_comprobante',
                render: d => badgeEstadoComprobante(d)
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    return `
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="btn btn-info text-white btn-ver-comprobante rounded-circle"
                                data-id="${row.id}" data-titulo="${row.tipo_comprobante} ${row.serie}-${row.numero}" data-estado="${row.estado_comprobante}"
                                style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="Ver / Descargar">
                                <i class="fas fa-eye fs-6"></i>
                            </button>
                        </div>
                    `;
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
            zeroRecords: "No se encontraron comprobantes",
            emptyTable: "No hay comprobantes en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // Pestañas Emitidos / Anulados
    document.querySelectorAll('#pills-tab-facturacion .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-facturacion .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // Cambiar origen reinicia la búsqueda / selección
        document.querySelectorAll('input[name="origen"]').forEach(radio => {
            radio.addEventListener('change', function () {
                inputRefId.value = '';
                inputBuscarReferencia.value = '';
                resumenReferencia.classList.add('d-none');
                resultadosReferenciaBox.style.display = 'none';
                actualizarDescuentoSegunOrigen();
            });
        });

        // Buscador de referencia (estadía finalizada o reserva con adelanto)
        let debounceTimer = null;
        if (inputBuscarReferencia) {
            inputBuscarReferencia.addEventListener('input', function () {
                inputRefId.value = '';
                resumenReferencia.classList.add('d-none');
                clearTimeout(debounceTimer);
                const termino = this.value.trim();
                if (termino.length < 2) {
                    resultadosReferenciaBox.style.display = 'none';
                    return;
                }
                debounceTimer = setTimeout(() => {
                    const origen = origenSeleccionado();
                    fetch(`?c=facturacion&a=buscarAJAX&origen=${origen}&q=${encodeURIComponent(termino)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success || !data.data || !data.data.length) {
                                resultadosReferenciaBox.innerHTML = '<div class="list-group-item text-muted small">Sin resultados</div>';
                                resultadosReferenciaBox.style.display = 'block';
                                return;
                            }
                            resultadosReferenciaBox.innerHTML = data.data.map(item => {
                                if (origen === 'estadia') {
                                    const checkin = item.fecha_hora_checkin ? item.fecha_hora_checkin.substring(0, 10) : '';
                                    const checkout = item.fecha_hora_checkout ? item.fecha_hora_checkout.substring(0, 10) : '';
                                    return `<button type="button" class="list-group-item list-group-item-action btn-opcion-referencia"
                                        data-id="${item.id}" data-cliente="${item.cliente_nombre}" data-habitacion="${item.numero_habitacion}"
                                        data-fechas="${checkin} → ${checkout}" data-monto="Según noches x S/ ${parseFloat(item.precio_noche_pactado).toFixed(2)}">
                                        <strong>${item.cliente_nombre}</strong> — Hab. ${item.numero_habitacion}<br>
                                        <small class="text-muted">${item.cliente_tipo_doc}: ${item.cliente_doc} · ${checkin} → ${checkout}</small>
                                    </button>`;
                                } else {
                                    return `<button type="button" class="list-group-item list-group-item-action btn-opcion-referencia"
                                        data-id="${item.id}" data-cliente="${item.cliente_nombre}" data-habitacion="${item.numero_habitacion}"
                                        data-fechas="${item.fecha_checkin_prevista} → ${item.fecha_checkout_prevista}" data-monto="S/ ${parseFloat(item.monto_adelanto).toFixed(2)} (adelanto)">
                                        <strong>${item.cliente_nombre}</strong> — Hab. ${item.numero_habitacion}<br>
                                        <small class="text-muted">${item.cliente_tipo_doc}: ${item.cliente_doc} · Adelanto S/ ${parseFloat(item.monto_adelanto).toFixed(2)}</small>
                                    </button>`;
                                }
                            }).join('');
                            resultadosReferenciaBox.style.display = 'block';
                        })
                        .catch(() => { resultadosReferenciaBox.style.display = 'none'; });
                }, 300);
            });
        }

        document.addEventListener('click', function (e) {
            // Selección de estadía/reserva desde el buscador
            const btnOpcion = e.target.closest('.btn-opcion-referencia');
            if (btnOpcion) {
                inputRefId.value = btnOpcion.getAttribute('data-id');
                inputBuscarReferencia.value = btnOpcion.getAttribute('data-cliente') + ' — Hab. ' + btnOpcion.getAttribute('data-habitacion');

                resCliente.innerText = btnOpcion.getAttribute('data-cliente');
                resHabitacion.innerText = btnOpcion.getAttribute('data-habitacion');
                resFechas.innerText = btnOpcion.getAttribute('data-fechas');
                resMonto.innerText = btnOpcion.getAttribute('data-monto');
                resumenReferencia.classList.remove('d-none');

                resultadosReferenciaBox.style.display = 'none';
            } else if (resultadosReferenciaBox && !e.target.closest('#resultadosReferenciaBox') && !e.target.closest('#buscar_referencia')) {
                resultadosReferenciaBox.style.display = 'none';
            }

        // Ver / Descargar comprobante desde la tabla
        const btnVer = e.target.closest('.btn-ver-comprobante');
        if (btnVer) {
            abrirVisorComprobante(
                btnVer.getAttribute('data-id'),
                btnVer.getAttribute('data-titulo'),
                btnVer.getAttribute('data-estado')
            );
        }

        // Anular comprobante (desde el modal de visualización)
        if (e.target.closest('#btnAnularComprobante')) {
            const id = btnAnularComprobante.getAttribute('data-id');

            Swal.fire({
                title: '¿Anular Comprobante?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Anular',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=facturacion&a=anular', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                if (verModal) verModal.hide();
                                tabla.ajax.reload(null, false);
                                Swal.fire('Anulado', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al anular', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }
    });

    // Abrir modal de nuevo comprobante
    const btnNuevo = document.querySelector('.btn-nuevo-comprobante');
    if (btnNuevo && facturarModal) {
        btnNuevo.addEventListener('click', function () {
            if (formFacturar) formFacturar.reset();
            inputRefId.value = '';
            resumenReferencia.classList.add('d-none');
            document.getElementById('origen_estadia').checked = true;
            actualizarDescuentoSegunOrigen();
            facturarModal.show();
        });
    }

    // Envío del formulario (generar comprobante)
    if (formFacturar) {
        formFacturar.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!inputRefId.value) {
                Swal.fire('Atención', 'Debe seleccionar una estadía o reserva de la lista de resultados.', 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=facturacion&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al generar el comprobante');

                if (facturarModal) facturarModal.hide();
                tabla.ajax.reload(null, false);

                Swal.fire('¡Comprobante generado!', data.message, 'success').then(() => {
                    if (data.data && data.data.id) {
                        abrirVisorComprobante(data.data.id, 'Comprobante generado', 'EMITIDO');
                    }
                });
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});