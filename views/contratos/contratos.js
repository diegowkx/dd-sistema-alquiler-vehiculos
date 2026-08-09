$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';
    let debounceBusquedaCliente = null;

    const modalContratoElement = document.getElementById('modalContrato');
    const contratoModal = modalContratoElement ? new bootstrap.Modal(modalContratoElement) : null;
    const formContrato = document.getElementById('formContrato');
    const lblTituloContrato = document.getElementById('lblTituloContrato');

    const buscadorCliente = document.getElementById('buscadorCliente');
    const listaClientesFlotante = document.getElementById('listaClientesFlotante');
    const inputClienteDoc = document.getElementById('cliente_doc');

    const selectVehiculo = document.getElementById('placa_vehiculo');
    const inputFechaSalida = document.getElementById('fecha_hora_salida');
    const inputFechaDevolucion = document.getElementById('fecha_hora_devolucion_estimada');
    const inputTarifa = document.getElementById('tarifa_dia_pactada');
    const resumenCosto = document.getElementById('resumenCosto');
    const detalleCosto = document.getElementById('detalleCosto');

    function money(n) {
        return 'S/ ' + Number(n || 0).toFixed(2);
    }

    // ---------------------------------------------------------
    // Autocompletado de Cliente (reutiliza ?c=clientes&a=buscarAJAX)
    // ---------------------------------------------------------
    if (buscadorCliente) {
        buscadorCliente.addEventListener('input', function () {
            const termino = this.value.trim();
            inputClienteDoc.value = '';
            clearTimeout(debounceBusquedaCliente);

            if (termino.length < 2) {
                listaClientesFlotante.style.display = 'none';
                return;
            }

            debounceBusquedaCliente = setTimeout(async () => {
                try {
                    const res = await fetch('?c=clientes&a=buscarAJAX&q=' + encodeURIComponent(termino));
                    const data = await res.json();
                    if (!data.success || !data.data.length) {
                        listaClientesFlotante.innerHTML = '<div class="px-3 py-2 text-muted small">Sin resultados</div>';
                        listaClientesFlotante.style.display = 'block';
                        return;
                    }
                    listaClientesFlotante.innerHTML = data.data.map(c => `
                        <div class="cliente-item px-3 py-2 border-bottom" style="cursor:pointer;" data-doc="${c.numero_documento}" data-nombre="${c.razon_social}">
                            <strong>${c.tipo_documento}: ${c.numero_documento}</strong><br>
                            <span class="small text-muted">${c.razon_social}</span>
                        </div>
                    `).join('');
                    listaClientesFlotante.style.display = 'block';
                } catch {
                    listaClientesFlotante.style.display = 'none';
                }
            }, 300);
        });

        listaClientesFlotante.addEventListener('click', function (e) {
            const item = e.target.closest('.cliente-item');
            if (!item) return;
            inputClienteDoc.value = item.getAttribute('data-doc');
            buscadorCliente.value = item.getAttribute('data-nombre') + ' (' + item.getAttribute('data-doc') + ')';
            listaClientesFlotante.style.display = 'none';
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#buscadorCliente') && !e.target.closest('#listaClientesFlotante')) {
                listaClientesFlotante.style.display = 'none';
            }
        });
    }

    // Actualiza la vista y los campos de tarifa/kilometraje al cambiar de vehículo
    function actualizarDatosVehiculo() {
        const opcion = selectVehiculo.options[selectVehiculo.selectedIndex];
        const info = document.getElementById('infoVehiculoSeleccionado');

        if (!opcion || !opcion.value) {
            info.innerHTML = `
                <span class="text-muted">
                    Seleccione un vehículo para ver su información.
                </span>
            `;
            return;
        }

        const tipo = opcion.dataset.tipo || '';
        const marca = opcion.dataset.marca || '';
        const modelo = opcion.dataset.modelo || '';
        const anio = opcion.dataset.anio || '';
        const color = opcion.dataset.color || '';
        const kilometraje = opcion.dataset.kilometraje || '';
        const tarifa = opcion.dataset.tarifa || '';
        const observacionesVehiculo = opcion.dataset.observaciones || '';

        info.innerHTML = `
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <strong>${marca} ${modelo}</strong>
                <span class="text-muted">|</span>
                <span>${anio}</span>
                <span class="text-muted">|</span>
                <span>${color || 'Sin color registrado'}</span>
            </div>

            <div class="small text-muted mt-1">
                ${tipo ? `Tipo: ${tipo} · ` : ''}
                Kilometraje: ${kilometraje || 'No registrado'} km
                ${tarifa ? ` · Tarifa base: S/ ${parseFloat(tarifa).toFixed(2)}/día` : ''}
            </div>
        `;

        // Solo establecer valores automáticamente al crear.
        // En edición se conserva lo que ya tenía el contrato.
        if (
            document.getElementById('es_edicion').value === '0' ||
            !document.getElementById('es_edicion').value
        ) {
            if (tarifa) {
                document.getElementById('tarifa_dia_pactada').value = tarifa;
            }

            if (kilometraje) {
                document.getElementById('kilometraje_salida').value = kilometraje;
            }

            // Copiar las observaciones actuales del vehículo
            // al campo del contrato, sin modificar el vehículo.
            document.getElementById('observaciones_salida').value =
                observacionesVehiculo;
        }

        if (typeof actualizarResumenCosto === 'function') {
            actualizarResumenCosto();
        }
    }
    // ---------------------------------------------------------
    // Vehículos disponibles
    // NOTA: se asume el endpoint ?c=vehiculos&a=getDisponiblesAJAX
    // devolviendo {success, data:[{placa, marca, modelo, anio, precio_base_dia}]}.
    // Ajustar el nombre de la acción si en el módulo de Vehículos ya existente se llama distinto.
    // ---------------------------------------------------------
    async function cargarVehiculosDisponibles(placaSeleccionadaActual = null) {
        selectVehiculo.innerHTML = '<option value="">Cargando vehículos...</option>';

        try {
            const res = await fetch('?c=vehiculos&a=getDisponiblesAJAX');
            const data = await res.json();

            let opciones = '<option value="">Seleccione un vehículo disponible...</option>';

            const lista = (data.success && Array.isArray(data.data)) ? data.data : [];

            lista.forEach(v => {
                opciones += `
                    <option 
                        value="${v.placa}"
                        data-tipo="${v.tipo_nombre ?? ''}"
                        data-marca="${v.marca ?? ''}"
                        data-modelo="${v.modelo ?? ''}"
                        data-anio="${v.anio ?? ''}"
                        data-color="${v.color ?? ''}"
                        data-kilometraje="${v.kilometraje ?? ''}"
                        data-observaciones="${v.observaciones ?? ''}"
                        data-tarifa="${v.precio_base_dia ?? ''}"
                    >
                        ${v.placa} — ${v.marca} ${v.modelo} (${v.anio})
                    </option>
                `;
            });

            /*
            * Si estamos editando un contrato y el vehículo ya no está
            * disponible, debe seguir apareciendo como vehículo actual.
            *
            * IMPORTANTE:
            * Para que este caso tenga TODOS los datos, el backend proporciona los datos del vehículo actual.
            * Mientras tanto, esta opción solo conserva la placa.
            */
            if (
                placaSeleccionadaActual &&
                !lista.some(v => v.placa === placaSeleccionadaActual)
            ) {
                opciones += `
                    <option 
                        value="${placaSeleccionadaActual}"
                        data-vehiculo-actual="1"
                    >
                        ${placaSeleccionadaActual} (vehículo actual del contrato)
                    </option>
                `;
            }

            selectVehiculo.innerHTML = opciones;

            if (placaSeleccionadaActual) {
                selectVehiculo.value = placaSeleccionadaActual;
            }

            // Actualizar información del vehículo seleccionado
            actualizarDatosVehiculo();

        } catch (error) {
            console.error('Error al cargar vehículos disponibles:', error);
            selectVehiculo.innerHTML = '<option value="">Error al cargar vehículos</option>';
        }
    }

    if (selectVehiculo) {
        selectVehiculo.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const tarifa = opt ? opt.getAttribute('data-tarifa') : null;
            if (tarifa) {
                inputTarifa.value = parseFloat(tarifa).toFixed(2);
                actualizarDatosVehiculo();
                calcularCostoEnVivo();
            }
        });
    }

    // ---------------------------------------------------------
    // Cálculo de costo en vivo (?c=contratos&a=calcularCosto)
    // ---------------------------------------------------------
    async function calcularCostoEnVivo() {
        const salida = inputFechaSalida.value;
        const devolucion = inputFechaDevolucion.value;
        const tarifa = parseFloat(inputTarifa.value);

        if (!salida || !devolucion || !tarifa || tarifa <= 0) {
            resumenCosto.textContent = money(0);
            detalleCosto.textContent = 'Complete fechas y tarifa para ver el cálculo (días completos + fracción proporcional de horas extra).';
            return;
        }

        const params = new URLSearchParams({
            fecha_hora_salida: salida,
            fecha_hora_devolucion: devolucion,
            tarifa_dia: tarifa
        });

        try {
            const res = await fetch('?c=contratos&a=calcularCosto&' + params.toString());
            const data = await res.json();

            if (!data.success) {
                resumenCosto.textContent = money(0);
                detalleCosto.textContent = data.message || 'No se pudo calcular el costo.';
                return;
            }

            const c = data.data;
            resumenCosto.textContent = money(c.monto_total);

            if (c.horas_restantes > 0) {
                detalleCosto.textContent = `${c.dias_completos} día(s) completo(s) + ${c.horas_restantes}h adicionales (prorrateadas como ${(c.fraccion_dia * 100).toFixed(1)}% de un día extra).`;
            } else {
                detalleCosto.textContent = `${c.dias_completos} día(s) completo(s), sin horas adicionales.`;
            }
        } catch {
            detalleCosto.textContent = 'Error al calcular el costo.';
        }
    }

    [inputFechaSalida, inputFechaDevolucion, inputTarifa].forEach(el => {
        if (el) el.addEventListener('input', calcularCostoEnVivo);
    });

    // ---------------------------------------------------------
    // DataTable
    // ---------------------------------------------------------
    function badgeFirmado(firmado) {
        return firmado == 1
            ? '<span class="badge bg-success">FIRMADO</span>'
            : '<span class="badge bg-secondary">PENDIENTE</span>';
    }

    const tabla = $('#datatable_contratos').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=contratos&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
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
            { data: 'fecha_hora_devolucion_estimada' },
            {
                data: 'tarifa_dia_pactada',
                render: d => money(d)
            },
            {
                data: null,
                render: d => {
                    // Estimado con la tarifa pactada usando la misma lógica del backend (fallback simple en el cliente)
                    return `<span class="text-muted small">ver detalle</span>`;
                }
            },
            {
                data: 'firmado',
                render: d => badgeFirmado(d)
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function (row) {
                    const jsonRow = encodeURIComponent(JSON.stringify(row));

                    if (row.estado === 'ACTIVO' && row.firmado == 0) {
                        return `
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <button type="button" class="btn btn-warning text-white btn-editar-contrato rounded-circle" data-contrato="${jsonRow}" style="width:36px;height:36px;padding:0;background-color:#FFA41D;border-color:#FFA41D;" title="Editar">
                                    <i class="fas fa-pencil-alt fs-6"></i>
                                </button>
                                <a class="btn btn-info text-white rounded-circle" href="?c=contratos&a=pdf&id=${row.id}" target="_blank" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;background-color:#0b667c;border-color:#0b667c;" title="Ver PDF">
                                    <i class="fas fa-file-pdf fs-6"></i>
                                </a>
                                <button type="button" class="btn btn-success text-white btn-firmar-contrato rounded-circle" data-id="${row.id}" style="width:36px;height:36px;padding:0;background-color:#2ab418;border-color:#2ab418;" title="Firmar">
                                    <i class="fas fa-signature fs-6"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-cancelar-contrato rounded-circle" data-id="${row.id}" style="width:36px;height:36px;padding:0;background-color:#e72e2e;border-color:#e72e2e;" title="Cancelar">
                                    <i class="fas fa-times text-white fs-6"></i>
                                </button>
                            </div>`;
                    }

                    if (row.estado === 'ACTIVO' && row.firmado == 1) {
                        // Contrato firmado, pendiente de liquidación
                        return `
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <button type="button" class="btn text-white btn-liquidar-contrato rounded-circle" data-id="${row.id}" style="width:36px;height:36px;padding:0;background-color:#0b667c;border-color:#0b667c;" title="Liquidar contrato">
                                    <i class="fas fa-file-invoice fs-6"></i>
                                </button>
                            </div>`;
                    }

                    // FINALIZADO o CANCELADO: solo consulta de PDF
                    return `
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <a class="btn btn-info text-white rounded-circle" href="?c=contratos&a=pdf&id=${row.id}" target="_blank" style="width:36px;height:36px;padding:0;display:inline-flex;align-items:center;justify-content:center;background-color:#0b667c;border-color:#0b667c;" title="Ver PDF">
                                <i class="fas fa-file-pdf fs-6"></i>
                            </a>
                        </div>`;
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
            zeroRecords: "No se encontraron contratos",
            emptyTable: "No hay contratos en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    document.querySelectorAll('#pills-tab-contratos .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-contratos .tab-estado').forEach(b => {
                b.classList.remove('active', 'text-white');
                b.classList.add('text-dark');
                b.style.backgroundColor = '';
                b.style.borderColor = '';
            });
            this.classList.add('active', 'text-white');
            this.classList.remove('text-dark');
            this.style.backgroundColor = '#d9383a';
            this.style.borderColor = '#d9383a';

            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload(null, true);
        });
    });

    // ---------------------------------------------------------
    // Nuevo contrato
    // ---------------------------------------------------------
    const btnNuevo = document.querySelector('.btn-nuevo-contrato');
    if (btnNuevo) {
        btnNuevo.addEventListener('click', async function () {
            if (formContrato) formContrato.reset();
            document.getElementById('es_edicion').value = '0';
            document.getElementById('contrato_id').value = '';
            inputClienteDoc.value = '';
            buscadorCliente.value = '';
            resumenCosto.textContent = money(0);
            detalleCosto.textContent = 'Complete fechas y tarifa para ver el cálculo (días completos + fracción proporcional de horas extra).';
            lblTituloContrato.innerText = 'NUEVO CONTRATO';

            await cargarVehiculosDisponibles();
            contratoModal.show();
        });
    }

    // ---------------------------------------------------------
    // Acciones de fila
    // ---------------------------------------------------------
    document.addEventListener('click', function (e) {
        const btnEditar = e.target.closest('.btn-editar-contrato');
        if (btnEditar && contratoModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-contrato')));

            (async () => {
                if (formContrato) formContrato.reset();
                document.getElementById('es_edicion').value = '1';
                document.getElementById('contrato_id').value = data.id;

                inputClienteDoc.value = data.cliente_doc;
                buscadorCliente.value = data.cliente_nombre + ' (' + data.cliente_doc + ')';

                inputFechaSalida.value = data.fecha_hora_salida.replace(' ', 'T').slice(0, 16);
                inputFechaDevolucion.value = data.fecha_hora_devolucion_estimada.replace(' ', 'T').slice(0, 16);
                inputTarifa.value = parseFloat(data.tarifa_dia_pactada).toFixed(2);
                document.getElementById('kilometraje_salida').value = data.kilometraje_salida;
                document.getElementById('observaciones_salida').value = data.observaciones_salida || '';

                await cargarVehiculosDisponibles(data.placa_vehiculo);
                calcularCostoEnVivo();

                lblTituloContrato.innerText = 'EDITAR CONTRATO #' + data.id;
                contratoModal.show();
            })();
        }

        const btnFirmar = e.target.closest('.btn-firmar-contrato');
        if (btnFirmar) {
            const id = btnFirmar.getAttribute('data-id');
            Swal.fire({
                title: '¿Firmar contrato?',
                text: 'Una vez firmado ya no podrá editarse ni cancelarse, y el vehículo pasará a estado ALQUILADO.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2ab418',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Firmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('?c=contratos&a=firmar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Firmado', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al firmar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        const btnCancelar = e.target.closest('.btn-cancelar-contrato');
        if (btnCancelar) {
            const id = btnCancelar.getAttribute('data-id');
            Swal.fire({
                title: '¿Cancelar contrato?',
                text: 'El contrato pasará a la pestaña de Cancelados.',
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
                    fetch('?c=contratos&a=cancelar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Cancelado', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al cancelar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        const btnLiquidar = e.target.closest('.btn-liquidar-contrato');
        if (btnLiquidar) {
            // El modal de Liquidación de contrato / Cargos de alquiler se implementa en el siguiente paso.
            Swal.fire('Próximamente', 'El modal de Liquidación se agregará en la siguiente etapa del módulo.', 'info');
        }
    });

    // ---------------------------------------------------------
    // Guardar (crear/editar)
    // ---------------------------------------------------------
    if (formContrato) {
        formContrato.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!inputClienteDoc.value) {
                Swal.fire('Atención', 'Debe seleccionar un cliente de la lista de búsqueda.', 'warning');
                return;
            }

            const formData = new FormData(this);
            formData.set('cliente_doc', inputClienteDoc.value);

            try {
                const res = await fetch('?c=contratos&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar el contrato');

                if (contratoModal) contratoModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});