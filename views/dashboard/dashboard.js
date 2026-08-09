$(document).ready(function() {

    // Silenciar alertas de error de DataTables
    $.fn.dataTable.ext.errMode = 'none';

    const modalElement = document.getElementById('modalIpDetalle');
    const modalIp = modalElement ? new bootstrap.Modal(modalElement) : null;

    // Configuración de Idioma en Español
    const lenguajeEspanol = {
        processing:     "Procesando...",
        search:         "Buscar:",
        lengthMenu:     "Mostrar _MENU_ registros",
        info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
        infoEmpty:      "Mostrando 0 a 0 de 0 registros",
        infoFiltered:   "(filtrado de _MAX_ registros totales)",
        infoPostFix:    "",
        loadingRecords: "Cargando...",
        zeroRecords:    "No se encontraron resultados",
        emptyTable:     "Ningún dato disponible en esta tabla",
        paginate: {
            first:      "Primero",
            previous:   "Anterior",
            next:       "Siguiente",
            last:       "Último"
        },
        aria: {
            sortAscending:  ": Activar para ordenar la columna de manera ascendente",
            sortDescending: ": Activar para ordenar la columna de manera descendente"
        }
    };

    // Función para reestructurar la paginación y eliminar bordes/hovers naranjas nativos
    function corregirPaginacionJS(settings) {
        const $wrapper = $(settings.nTableWrapper);
        const $paginate = $wrapper.find('.dataTables_paginate');

        // Limpiar estilos del contenedor principal
        $paginate.attr('style', 'display: flex !important; align-items: center !important; justify-content: flex-end !important; padding-top: 8px !important; background: transparent !important; border: none !important;');

        // Eliminar contenedor de barra inferior que genera la línea naranja en DataTables
        $wrapper.find('.dataTables_scrollBody, .dataTables_wrapper').css({
            'border': 'none',
            'box-shadow': 'none',
            'outline': 'none'
        });

        // Iterar todos los elementos de paginación dentro (Anterior, números, Siguiente)
        $paginate.find('a, span, .paginate_button').each(function() {
            const $btn = $(this);
            
            // Remover estilos/clases nativas que disparan el naranja
            $btn.removeClass('paginate_button').css({
                'background': 'none',
                'border': 'none',
                'box-shadow': 'none',
                'outline': 'none'
            });

            const esActivo = $btn.hasClass('current');
            const esDeshabilitado = $btn.hasClass('disabled');
            const esNumero = !$btn.hasClass('previous') && !$btn.hasClass('next') && !$btn.hasClass('first') && !$btn.hasClass('last') && $btn.children().length === 0;

            if (esNumero) {
                $btn.attr('style', `
                    display: inline-flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    width: 32px !important;
                    height: 32px !important;
                    border-radius: 50% !important;
                    margin: 0 10px !important;
                    font-weight: bold !important;
                    font-size: 0.875rem !important;
                    text-decoration: none !important;
                    cursor: ${esDeshabilitado ? 'default' : 'pointer'} !important;
                    background-color: ${esActivo ? '#d9383a' : 'transparent'} !important;
                    color: ${esActivo ? '#ffffff' : '#6c757d'} !important;
                    border: none !important;
                `);
            } else if ($btn.hasClass('previous') || $btn.hasClass('next')) {
                $btn.attr('style', `
                    display: inline-block !important;
                    padding: 4px 8px !important;
                    margin: 0 5px !important;
                    font-size: 0.85rem !important;
                    text-decoration: none !important;
                    cursor: ${esDeshabilitado ? 'default' : 'pointer'} !important;
                    background: transparent !important;
                    color: ${esDeshabilitado ? '#cccccc' : '#6c757d'} !important;
                    opacity: ${esDeshabilitado ? '0.5' : '1'} !important;
                    border: none !important;
                `);
            }

            // Manejo de Hover por JS puro (sin CSS externo)
            $btn.off('mouseenter mouseleave').on('mouseenter', function() {
                if (esDeshabilitado) return;
                if (esActivo) {
                    $(this).css('background-color', '#bd3133');
                } else if (esNumero) {
                    $(this).css({ 'background-color': '#e9ecef', 'color': '#212529' });
                } else {
                    $(this).css({ 'background-color': '#f8f9fa', 'color': '#212529' });
                }
            }).on('mouseleave', function() {
                if (esDeshabilitado) return;
                if (esActivo) {
                    $(this).css({ 'background-color': '#d9383a', 'color': '#ffffff' });
                } else {
                    $(this).css({ 'background-color': 'transparent', 'color': '#6c757d' });
                }
            });
        });
    }

    // ==========================================
    // 1. CARGAR MÉTRICAS
    // ==========================================
    function cargarMetricas() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        $.ajax({
            url: `?c=dashboard&a=getMetricas&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#lbl_total_ventas').text(res.data.total_ventas);
                    $('#lbl_ingreso_total').text(`S/ ${res.data.ingreso_total}`);
                }
            }
        });
    }

    cargarMetricas();
    $('#btn_filtrar').on('click', function() { cargarMetricas(); });

    // ==========================================
    // 2. DATATABLE: OPERACIONES (ENTREGAS/DEVOLUCIONES)
    // ==========================================
    $('#datatable_operaciones').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        pagingType: 'simple_numbers',
        ajax: {
            url: '?c=dashboard&a=getOperacionesProximas',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'numero_contrato',
                render: function(data) { return `<span class="fw-bold">#${data}</span>`; }
            },
            { data: 'cliente_nombre' },
            { 
                data: null,
                render: function(row) {
                    return `<strong>${row.vehiculo}</strong> <span class="badge bg-dark ms-1">${row.placa}</span>`;
                }
            },
            { 
                data: 'tipo_operacion',
                render: function(data) {
                    return data === 'ENTREGA' 
                        ? `<span class="badge text-white px-2 py-1" style="background-color: #6f42c1;"><i class="fas fa-key me-1"></i>ENTREGA</span>`
                        : `<span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-undo me-1"></i>DEVOLUCIÓN</span>`;
                }
            },
            { data: 'fecha_programada' },
            { 
                data: 'estado',
                render: function(data) {
                    return `<span class="badge text-white px-2 py-1 fw-bold" style="background-color: #28a745;">${data}</span>`;
                }
            }
        ],
        language: lenguajeEspanol,
        drawCallback: function(settings) {
            corregirPaginacionJS(settings);
        }
    });

    // ==========================================
    // 3. DATATABLE: ESTADO DE FLOTA
    // ==========================================
    $('#datatable_flota').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        pagingType: 'simple_numbers',
        ajax: {
            url: '?c=dashboard&a=getEstadoFlota',
            dataSrc: 'data'
        },
        columns: [
            { data: 'placa', render: function(d) { return `<span class="badge bg-dark">${d}</span>`; } },
            { data: null, render: function(r) { return `<strong>${r.marca} ${r.modelo}</strong>`; } },
            { data: 'precio_base_dia', render: function(d) { return `S/ ${parseFloat(d).toFixed(2)}`; } },
            { 
                data: 'estado_disponibilidad',
                render: function(d) {
                    if (d === 'DISPONIBLE') return `<span class="badge bg-success">DISPONIBLE</span>`;
                    if (d === 'ALQUILADO') return `<span class="badge bg-danger">ALQUILADO</span>`;
                    return `<span class="badge bg-warning text-dark">MANTENIMIENTO</span>`;
                }
            },
            { data: 'cliente_actual', render: function(d) { return d ? `<span class="fw-bold text-danger">${d}</span>` : `-`; } }
        ],
        language: lenguajeEspanol,
        drawCallback: function(settings) {
            corregirPaginacionJS(settings);
        }
    });

    // ==========================================
    // 4. DATATABLE: TOP CLIENTES
    // ==========================================
    $('#datatable_top_clientes').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        pagingType: 'simple_numbers',
        ajax: {
            url: '?c=dashboard&a=getTopClientes',
            dataSrc: 'data'
        },
        columns: [
            { data: 'cliente_nombre', render: function(d) { return `<strong>${d}</strong>`; } },
            { data: 'total_alquileres', className: 'text-center' },
            { data: 'total_gastado', render: function(d) { return `S/ ${parseFloat(d).toFixed(2)}`; } }
        ],
        language: lenguajeEspanol,
        drawCallback: function(settings) {
            corregirPaginacionJS(settings);
        }
    });

    // ==========================================
    // 5. DATATABLE: AUDITORÍA / HISTORIAL DE IPS
    // ==========================================
    $('#datatable_ips').DataTable({
        autoWidth: false,
        responsive: true,
        bDestroy: true,
        pagingType: 'simple_numbers',
        ajax: {
            url: '?c=dashboard&a=listarIps',
            dataSrc: 'data'
        },
        columns: [
            { data: 'usuario_username', render: function(d) { return `<strong>${d}</strong>`; } },
            { data: 'usuario_nombre_completo' },
            { data: 'rol_name', render: function(d) { return `<span class="badge text-white" style="background-color: #6f42c1;">${d}</span>`; } },
            { data: 'ip_address', render: function(d) { return `<code class="text-danger fw-bold">${d}</code>`; } },
            { data: 'fecha_acceso' },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(row) {
                    return `
                        <button type="button" class="btn btn-ver-ip rounded-circle text-white shadow-sm" data-id="${row.id}" style="width: 32px; height: 32px; padding:0; background-color: #d9383a; border: none;">
                            <i class="fas fa-eye text-white"></i>
                        </button>`;
                }
            }
        ],
        language: lenguajeEspanol,
        drawCallback: function(settings) {
            corregirPaginacionJS(settings);
        }
    });

    // Modal Auditoría IP
    document.addEventListener('click', function(e) {
        const btnVerIp = e.target.closest('.btn-ver-ip');
        if (btnVerIp && modalIp) {
            const id = btnVerIp.getAttribute('data-id');
            fetch(`?c=dashboard&a=getIpById&id=${id}`)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const d = res.data;
                        document.getElementById('det_ip_id').innerText = `#${d.id}`;
                        document.getElementById('det_ip_usuario').innerText = d.usuario_username;
                        document.getElementById('det_ip_nombre').innerText = d.usuario_nombre_completo;
                        document.getElementById('det_ip_correo').innerText = d.correo || '-';
                        document.getElementById('det_ip_rol').innerText = d.rol_name;
                        document.getElementById('det_ip_address').innerText = d.ip_address;
                        document.getElementById('det_ip_fecha').innerText = d.fecha_acceso;
                        modalIp.show();
                    }
                });
        }
    });

});