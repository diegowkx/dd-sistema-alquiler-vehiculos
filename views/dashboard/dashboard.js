$(document).ready(function() {

    // Modal de Detalle IP
    const modalElement = document.getElementById('modalIpDetalle');
    const modalIp = modalElement ? new bootstrap.Modal(modalElement) : null;

    // ==========================================
    // DATATABLE: HISTORIAL DE IPs
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
                    return `<span class="badge text-white fw-bold" style="background-color: #334155; padding: 6px 12px; border-radius: 6px;">${data}</span>`;
                }
            },
            { 
                data: 'ip_address',
                render: function(data) {
                    return `<code class="fw-bold fs-6 text-dark px-2 py-1 bg-light rounded" style="border: 1px solid #cbd5e1;">${data}</code>`;
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
                                class="btn btn-ver-ip rounded-circle text-white shadow-sm" 
                                data-id="${row.id}" 
                                style="width: 36px; height: 36px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #d9383a; border-color: #d9383a;" 
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
        },
        drawCallback: function() {
            // Forzar el botón activo de la paginación a Rojo Automotriz
            $('.dataTables_paginate .paginate_button.current, .dataTables_paginate .page-item.active .page-link').each(function() {
                this.style.setProperty('background-color', '#d9383a', 'important');
                this.style.setProperty('background', '#d9383a', 'important');
                this.style.setProperty('border-color', '#d9383a', 'important');
                this.style.setProperty('color', '#ffffff', 'important');
            });
        }
    });

    // ==========================================
    // EVENTOS (MODAL VER DETALLE IP)
    // ==========================================
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