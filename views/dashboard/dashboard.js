$(document).ready(function() {

    // Modal de Detalle IP
    const modalElement = document.getElementById('modalIpDetalle');
    const modalIp = modalElement ? new bootstrap.Modal(modalElement) : null;

    // ==========================================
    // 1. CARGA DE METRICAS EN CARDS (ESTADÍAS E INGRESOS)
    // ==========================================
    function cargarReporteVentas() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        $.ajax({
            url: '?c=dashboard&a=reporteVentas',
            type: 'GET',
            data: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#lbl_total_ventas').text(res.data.total_estadias || 0);
                    $('#lbl_ingreso_total').text('S/ ' + parseFloat(res.data.ingreso_total || 0).toFixed(2));
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudieron obtener las métricas de estadías', 'error');
            }
        });
    }

    // ==========================================
    // 2. DATATABLE: OCUPACIÓN DE HABITACIONES
    // ==========================================
    const tablaProductos = $('#datatable_productos').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=dashboard&a=reporteOcupacion',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'numero_habitacion',
                render: function(data) {
                    return `<strong>Nº ${data}</strong>`;
                }
            },
            { 
                data: 'tipo_nombre',
                render: function(data) {
                    return `<span class="badge bg-soft-primary text-primary">${data}</span>`;
                }
            },
            { 
                data: 'piso',
                render: function(data) {
                    return `Piso ${data}`;
                }
            },
            { 
                data: 'estado_disponibilidad',
                render: function(data) {
                    let badgeClass = 'bg-success';
                    if (data === 'OCUPADA') badgeClass = 'bg-danger';
                    else if (data === 'MANTENIMIENTO') badgeClass = 'bg-warning text-dark';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { 
                data: 'precio_base_noche',
                render: function(data) {
                    return `<strong class="text-success">S/ ${parseFloat(data).toFixed(2)}</strong>`;
                }
            },
            { 
                data: 'cliente_actual',
                render: function(data) {
                    return data ? `<span class="fw-bold text-dark">${data}</span>` : '<span class="text-muted fst-italic">Disponible</span>';
                }
            }
        ],
        language: {
            search: "Buscar habitación / cliente:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No hay habitaciones registradas",
            emptyTable: "No hay datos de habitaciones disponibles",
            loadingRecords: "Cargando...", 
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left"></i>'
            }
        }
    });

    // ==========================================
    // 3. DATATABLE: HISTORIAL DE IPs
    // ==========================================
    const tablaIps = $('#datatable_ips').DataTable({
        autoWidth: false,
        serverSide: false,
        responsive: true,
        bDestroy: true,
        ajax: {
            url: '?c=dashboard&a=listarIps',
            dataSrc: 'data'
        },
        columns: [
            { 
                data: 'usuario_username',
                render: function(data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { data: 'usuario_nombre_completo' },
            { 
                data: 'rol_name',
                render: function(data) {
                    return `<span class="badge bg-soft-info text-info fw-bold">${data}</span>`;
                }
            },
            { 
                data: 'ip_address',
                render: function(data) {
                    return `<code class="fw-bold fs-6">${data}</code>`;
                }
            },
            { data: 'fecha_acceso' },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(row) {
                    return `
                        <button type="button" 
                                class="btn btn-primary btn-ver-ip rounded-circle" 
                                data-id="${row.id}" 
                                style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                title="Ver Detalle IP">
                            <i class="fas fa-eye text-white fs-6"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            search: "Buscar IP / Usuario:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron conexiones de IP",
            emptyTable: "No hay historial de conexiones",
            loadingRecords: "Cargando...", 
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            paginate: {
                next: '<i class="fa fa-angle-double-right"></i>',
                previous: '<i class="fa fa-angle-double-left"></i>'
            }
        }
    });

    // ==========================================
    // 4. EVENTOS (FILTRAR Y MODAL)
    // ==========================================

    // Inicializar métricas al cargar la página
    cargarReporteVentas();

    // Evento Filtrar por Fecha
    $('#btn_filtrar').on('click', function() {
        cargarReporteVentas();
        tablaProductos.ajax.reload();
    });

    // Ver Detalle de registro IP
    document.addEventListener('click', function(e) {
        const btnVerIp = e.target.closest('.btn-ver-ip');
        if (btnVerIp && modalIp) {
            const id = btnVerIp.getAttribute('data-id');

            fetch(`?c=dashboard&a=getIpById&id=${id}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;
                        document.getElementById('det_ip_id').innerText = `#${data.id}`;
                        document.getElementById('det_ip_usuario').innerText = data.usuario_username;
                        document.getElementById('det_ip_nombre').innerText = data.usuario_nombre_completo;
                        document.getElementById('det_ip_correo').innerText = data.correo || '-';
                        document.getElementById('det_ip_rol').innerText = data.rol_name;
                        document.getElementById('det_ip_address').innerText = data.ip_address;
                        document.getElementById('det_ip_fecha').innerText = data.fecha_acceso;

                        modalIp.show();
                    } else {
                        Swal.fire('Error', res.message || 'No se pudo obtener la información de la IP', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Error al realizar la consulta al servidor.', 'error');
                });
        }
    });
});