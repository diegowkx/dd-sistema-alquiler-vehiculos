document.addEventListener('DOMContentLoaded', () => {
    let vehiculosData = [];
    let categoriaSeleccionada = 'TODOS';

    const gridProductos = document.getElementById('grid_productos');
    const sinResultados = document.getElementById('sin_resultados');
    const inputBuscar = document.getElementById('buscar_producto');
    const contenedorFiltros = document.getElementById('contenedor-filtros-categorias');

    const cargarVehiculos = async () => {
        try {
            const response = await fetch(`${BASE_URL}?c=catalogo&a=listarVehiculos`);
            const result = await response.json();

            if (result.success) {
                vehiculosData = result.data;
                renderizarTarjetas();
            } else {
                mostrarSinResultados();
            }
        } catch (error) {
            console.error('Error al cargar catálogo:', error);
            mostrarSinResultados();
        }
    };

    const mostrarSinResultados = () => {
        if (gridProductos) gridProductos.innerHTML = '';
        if (sinResultados) sinResultados.classList.remove('d-none');
    };

    const renderizarTarjetas = () => {
        const textoBusqueda = inputBuscar ? inputBuscar.value.trim().toLowerCase() : '';

        const vehiculosFiltrados = vehiculosData.filter(v => {
            const coincideTipo = categoriaSeleccionada === 'TODOS' || 
                                 (v.tipo_nombre && v.tipo_nombre.toUpperCase() === categoriaSeleccionada.toUpperCase());

            const cadenaInfo = `${v.marca} ${v.modelo} ${v.placa} ${v.color}`.toLowerCase();
            const coincideTexto = cadenaInfo.includes(textoBusqueda);

            return coincideTipo && coincideTexto;
        });

        if (vehiculosFiltrados.length === 0) {
            mostrarSinResultados();
            return;
        }

        sinResultados.classList.add('d-none');
        gridProductos.innerHTML = vehiculosFiltrados.map(v => {
            let imagen = `${BASE_URL}/assets/vehiculos/default.png`;

            if (v.imagen_url && v.imagen_url.trim() !== '') {
                const urlLimpia = v.imagen_url.trim();
                if (urlLimpia.startsWith('http://') || urlLimpia.startsWith('https://')) {
                    imagen = urlLimpia;
                } else {
                    imagen = `${BASE_URL}/${urlLimpia.replace(/^\/+/, '')}`;
                }
            }

            const precio = parseFloat(v.precio_base_dia || 0).toFixed(2);
            const esDisponible = (v.estado_disponibilidad || '').toUpperCase() === 'DISPONIBLE';
            const bgBadge = esDisponible ? '#10b981' : '#f59e0b';

            return `
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden position-relative" style="background-color: #ffffff;">
                        <span class="badge position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill text-white fw-bold shadow-sm" style="background-color: ${bgBadge}; font-size: 0.75rem;">
                            ${v.estado_disponibilidad}
                        </span>
                        <img src="${imagen}" class="card-img-top" alt="${v.marca} ${v.modelo}" style="height: 180px; object-fit: cover;" onerror="this.src='${BASE_URL}/assets/vehiculos/default.png'">
                        <div class="card-body d-flex flex-column justify-content-between p-3">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-uppercase" style="color: #d9383a; font-size: 0.75rem;">${v.tipo_nombre}</small>
                                    <small style="color: #64748b;"><i class="fas fa-calendar-alt me-1" style="color: #94a3b8;"></i>${v.anio}</small>
                                </div>
                                <h5 class="card-title fw-bold mb-1" style="color: #0f172a; font-size: 1.1rem;">${v.marca} ${v.modelo}</h5>
                                <p class="small mb-2" style="color: #64748b;"><i class="fas fa-id-card me-1" style="color: #94a3b8;"></i>Placa: <strong style="color: #334155;">${v.placa}</strong></p>
                                
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge border fw-semibold" style="background-color: #f8fafc; border-color: #e2e8f0 !important; color: #334155;"><i class="fas fa-palette me-1" style="color: #94a3b8;"></i>${v.color}</span>
                                    <span class="badge border fw-semibold" style="background-color: #f8fafc; border-color: #e2e8f0 !important; color: #334155;"><i class="fas fa-tachometer-alt me-1" style="color: #94a3b8;"></i>${v.kilometraje} km</span>
                                </div>
                            </div>

                            <div class="pt-2 border-top d-flex align-items-center justify-content-between" style="border-color: #f1f5f9 !important;">
                                <div>
                                    <span class="small d-block fw-medium" style="color: #64748b; font-size: 0.725rem;">Precio / Día</span>
                                    <span class="fs-5 fw-bold" style="color: #0f172a;">S/ ${precio}</span>
                                </div>
                                <button type="button" 
                                        class="btn text-white rounded-pill px-3 py-2 btn-sm fw-semibold btn-ver-detalles shadow-sm" 
                                        style="background-color: #d9383a; border: none; font-size: 0.85rem;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetallesVehiculo" 
                                        data-placa="${v.placa}">
                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    };

    // Evento Delegado para poblar los datos en el Modal
    if (gridProductos) {
        gridProductos.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-ver-detalles');
            if (!btn) return;

            const placa = btn.getAttribute('data-placa');
            const v = vehiculosData.find(item => item.placa === placa);

            if (v) {
                let imagen = `${BASE_URL}/assets/vehiculos/default.png`;
                if (v.imagen_url && v.imagen_url.trim() !== '') {
                    const urlLimpia = v.imagen_url.trim();
                    imagen = (urlLimpia.startsWith('http://') || urlLimpia.startsWith('https://')) 
                             ? urlLimpia 
                             : `${BASE_URL}/${urlLimpia.replace(/^\/+/, '')}`;
                }

                // Seteo de los elementos en el modal
                document.getElementById('det_imagen').src = imagen;
                document.getElementById('det_titulo').textContent = `${v.marca} ${v.modelo}`;
                document.getElementById('det_tipo').textContent = v.tipo_nombre || 'N/A';
                document.getElementById('det_placa').textContent = v.placa || '-';
                document.getElementById('det_anio').textContent = v.anio || '-';
                document.getElementById('det_color').textContent = v.color || '-';
                document.getElementById('det_km').textContent = `${v.kilometraje || 0} km`;
                document.getElementById('det_precio').textContent = `S/ ${parseFloat(v.precio_base_dia || 0).toFixed(2)}`;
                document.getElementById('det_observaciones').textContent = v.observaciones || 'Sin observaciones adicionales.';

                const badgeEstado = document.getElementById('det_estado');
                const esDisponible = (v.estado_disponibilidad || '').toUpperCase() === 'DISPONIBLE';
                badgeEstado.textContent = v.estado_disponibilidad;
                badgeEstado.className = 'badge px-3 py-2 rounded-pill text-white fw-bold';
                badgeEstado.style.backgroundColor = esDisponible ? '#10b981' : '#f59e0b';
            }
        });
    }

    if (inputBuscar) {
        inputBuscar.addEventListener('input', renderizarTarjetas);
    }

    if (contenedorFiltros) {
        contenedorFiltros.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-filtro-categoria');
            if (!btn) return;

            document.querySelectorAll('.btn-filtro-categoria').forEach(b => {
                b.classList.remove('active');
            });

            btn.classList.add('active');

            categoriaSeleccionada = btn.getAttribute('data-categoria');
            renderizarTarjetas();
        });
    }

    cargarVehiculos();
});