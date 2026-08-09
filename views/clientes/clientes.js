$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    const modalClienteElement = document.getElementById('modalCliente');
    const clienteModal = modalClienteElement ? new bootstrap.Modal(modalClienteElement) : null;
    const formCliente = document.getElementById('formCliente');
    const lblTituloCliente = document.getElementById('lblTituloCliente');
    const inputTipoDoc = document.getElementById('tipo_documento');
    const inputNumeroDoc = document.getElementById('numero_documento');
    const hintDocBloqueado = document.getElementById('hintDocumentoBloqueado');

    const modalCambiarDocElement = document.getElementById('modalCambiarDocumento');
    const cambiarDocModal = modalCambiarDocElement ? new bootstrap.Modal(modalCambiarDocElement) : null;
    const formCambiarDocumento = document.getElementById('formCambiarDocumento');
    const inputNuevoTipoDoc = document.getElementById('nuevo_tipo_documento');
    const inputNuevoNumeroDoc = document.getElementById('nuevo_numero_documento');

    // Reglas de validación para documentos de Perú
    const reglasDocumentos = {
        'DNI': { min: 8, max: 8, mensaje: 'El DNI debe tener exactamente 8 dígitos numéricos.', soloNumeros: true, placeholder: 'Ej: 12345678' },
        'RUC': { min: 11, max: 11, mensaje: 'El RUC debe tener exactamente 11 dígitos numéricos.', soloNumeros: true, placeholder: 'Ej: 00123456789' },
        'CE': { min: 9, max: 12, mensaje: 'El Carné de Extranjería debe tener entre 9 y 12 caracteres.', soloNumeros: false, placeholder: 'Ej: 000123456789' },
        'PASAPORTE': { min: 8, max: 15, mensaje: 'El Pasaporte debe tener entre 8 y 15 caracteres alfanuméricos.', soloNumeros: false, placeholder: 'Ej: A12345678901234' }
    };

    function aplicarReglas(tipoSelect, inputNum) {
        if (!tipoSelect || !inputNum) return;
        const tipo = tipoSelect.value;
        const regla = reglasDocumentos[tipo];

        if (regla) {
            inputNum.setAttribute('minlength', regla.min);
            inputNum.setAttribute('maxlength', regla.max);
            inputNum.setAttribute('placeholder', regla.placeholder);
            
            const feedbackEl = inputNum.nextElementSibling;
            if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                feedbackEl.textContent = regla.mensaje;
            }
        }
    }

    if (inputTipoDoc) {
        inputTipoDoc.addEventListener('change', function () {
            aplicarReglas(inputTipoDoc, inputNumeroDoc);
            inputNumeroDoc.value = '';
        });
    }

    if (inputNumeroDoc) {
        inputNumeroDoc.addEventListener('input', function () {
            const regla = reglasDocumentos[inputTipoDoc.value];
            if (regla && regla.soloNumeros) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });
    }

    if (inputNuevoTipoDoc) {
        inputNuevoTipoDoc.addEventListener('change', function () {
            aplicarReglas(inputNuevoTipoDoc, inputNuevoNumeroDoc);
            inputNuevoNumeroDoc.value = '';
        });
    }

    if (inputNuevoNumeroDoc) {
        inputNuevoNumeroDoc.addEventListener('input', function () {
            const regla = reglasDocumentos[inputNuevoTipoDoc.value];
            if (regla && regla.soloNumeros) {
                this.value = this.value.replace(/[^0-9]/g, '');
            }
        });
    }

    function badgeEstado(activo) {
        return activo == 1
            ? '<span class="badge bg-success">ACTIVO</span>'
            : '<span class="badge bg-dark">INACTIVO</span>';
    }

    const tabla = $('#datatable_clientes').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=clientes&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: d => `<span class="fw-bold" style="color:#1e293b;">${d.tipo_documento}: ${d.numero_documento}</span>`
            },
            { data: 'razon_social' },
            {
                data: 'licencia_conducir',
                render: d => d ? `<span class="fw-semibold text-uppercase">${d}</span>` : '<span class="text-muted small">-</span>'
            },
            {
                data: 'correo',
                render: d => d ? d : '<span class="text-muted small">-</span>'
            },
            {
                data: 'telefono',
                render: d => d ? d : '<span class="text-muted small">-</span>'
            },
            { data: 'pais' },
            {
                data: 'activo',
                render: d => badgeEstado(d)
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
                            <button type="button" class="btn btn-warning text-white btn-editar-cliente rounded-circle" data-cliente="${jsonRow}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #FFA41D; border-color: #FFA41D;" title="Editar">
                                <i class="fas fa-pencil-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-info text-white btn-corregir-documento rounded-circle" data-cliente="${jsonRow}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #0b667c; border-color: #0b667c;" title="Corregir Documento">
                                <i class="fas fa-id-card fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-desactivar-cliente rounded-circle" data-numero="${row.numero_documento}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #e72e2e; border-color: #e72e2e;" title="Desactivar">
                                <i class="fas fa-times text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-cliente rounded-circle" data-numero="${row.numero_documento}" style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2ab418; border-color: #2ab418;" title="Restaurar">
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
            zeroRecords: "No se encontraron clientes",
            emptyTable: "No hay clientes en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)"
        }
    });

    document.querySelectorAll('#pills-tab-clientes .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-clientes .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    const inputPais = document.getElementById('inputPais');
    const listaPaisesFlotante = document.getElementById('listaPaisesFlotante');

    if (inputPais && listaPaisesFlotante) {
        const itemsPaises = listaPaisesFlotante.querySelectorAll('.pais-item');

        inputPais.addEventListener('focus', () => {
            listaPaisesFlotante.style.display = 'block';
        });

        inputPais.addEventListener('input', function () {
            const filtro = this.value.toLowerCase().trim();
            listaPaisesFlotante.style.display = 'block';

            itemsPaises.forEach(item => {
                const texto = item.textContent.toLowerCase();
                item.style.display = texto.includes(filtro) ? 'block' : 'none';
            });
        });

        itemsPaises.forEach(item => {
            item.addEventListener('click', function () {
                inputPais.value = this.textContent;
                listaPaisesFlotante.style.display = 'none';
            });
        });

        document.addEventListener('click', function (e) {
            if (!inputPais.contains(e.target) && !listaPaisesFlotante.contains(e.target)) {
                listaPaisesFlotante.style.display = 'none';
            }
        });
    }

    const btnNuevo = document.querySelector('.btn-nuevo-cliente');
    if (btnNuevo && clienteModal) {
        btnNuevo.addEventListener('click', function () {
            if (formCliente) formCliente.reset();
            document.getElementById('es_edicion').value = '0';
            if (inputTipoDoc) {
                inputTipoDoc.disabled = false;
                inputTipoDoc.value = 'DNI';
            }
            aplicarReglas(inputTipoDoc, inputNumeroDoc);
            if (inputNumeroDoc) inputNumeroDoc.readOnly = false;
            if (hintDocBloqueado) hintDocBloqueado.style.display = 'none';
            if (lblTituloCliente) lblTituloCliente.innerText = 'NUEVO CLIENTE';
            clienteModal.show();
        });
    }

    document.addEventListener('click', function (e) {
        const btnEditar = e.target.closest('.btn-editar-cliente');
        if (btnEditar && clienteModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-cliente')));

            if (formCliente) formCliente.reset();
            document.getElementById('es_edicion').value = '1';
            document.getElementById('numero_documento_original').value = data.numero_documento;

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val || '';
            };

            setVal('tipo_documento', data.tipo_documento);
            aplicarReglas(inputTipoDoc, inputNumeroDoc);
            setVal('numero_documento', data.numero_documento);
            setVal('razon_social', data.razon_social);
            setVal('licencia_conducir', data.licencia_conducir);
            setVal('correo', data.correo);
            setVal('telefono', data.telefono);
            setVal('direccion', data.direccion);
            setVal('inputPais', data.pais);

            if (inputTipoDoc) inputTipoDoc.disabled = true;
            if (inputNumeroDoc) inputNumeroDoc.readOnly = true;
            if (hintDocBloqueado) hintDocBloqueado.style.display = 'block';

            if (lblTituloCliente) lblTituloCliente.innerText = 'EDITAR CLIENTE: ' + data.numero_documento;
            clienteModal.show();
        }

        const btnCorregir = e.target.closest('.btn-corregir-documento');
        if (btnCorregir && cambiarDocModal) {
            const data = JSON.parse(decodeURIComponent(btnCorregir.getAttribute('data-cliente')));

            if (formCambiarDocumento) formCambiarDocumento.reset();
            document.getElementById('numero_documento_actual').value = data.numero_documento;
            document.getElementById('documento_actual_display').value = data.tipo_documento + ': ' + data.numero_documento;
            
            document.getElementById('nuevo_tipo_documento').value = data.tipo_documento;
            aplicarReglas(inputNuevoTipoDoc, inputNuevoNumeroDoc);
            document.getElementById('nuevo_numero_documento').value = data.numero_documento;

            cambiarDocModal.show();
        }

        const btnDesactivar = e.target.closest('.btn-desactivar-cliente');
        if (btnDesactivar) {
            const numero = btnDesactivar.getAttribute('data-numero');

            Swal.fire({
                title: '¿Desactivar Cliente?',
                text: `¿Deseas desactivar al cliente ${numero}?`,
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#34495e',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('numero_documento', numero);
                    fetch('?c=clientes&a=eliminar', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                tabla.ajax.reload(null, false);
                                Swal.fire('Desactivado', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message || 'Error al desactivar', 'warning');
                            }
                        })
                        .catch(() => Swal.fire('Error', 'Error en la petición al servidor', 'error'));
                }
            });
        }

        const btnRestaurar = e.target.closest('.btn-restaurar-cliente');
        if (btnRestaurar) {
            const numero = btnRestaurar.getAttribute('data-numero');

            Swal.fire({
                title: '¿Restaurar Cliente?',
                text: `¿Deseas volver a activar al cliente ${numero}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('numero_documento', numero);
                    fetch('?c=clientes&a=restaurar', { method: 'POST', body: formData })
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

    if (formCliente) {
        formCliente.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (inputTipoDoc) inputTipoDoc.disabled = false;
            const formData = new FormData(this);

            try {
                const res = await fetch('?c=clientes&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar el cliente');

                if (clienteModal) clienteModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    if (formCambiarDocumento) {
        formCambiarDocumento.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('?c=clientes&a=cambiarDocumento', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al corregir el documento');

                if (cambiarDocModal) cambiarDocModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Corregido!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});