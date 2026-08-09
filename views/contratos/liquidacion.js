$(document).ready(function () {
    const modalLiquidacionElement = document.getElementById('modalLiquidacion');
    const liquidacionModal = modalLiquidacionElement ? new bootstrap.Modal(modalLiquidacionElement) : null;

    const lblContratoId = document.getElementById('lblContratoIdLiquidacion');
    const infoContrato = document.getElementById('infoContratoLiquidacion');
    const avisoFirmada = document.getElementById('avisoLiquidacionFirmada');

    const inputContratoId = document.getElementById('liq_contrato_id');
    const inputFechaReal = document.getElementById('fecha_hora_devolucion_real');
    const inputKmLlegada = document.getElementById('kilometraje_llegada');
    const wrapResponsable = document.getElementById('wrapResponsableRetraso');
    const selectResponsable = document.getElementById('responsable_retraso');
    const inputObservaciones = document.getElementById('observaciones_llegada');

    const formCargo = document.getElementById('formCargo');
    const cargoConcepto = document.getElementById('cargo_concepto');
    const cargoCantidad = document.getElementById('cargo_cantidad');
    const cargoPrecio = document.getElementById('cargo_precio');
    const tablaCargosBody = document.getElementById('tablaCargosBody');

    const resumenMontoAlquiler = document.getElementById('resumenMontoAlquiler');
    const resumenTotalCargos = document.getElementById('resumenTotalCargos');
    const resumenTotalFinal = document.getElementById('resumenTotalFinal');
    const detalleCalculo = document.getElementById('detalleCalculoLiquidacion');

    const btnGuardar = document.getElementById('btnGuardarLiquidacion');
    const btnFirmar = document.getElementById('btnFirmarLiquidacion');
    const btnPdf = document.getElementById('btnPdfLiquidacion');
    const contenedorFirmar = document.getElementById('contenedorFirmarLiquidacion');

    if (btnPdf && btnFirmar) {
    btnPdf.addEventListener('click', function () {
        btnFirmar.disabled = false;
    });

    if (contenedorFirmar) {
    contenedorFirmar.addEventListener('click', function () {
        if (btnFirmar.disabled) {
            Swal.fire({
                icon: 'info',
                title: 'Revise el PDF primero',
                text: 'Debe previsualizar el PDF de la liquidación antes de poder firmarla.',
                confirmButtonText: 'Entendido'
            });
        }
    });
}
}

    let contratoActual = null;
    let liquidacionFirmadaActual = false;

    function money(n) {
        return 'S/ ' + Number(n || 0).toFixed(2);
    }

    function fechaInputValue(fechaMysql) {
        if (!fechaMysql) return '';
        return fechaMysql.replace(' ', 'T').slice(0, 16);
    }

    function bloquearFormulario(bloquear) {
        [inputFechaReal, inputKmLlegada, selectResponsable, inputObservaciones, cargoConcepto, cargoCantidad, cargoPrecio].forEach(el => {
            if (el) el.disabled = bloquear;
        });
        if (formCargo) formCargo.querySelector('button[type="submit"]').disabled = bloquear;
        btnGuardar.style.display = bloquear ? 'none' : 'inline-block';
        btnFirmar.style.display = bloquear ? 'none' : 'inline-block';
        avisoFirmada.style.display = bloquear ? 'block' : 'none';
        document.querySelectorAll('.btn-eliminar-cargo').forEach(b => b.style.display = bloquear ? 'none' : 'inline-block');
    }

    function renderCargos(cargos) {
        if (!cargos || !cargos.length) {
            tablaCargosBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center small">Sin cargos registrados</td></tr>';
            return;
        }
        tablaCargosBody.innerHTML = cargos.map(c => `
            <tr>
                <td class="small">${c.concepto}</td>
                <td class="text-end small">${c.cantidad}</td>
                <td class="text-end small">${money(c.precio_unitario)}</td>
                <td class="text-end small fw-bold">${money(c.subtotal)}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-eliminar-cargo" data-id="${c.id}" style="border-radius:6px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function actualizarResumen(calculo) {
        if (!calculo) {
            resumenMontoAlquiler.textContent = money(0);
            resumenTotalCargos.textContent = money(0);
            resumenTotalFinal.textContent = money(0);
            detalleCalculo.textContent = '';
            return;
        }
        const ca = calculo.calculo_alquiler;
        resumenMontoAlquiler.textContent = money(ca.monto_total);
        resumenTotalCargos.textContent = money(calculo.total_cargos);
        resumenTotalFinal.textContent = money(calculo.total_final);

        let detalle = `${ca.dias_completos} día(s) completo(s)`;
        if (ca.horas_restantes > 0) {
            detalle += ` + ${ca.horas_restantes}h prorrateadas (${(ca.fraccion_dia * 100).toFixed(1)}% de un día extra)`;
        }
        if (calculo.hubo_retraso) {
            detalle += selectResponsable.value === 'EMPRESA'
                ? ' — Retraso NO cobrado al cliente (responsabilidad de la empresa).'
                : ' — Retraso cobrado al cliente.';
        }
        detalleCalculo.textContent = detalle;
    }

    async function recalcularEnVivo() {
        if (!contratoActual || !inputFechaReal.value) return;

        const huboRetraso = new Date(inputFechaReal.value) >
            new Date(fechaInputValue(contratoActual.fecha_hora_devolucion_estimada));

        wrapResponsable.style.display = huboRetraso ? 'block' : 'none';

        if (!huboRetraso) {
            selectResponsable.value = 'NINGUNO';
        }

        const params = new URLSearchParams({
            contrato_id: contratoActual.id,
            fecha_hora_devolucion_real: inputFechaReal.value,
            responsable_retraso: selectResponsable.value
        });

        try {
            const res = await fetch(
                '?c=liquidaciones&a=calcularEnVivo&' + params.toString()
            );

            const data = await res.json();

            if (data.success) {
                actualizarResumen(data.data);
            }
        } catch {
            // Silencioso: es solo preview
        }
    }

    [inputFechaReal, selectResponsable].forEach(el => {
        if (el) el.addEventListener('change', recalcularEnVivo);
    });

    /**
     * Abre el modal de Liquidación para un contrato firmado. Se llama desde contratos.js
     * al hacer clic en el botón "Liquidar contrato" (ícono de hoja/documento).
     */
    window.abrirModalLiquidacion = async function (contratoId) {
        lblContratoId.textContent = contratoId;
        inputContratoId.value = contratoId;
        tablaCargosBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center small">Cargando...</td></tr>';
        infoContrato.textContent = 'Cargando datos del contrato...';

        try {
            const res = await fetch('?c=liquidaciones&a=obtenerContexto&contrato_id=' + contratoId);
            const data = await res.json();

            if (!data.success) {
                Swal.fire('Atención', data.message || 'No se pudo cargar la liquidación', 'warning');
                return;
            }

            const { contrato, liquidacion, cargos, calculo } = data.data;
            contratoActual = contrato;
            liquidacionFirmadaActual = !!(liquidacion && liquidacion.firmado == 1);

            infoContrato.innerHTML = `<strong>${contrato.placa_vehiculo}</strong> — ${contrato.marca} ${contrato.modelo} &nbsp;|&nbsp; Cliente: <strong>${contrato.cliente_nombre}</strong> (${contrato.cliente_tipo_doc}: ${contrato.cliente_doc}) &nbsp;|&nbsp; Salida: ${contrato.fecha_hora_salida} &nbsp;|&nbsp; Devolución estimada: ${contrato.fecha_hora_devolucion_estimada}`;

            if (liquidacion) {
                inputFechaReal.value = fechaInputValue(liquidacion.fecha_hora_devolucion_real);
                inputKmLlegada.value = liquidacion.kilometraje_llegada;
                selectResponsable.value = liquidacion.responsable_retraso;
                inputObservaciones.value = liquidacion.observaciones_llegada || '';
                wrapResponsable.style.display = liquidacion.responsable_retraso !== 'NINGUNO' ? 'block' : 'none';
            } else {
                inputFechaReal.value = '';
                inputKmLlegada.value = '';
                selectResponsable.value = 'NINGUNO';
                inputObservaciones.value = '';
                wrapResponsable.style.display = 'none';
            }

            renderCargos(cargos);
            actualizarResumen(calculo);

            btnPdf.href = '?c=liquidaciones&a=pdf&contrato_id=' + contratoId;
            btnPdf.style.display = liquidacion ? 'inline-block' : 'none';

            bloquearFormulario(liquidacionFirmadaActual);

            liquidacionModal.show();
        } catch {
            Swal.fire('Error', 'Error al cargar la liquidación', 'error');
        }
    };

    // ---------------------------------------------------------
    // Guardar borrador de liquidación
    // ---------------------------------------------------------
    if (btnGuardar) {
        btnGuardar.addEventListener('click', async function () {
            if (!inputFechaReal.value || !inputKmLlegada.value) {
                Swal.fire('Atención', 'Complete la fecha de devolución real y el kilometraje de llegada.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('contrato_id', inputContratoId.value);
            formData.append('fecha_hora_devolucion_real', inputFechaReal.value);
            formData.append('kilometraje_llegada', inputKmLlegada.value);
            formData.append('responsable_retraso', selectResponsable.value);
            formData.append('observaciones_llegada', inputObservaciones.value);

            try {
                const res = await fetch('?c=liquidaciones&a=guardarLiquidacion', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al guardar la liquidación');

                actualizarResumen(data.data);
                btnPdf.href = '?c=liquidaciones&a=pdf&contrato_id=' + inputContratoId.value;
                btnPdf.style.display = 'inline-block';
                Swal.fire('Guardado', data.message, 'success');
                liquidacionModal.hide();
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    // ---------------------------------------------------------
    // Firmar liquidación
    // ---------------------------------------------------------
    if (btnFirmar) {
        btnFirmar.addEventListener('click', function () {
            Swal.fire({
                title: '¿Firmar liquidación?',
                text: 'Esto bloqueará la liquidación de forma permanente, liberará el vehículo (DISPONIBLE) y finalizará el contrato.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2ab418',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Firmar',
                cancelButtonText: 'Volver'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('contrato_id', inputContratoId.value);

                fetch('?c=liquidaciones&a=firmar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            bloquearFormulario(true);
                            Swal.fire('Firmado', data.message, 'success').then(() => {
                                liquidacionModal.hide();
                                if (window.recargarTablaContratos) window.recargarTablaContratos();
                            });
                        } else {
                            Swal.fire('Atención', data.message || 'No se pudo firmar. Guarde primero la liquidación.', 'warning');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
            });
        });
    }

    // ---------------------------------------------------------
    // Cargos de alquiler
    // ---------------------------------------------------------
    async function actualizarCargosYResumen(contratoId) {
        const res = await fetch(
            '?c=liquidaciones&a=obtenerContexto&contrato_id=' + contratoId
        );

        const data = await res.json();

        if (!data.success) {
            throw new Error(
                data.message || 'No se pudieron actualizar los cargos'
            );
        }

        renderCargos(data.data.cargos);

        await recalcularEnVivo();
    }


    if (formCargo) {
        formCargo.addEventListener('submit', async function (e) {
            e.preventDefault();

            const concepto = cargoConcepto.value.trim();
            const cantidad = parseInt(cargoCantidad.value, 10);
            const precio = parseFloat(cargoPrecio.value);

            if (!concepto || !cantidad || cantidad <= 0 || !precio || precio <= 0) {
                Swal.fire('Atención', 'Complete concepto, cantidad y precio unitario del cargo.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('contrato_id', inputContratoId.value);
            formData.append('concepto', concepto);
            formData.append('cantidad', cantidad);
            formData.append('precio_unitario', precio);

            try {
                const res = await fetch('?c=liquidaciones&a=guardarCargo', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al agregar el cargo');

                formCargo.reset();
                cargoCantidad.value = 1;

                await actualizarCargosYResumen(inputContratoId.value);
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    tablaCargosBody.addEventListener('click', async function (e) {
        const btn = e.target.closest('.btn-eliminar-cargo');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        const result = await Swal.fire({
            title: '¿Eliminar cargo?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'Cancelar'
        });
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('cargo_id', id);

        try {
            const res = await fetch('?c=liquidaciones&a=eliminarCargo', { method: 'POST', body: formData });
            const data = await res.json();
            if (!data.success) throw new Error(data.message || 'Error al eliminar el cargo');
            await actualizarCargosYResumen(inputContratoId.value);
        } catch (err) {
            Swal.fire('Error', err.message || 'Error en el servidor', 'error');
        }
    });
});