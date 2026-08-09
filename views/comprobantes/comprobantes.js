$(document).ready(function () {
    let vistaActual = 'PENDIENTES';

    const modalComprobanteElement = document.getElementById('modalComprobante');
    const comprobanteModal = modalComprobanteElement ? new bootstrap.Modal(modalComprobanteElement) : null;
    const formComprobante = document.getElementById('formComprobante');

    const lblContratoId = document.getElementById('lblContratoIdComprobante');
    const infoContrato = document.getElementById('infoContratoComprobante');
    const inputContratoId = document.getElementById('cmp_contrato_id');
    const tablaCargosBody = document.getElementById('tablaCargosComprobanteBody');

    const selectTipoComprobante = document.getElementById('tipo_comprobante');
    const selectClienteTipoDoc = document.getElementById('cliente_tipo_doc');
    const inputClienteNumDoc = document.getElementById('cliente_num_doc');
    const inputClienteRazonSocial = document.getElementById('cliente_razon_social');
    const inputClienteDireccion = document.getElementById('cliente_direccion');
    const inputDescuento = document.getElementById('descuento');

    const resumenSubtotalAlquiler = document.getElementById('resumenSubtotalAlquiler');
    const resumenCargosExtra = document.getElementById('resumenCargosExtra');
    const resumenTotalComprobante = document.getElementById('resumenTotalComprobante');
    const detalleDesgloseIgv = document.getElementById('detalleDesgloseIgv');

    function money(n) {
        return 'S/ ' + Number(n || 0).toFixed(2);
    }

    let calculoActual = null;

    function renderCargos(cargos) {
        if (!cargos || !cargos.length) {
            tablaCargosBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center small">Sin cargos extra registrados</td></tr>';
            return;
        }
        tablaCargosBody.innerHTML = cargos.map(c => `
            <tr>
                <td class="small">${c.concepto}</td>
                <td class="text-end small">${c.cantidad}</td>
                <td class="text-end small">${money(c.precio_unitario)}</td>
                <td class="text-end small fw-bold">${money(c.subtotal)}</td>
            </tr>
        `).join('');
    }

    function actualizarResumen() {
        if (!calculoActual) {
            resumenSubtotalAlquiler.textContent = money(0);
            resumenCargosExtra.textContent = money(0);
            resumenTotalComprobante.textContent = money(0);
            detalleDesgloseIgv.textContent = '';
            return;
        }

        const subtotal = calculoActual.calculo_alquiler.monto_total;
        const cargos = calculoActual.total_cargos;
        const descuento = parseFloat(inputDescuento.value) || 0;
        const totalFinal = Math.max(subtotal + cargos - descuento, 0);

        resumenSubtotalAlquiler.textContent = money(subtotal);
        resumenCargosExtra.textContent = money(cargos);
        resumenTotalComprobante.textContent = money(totalFinal);

        const opGravadas = totalFinal / 1.18;
        const igv = totalFinal - opGravadas;
        detalleDesgloseIgv.textContent = `Op. gravadas: ${money(opGravadas)} · IGV (18%): ${money(igv)}`;
    }

    inputDescuento.addEventListener('input', actualizarResumen);

    // Longitud/formato esperado del N° de documento según el tipo 
    const PATRONES_DOC = {
        DNI: { maxlength: 8, regex: /^\d{8}$/, mensaje: 'El DNI debe tener exactamente 8 dígitos numéricos, sin letras ni símbolos.' },
        RUC: { maxlength: 11, regex: /^\d{11}$/, mensaje: 'El RUC debe tener exactamente 11 dígitos numéricos, sin letras ni símbolos.' },
        CE: { maxlength: 12, regex: /^[A-Za-z0-9]{9,12}$/, mensaje: 'El Carné de Extranjería debe tener entre 9 y 12 caracteres alfanuméricos, sin símbolos.' },
        PASAPORTE: { maxlength: 15, regex: /^[A-Za-z0-9]{5,15}$/, mensaje: 'El Pasaporte debe tener entre 5 y 15 caracteres alfanuméricos, sin símbolos.' }
    };

    const msgErrorDocumento = document.getElementById('msgErrorDocumento');

    function actualizarRestriccionDocumento() {
        const patron = PATRONES_DOC[selectClienteTipoDoc.value];
        if (!patron) return;
        inputClienteNumDoc.maxLength = patron.maxlength;
        validarDocumentoEnVivo();
    }

    function validarDocumentoEnVivo() {
        const patron = PATRONES_DOC[selectClienteTipoDoc.value];
        const valor = inputClienteNumDoc.value.trim();

        if (!patron || valor === '') {
            inputClienteNumDoc.style.borderColor = '#cbd5e1';
            msgErrorDocumento.style.display = 'none';
            return true;
        }

        const esValido = patron.regex.test(valor);
        inputClienteNumDoc.style.borderColor = esValido ? '#2ab418' : '#dc3545';
        msgErrorDocumento.textContent = esValido ? '' : patron.mensaje;
        msgErrorDocumento.style.display = esValido ? 'none' : 'block';
        return esValido;
    }

    // Al elegir FACTURA, forzar tipo de documento a RUC 
    selectTipoComprobante.addEventListener('change', function () {
        if (this.value === 'FACTURA') {
            selectClienteTipoDoc.value = 'RUC';
        }
        actualizarRestriccionDocumento();
    });

    selectClienteTipoDoc.addEventListener('change', actualizarRestriccionDocumento);

    /**
     * Abre el modal de emisión para un contrato FINALIZADO pendiente de facturar.
     * Se llama desde el botón "Facturar" de la pestaña Pendientes.
     */
    async function abrirModalComprobante(contratoId) {
        lblContratoId.textContent = contratoId;
        inputContratoId.value = contratoId;
        infoContrato.textContent = 'Cargando datos del contrato...';
        tablaCargosBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center small">Cargando...</td></tr>';

        try {
            const res = await fetch('?c=comprobantes&a=obtenerContexto&contrato_id=' + contratoId);
            const data = await res.json();

            if (!data.success) {
                Swal.fire('Atención', data.message || 'No se pudo cargar el contrato', 'warning');
                return;
            }

            const { contrato, cargos, calculo } = data.data;
            calculoActual = calculo;

            infoContrato.innerHTML = `<strong>${contrato.placa_vehiculo}</strong> — ${contrato.marca} ${contrato.modelo} &nbsp;|&nbsp; Cliente: <strong>${contrato.cliente_nombre}</strong> (${contrato.cliente_tipo_doc}: ${contrato.cliente_doc}) &nbsp;|&nbsp; Salida: ${contrato.fecha_hora_salida} &nbsp;|&nbsp; Devolución: ${contrato.fecha_hora_devolucion_real}`;

            selectTipoComprobante.value = 'BOLETA';
            selectClienteTipoDoc.value = contrato.cliente_tipo_doc || 'DNI';
            inputClienteNumDoc.value = contrato.cliente_doc || '';
            actualizarRestriccionDocumento();
            inputClienteRazonSocial.value = contrato.cliente_nombre || '';
            inputClienteDireccion.value = contrato.cliente_direccion || '';
            inputDescuento.value = 0;
            document.getElementById('observaciones').value = '';
            document.getElementById('metodo_pago').value = 'EFECTIVO';

            renderCargos(cargos);
            actualizarResumen();

            comprobanteModal.show();
        } catch {
            Swal.fire('Error', 'Error al cargar el contrato', 'error');
        }
    }

    if (formComprobante) {
        formComprobante.addEventListener('submit', async function (e) {
            e.preventDefault();

            const patronDoc = PATRONES_DOC[selectClienteTipoDoc.value];
            if (!validarDocumentoEnVivo()) {
                Swal.fire('Atención', PATRONES_DOC[selectClienteTipoDoc.value].mensaje, 'warning');
                return;
            }

            const formData = new FormData(this);

            try {
                const res = await fetch('?c=comprobantes&a=generar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al emitir el comprobante');

                comprobanteModal.hide();
                Swal.fire('¡Comprobante Emitido!', data.message, 'success').then(() => {
                    if (data.data && data.data.id) {
                        window.open('?c=comprobantes&a=pdf&id=' + data.data.id, '_blank');
                    }
                    tablaPendientes.ajax.reload(null, false);
                    tablaEmitidos.ajax.reload(null, false);
                });
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    // ---------------------------------------------------------
    // DataTable: Pendientes de Facturar
    // ---------------------------------------------------------
    const tablaPendientes = $('#datatable_pendientes').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=comprobantes&a=listarPendientes',
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: d => `<span class="fw-bold" style="color:#1e293b;">${d.placa_vehiculo}</span><br><span class="small text-muted">${d.marca} ${d.modelo} (${d.anio})</span>`
            },
            {
                data: null,
                render: d => `<span class="fw-semibold">${d.cliente_nombre}</span><br><span class="small text-muted">${d.cliente_tipo_doc}: ${d.cliente_doc}</span>`
            },
            { data: 'fecha_hora_salida' },
            { data: 'fecha_hora_devolucion_real' },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: d => `
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button type="button" class="btn text-white btn-facturar-contrato rounded-circle" data-id="${d.id}" style="width:36px;height:36px;padding:0;background-color:#6d28d9;border-color:#6d28d9;" title="Facturar">
                            <i class="fas fa-file-invoice-dollar fs-6"></i>
                        </button>
                    </div>`
            }
        ],
        language: {
            paginate: {
                next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
            },
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron contratos pendientes de facturar",
            emptyTable: "No hay contratos pendientes de facturar",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // ---------------------------------------------------------
    // DataTable: Comprobantes Emitidos
    // ---------------------------------------------------------
    function badgeEstadoComprobante(estado) {
        return estado === 'EMITIDO'
            ? '<span class="badge bg-success">EMITIDO</span>'
            : '<span class="badge bg-danger">ANULADO</span>';
    }

    const tablaEmitidos = $('#datatable_emitidos').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=comprobantes&a=listarEmitidos',
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: d => `<span class="fw-bold" style="color:#6d28d9;">${d.tipo_comprobante}</span><br><span class="small text-muted">${d.serie}-${d.numero}</span>`
            },
            { data: 'fecha_emision' },
            {
                data: null,
                render: d => `<span class="fw-semibold">${d.cliente_razon_social}</span><br><span class="small text-muted">${d.cliente_num_doc}</span>`
            },
            {
                data: 'monto_total',
                render: d => money(d)
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
                render: d => `
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <a class="btn btn-info text-white rounded-circle" href="?c=comprobantes&a=pdf&id=${d.id}" target="_blank" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;background-color:#0b667c;border-color:#0b667c;" title="Ver PDF">
                            <i class="fas fa-file-pdf fs-6"></i>
                        </a>
                    </div>`
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
            emptyTable: "Aún no se han emitido comprobantes",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    // ---------------------------------------------------------
    // Pestañas
    // ---------------------------------------------------------
    document.querySelectorAll('#pills-tab-comprobantes .tab-vista').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-comprobantes .tab-vista').forEach(b => {
                b.classList.remove('active', 'text-white');
                b.classList.add('text-dark');
                b.style.backgroundColor = '';
                b.style.borderColor = '';
            });
            this.classList.add('active', 'text-white');
            this.classList.remove('text-dark');
            this.style.backgroundColor = '#6d28d9';
            this.style.borderColor = '#6d28d9';

            vistaActual = this.getAttribute('data-vista');

            document.getElementById('wrapPendientes').style.display = vistaActual === 'PENDIENTES' ? '' : 'none';
            document.getElementById('wrapEmitidos').style.display = vistaActual === 'EMITIDOS' ? '' : 'none';

            if (vistaActual === 'PENDIENTES') {
                tablaPendientes.ajax.reload(null, false);
            } else {
                tablaEmitidos.ajax.reload(null, false);
            }
        });
    });

    // ---------------------------------------------------------
    // Acciones de fila
    // ---------------------------------------------------------
    document.addEventListener('click', function (e) {
        const btnFacturar = e.target.closest('.btn-facturar-contrato');
        if (btnFacturar) {
            abrirModalComprobante(btnFacturar.getAttribute('data-id'));
        }
    });
});