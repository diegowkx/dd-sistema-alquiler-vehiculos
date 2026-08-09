$(document).ready(function () {
    let estadoFiltro = 'ACTIVO';

    // Elementos del Modal Principal y Formulario
    const modalVehiculoElement = document.getElementById('modalVehiculo');
    const vehiculoModal = modalVehiculoElement ? new bootstrap.Modal(modalVehiculoElement) : null;
    const formVehiculo = document.getElementById('formVehiculo');
    const lblTituloVehiculo = document.getElementById('lblTituloVehiculo');

    // Elementos del Modal Cambiar Placa
    const modalCambiarPlacaElement = document.getElementById('modalCambiarPlaca');
    const cambiarPlacaModal = modalCambiarPlacaElement ? new bootstrap.Modal(modalCambiarPlacaElement) : null;
    const formCambiarPlaca = document.getElementById('formCambiarPlaca');

    // Manejo de accesibilidad Bootstrap Modal
    if (modalVehiculoElement) {
        modalVehiculoElement.addEventListener('hide.bs.modal', function () {
            if (document.activeElement) document.activeElement.blur();
        });
    }

    if (modalCambiarPlacaElement) {
        modalCambiarPlacaElement.addEventListener('hide.bs.modal', function () {
            if (document.activeElement) document.activeElement.blur();
        });
    }

    // Elementos de Imagen
    const imagenUrlHidden = document.getElementById('imagen_url');
    const radioLocal = document.getElementById('tipoLocal');
    const radioUrl = document.getElementById('tipoUrl');
    const contenedorLocal = document.getElementById('contenedorLocal');
    const contenedorUrl = document.getElementById('contenedorUrl');
    const selectImagenLocal = document.getElementById('selectImagenLocal');
    const inputImagenUrl = document.getElementById('inputImagenUrl');
    const previewImagen = document.getElementById('previewImagen');

    const DEFAULT_IMAGE = 'assets/vehiculos/default.png';
    const SVG_FALLBACK = 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 24 24" fill="none" stroke="%23ccc" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';

    function resolverRutaImagen(url) {
        if (!url || url.trim() === '') return DEFAULT_IMAGE;
        if (url.startsWith('http://') || url.startsWith('https://')) return url;

        const pathName = window.location.pathname;
        const basePath = pathName.substring(0, pathName.lastIndexOf('/'));
        const cleanUrl = url.replace(/^\//, '');

        return `${window.location.origin}${basePath}/${cleanUrl}`;
    }

    function actualizarVistaPrevia(url) {
        const rutaRelativa = url || DEFAULT_IMAGE;
        if (imagenUrlHidden) imagenUrlHidden.value = rutaRelativa;

        if (previewImagen) {
            previewImagen.onerror = null;
            previewImagen.onerror = function () {
                this.onerror = null;
                this.src = SVG_FALLBACK;
            };
            previewImagen.src = resolverRutaImagen(rutaRelativa);
        }
    }

    function cambiarTipoOrigenImagen(tipo) {
        if (tipo === 'local') {
            if (contenedorLocal) contenedorLocal.classList.remove('d-none');
            if (contenedorUrl) contenedorUrl.classList.add('d-none');
            if (selectImagenLocal) actualizarVistaPrevia(selectImagenLocal.value);
        } else {
            if (contenedorLocal) contenedorLocal.classList.add('d-none');
            if (contenedorUrl) contenedorUrl.classList.remove('d-none');
            if (inputImagenUrl) actualizarVistaPrevia(inputImagenUrl.value);
        }
    }

    function badgeEstadoDisponibilidad(estado) {
        const clases = {
            'DISPONIBLE': 'bg-success',
            'ALQUILADO': 'bg-danger',
            'MANTENIMIENTO': 'bg-secondary'
        };
        return `<span class="badge ${clases[estado] || 'bg-secondary'}" style="font-size: 0.75rem;">${estado}</span>`;
    }

    if (radioLocal && radioUrl) {
        radioLocal.addEventListener('change', () => cambiarTipoOrigenImagen('local'));
        radioUrl.addEventListener('change', () => cambiarTipoOrigenImagen('url'));
    }

    if (selectImagenLocal) {
        selectImagenLocal.addEventListener('change', function () {
            actualizarVistaPrevia(this.value);
        });
    }

    if (inputImagenUrl) {
        inputImagenUrl.addEventListener('input', function () {
            actualizarVistaPrevia(this.value);
        });
    }

    // ==========================================
    // DATATABLES (Estructura Unificada)
    // ==========================================
    const tabla = $('#datatable_vehiculos').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: false,
        bDestroy: true,
        order: [[1, 'asc']], // <-- ORDENA INICIALMENTE POR PLACA
        ajax: {
            url: '?c=vehiculos&a=listar',
            data: function (d) { d.estado = estadoFiltro; },
            dataSrc: function (json) {
                if (!json || !json.success || !Array.isArray(json.data)) return [];
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                orderable: false, 
                className: 'text-center align-middle p-1',
                width: '45px',
                render: function (row) {
                    var rutaImg = resolverRutaImagen(row.imagen_url);
                    return '<img src="' + rutaImg + '" class="rounded shadow-sm" style="width: 38px; height: 38px; object-fit: cover;" alt="Vehículo">';
                }
            },
            { 
                data: 'placa', 
                className: 'align-middle',
                render: function(d) { return '<span class="fw-bold text-dark text-nowrap" style="font-size: 0.85rem;">' + (d || '') + '</span>'; } 
            },
            { 
                data: null, 
                className: 'align-middle',
                render: function(row) { return '<span class="text-nowrap" style="font-size: 0.85rem;">' + (row.marca || '') + ' ' + (row.modelo || '') + '</span>'; } 
            },
            { 
                data: 'tipo_nombre', 
                className: 'align-middle text-center',
                render: function(d) { return '<span class="badge bg-secondary" style="font-size: 0.75rem;">' + (d || 'Sin Tipo') + '</span>'; } 
            },
            { 
                data: 'color',
                className: 'align-middle',
                render: function(d) { return '<span class="text-nowrap" style="font-size: 0.8rem;">' + (d || '-') + '</span>'; }
            },
            { 
                data: 'anio',
                className: 'align-middle text-center',
                render: function(d) { return '<span style="font-size: 0.8rem;">' + (d ? d : '-') + '</span>'; }
            },
            { 
                data: 'kilometraje',
                className: 'align-middle text-end',
                render: function(d) { 
                    var km = d ? parseInt(d).toLocaleString('es-PE') : '0';
                    return '<span class="text-nowrap" style="font-size: 0.8rem;">' + km + ' km</span>'; 
                }
            },
            { 
                data: null, 
                className: 'align-middle text-end',
                render: function(row) {
                    var precio = row.precio_base_dia ? parseFloat(row.precio_base_dia) : 0;
                    return '<span class="fw-semibold text-nowrap" style="font-size: 0.85rem;">S/ ' + precio.toFixed(2) + '</span>';
                } 
            },
            {
                data: null,
                className: 'align-middle text-center',
                render: function (row) {
                    if (row.activo == 0) return '<span class="badge bg-dark" style="font-size: 0.75rem;">INACTIVO</span>';
                    return badgeEstadoDisponibilidad(row.estado_disponibilidad);
                }
            },
            {
                data: 'observaciones',
                className: 'align-middle',
                render: function(d) {
                    if (!d || d.trim() === '') return '<span class="text-muted" style="font-size: 0.75rem;">-</span>';
                    return '<div style="font-size: 0.75rem; line-height: 1.15; max-width: 180px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;" title="' + d + '">' + d + '</div>';
                }
            },
            {
                data: null,
                orderable: false, 
                className: 'text-center align-middle p-1',
                width: '130px',
                render: function (row) {
                    var jsonRow = encodeURIComponent(JSON.stringify(row));
                    var botones = '';

                    if (row.activo == 1) {
                        botones += `
                            <button type="button" class="btn btn-warning text-white btn-editar-vehiculo rounded-circle" data-vehiculo="${jsonRow}" style="width: 36px; height: 36px; min-width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #FFA41D; border-color: #FFA41D;" title="Editar">
                                <i class="fas fa-pencil-alt fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-info text-white btn-corregir-placa rounded-circle" data-vehiculo="${jsonRow}" style="width: 36px; height: 36px; min-width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #0b667c; border-color: #0b667c;" title="Corregir Placa">
                                <i class="fas fa-credit-card fs-6"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-desactivar-vehiculo rounded-circle" data-placa="${row.placa}" style="width: 36px; height: 36px; min-width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #e72e2e; border-color: #e72e2e;" title="Desactivar">
                                <i class="fas fa-times text-white fs-6"></i>
                            </button>
                        `;
                    } else {
                        botones += `
                            <button type="button" class="btn btn-success btn-restaurar-vehiculo rounded-circle" data-placa="${row.placa}" style="width: 36px; height: 36px; min-width: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2ab418; border-color: #2ab418;" title="Restaurar">
                                <i class="fas fa-check text-white fs-6"></i>
                            </button>
                        `;
                    }
                    return '<div class="d-flex align-items-center justify-content-center" style="gap: 6px;">' + botones + '</div>';
                }
            }
        ],
        pagingType: "simple_numbers", 
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron vehículos",
            emptyTable: "No hay vehículos en esta sección",
            loadingRecords: "Cargando...",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-chevron-right"></i>',
                previous: '<i class="fas fa-chevron-left"></i>'
            }
        }
    });

    // Pestañas de Filtro
    document.querySelectorAll('#pills-tab-vehiculos .tab-estado').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#pills-tab-vehiculos .tab-estado').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            estadoFiltro = this.getAttribute('data-estado');
            tabla.ajax.reload();
        });
    });

    // Modal Nuevo Vehículo
    const btnNuevo = document.querySelector('.btn-nuevo-vehiculo');
    if (btnNuevo && vehiculoModal) {
        btnNuevo.addEventListener('click', function () {
            if (formVehiculo) formVehiculo.reset();

            const setVal = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };
            setVal('es_edicion', '0');
            setVal('placa_original', '');

            const inputPlaca = document.getElementById('placa');
            if (inputPlaca) inputPlaca.readOnly = false;

            if (lblTituloVehiculo) lblTituloVehiculo.innerText = 'NUEVO VEHÍCULO';

            if (radioLocal) {
                radioLocal.checked = true;
                cambiarTipoOrigenImagen('local');
            }
            if (selectImagenLocal && selectImagenLocal.options.length > 0) {
                selectImagenLocal.selectedIndex = 0;
                actualizarVistaPrevia(selectImagenLocal.value);
            } else {
                actualizarVistaPrevia(DEFAULT_IMAGE);
            }

            vehiculoModal.show();
        });
    }

    // Delegación de Eventos
    document.addEventListener('click', function (e) {
        // EDITAR
        const btnEditar = e.target.closest('.btn-editar-vehiculo');
        if (btnEditar && vehiculoModal) {
            const data = JSON.parse(decodeURIComponent(btnEditar.getAttribute('data-vehiculo')));
            if (formVehiculo) formVehiculo.reset();

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val !== undefined && val !== null ? val : '';
            };

            setVal('es_edicion', '1');
            setVal('placa_original', data.placa || '');
            setVal('placa', data.placa);
            setVal('tipo_nombre', data.tipo_nombre);
            setVal('marca', data.marca);
            setVal('modelo', data.modelo);
            setVal('anio', data.anio);
            setVal('color', data.color);
            setVal('kilometraje', data.kilometraje);
            setVal('precio_base_dia', data.precio_base_dia);
            setVal('capacidad_pasajeros', data.capacidad_pasajeros);
            setVal('estado_disponibilidad', data.estado_disponibilidad);
            setVal('observaciones', data.observaciones);

            const inputPlaca = document.getElementById('placa');
            if (inputPlaca) inputPlaca.readOnly = true;

            const imgUrl = data.imagen_url || DEFAULT_IMAGE;
            if (imgUrl.startsWith('http://') || imgUrl.startsWith('https://')) {
                if (radioUrl) radioUrl.checked = true;
                if (inputImagenUrl) inputImagenUrl.value = imgUrl;
                cambiarTipoOrigenImagen('url');
            } else {
                if (radioLocal) radioLocal.checked = true;
                if (selectImagenLocal) selectImagenLocal.value = imgUrl;
                cambiarTipoOrigenImagen('local');
            }

            actualizarVistaPrevia(imgUrl);
            if (lblTituloVehiculo) lblTituloVehiculo.innerText = 'EDITAR VEHÍCULO: ' + data.placa;
            vehiculoModal.show();
        }

        // CORREGIR PLACA
        const btnCorregir = e.target.closest('.btn-corregir-placa');
        if (btnCorregir && cambiarPlacaModal) {
            const data = JSON.parse(decodeURIComponent(btnCorregir.getAttribute('data-vehiculo')));
            if (formCambiarPlaca) formCambiarPlaca.reset();

            document.getElementById('placa_actual').value = data.placa;
            document.getElementById('placa_actual_display').value = data.placa;
            document.getElementById('nueva_placa').value = data.placa;

            cambiarPlacaModal.show();
        }

        // DESACTIVAR
        const btnDesactivar = e.target.closest('.btn-desactivar-vehiculo');
        if (btnDesactivar) {
            const placa = btnDesactivar.getAttribute('data-placa');
            Swal.fire({
                title: '¿Desactivar Vehículo?',
                text: `¿Deseas desactivar el vehículo con placa ${placa}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, Desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('placa', placa);
                    fetch('?c=vehiculos&a=eliminar', { method: 'POST', body: formData })
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

        // RESTAURAR
        const btnRestaurar = e.target.closest('.btn-restaurar-vehiculo');
        if (btnRestaurar) {
            const placa = btnRestaurar.getAttribute('data-placa');
            Swal.fire({
                title: '¿Restaurar Vehículo?',
                text: `¿Deseas volver a activar el vehículo con placa ${placa}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'Sí, Restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('placa', placa);
                    fetch('?c=vehiculos&a=restaurar', { method: 'POST', body: formData })
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

    // Envío del Formulario Vehículo (Crear / Editar)
    if (formVehiculo) {
        formVehiculo.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('?c=vehiculos&a=guardar', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al procesar el vehículo');

                if (vehiculoModal) vehiculoModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Éxito!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }

    // Envío del Formulario Cambiar Placa
    if (formCambiarPlaca) {
        formCambiarPlaca.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            try {
                const res = await fetch('?c=vehiculos&a=cambiarPlaca', { method: 'POST', body: formData });
                const data = await res.json();
                if (!data.success) throw new Error(data.message || 'Error al cambiar la placa');

                if (cambiarPlacaModal) cambiarPlacaModal.hide();
                tabla.ajax.reload(null, false);
                Swal.fire('¡Corregido!', data.message, 'success');
            } catch (err) {
                Swal.fire('Error', err.message || 'Error en el servidor', 'error');
            }
        });
    }
});