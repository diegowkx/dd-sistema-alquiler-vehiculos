-- =======================================================
-- 1. ROLES Y PERMISOS (Acceso por Módulos / Sidebar)
-- =======================================================

-- 1. Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- 2. Permisos (Módulos del Sistema)
CREATE TABLE permisos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,         -- ej: 'VEHICULOS', 'CLIENTES', 'CONTRATOS', 'FACTURACION'
    descripcion VARCHAR(255)
);

-- 3. Asignación de Permisos a Roles
CREATE TABLE permisos_roles (
    rol_nombre VARCHAR(50) NOT NULL,
    permiso_nombre VARCHAR(50) NOT NULL,
    
    PRIMARY KEY (rol_nombre, permiso_nombre),
    FOREIGN KEY (rol_nombre) REFERENCES roles(nombre) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (permiso_nombre) REFERENCES permisos(nombre) ON DELETE CASCADE ON UPDATE CASCADE
);


-- =======================================================
-- 2. USUARIOS DEL SISTEMA E HISTORIAL
-- =======================================================

-- 4. Usuarios (Empleados)
CREATE TABLE usuarios (
    documento_id VARCHAR(15) PRIMARY KEY,
    rol_name VARCHAR(50) NOT NULL DEFAULT 'ASESOR',
    username VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15),
    password_hash VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    
    FOREIGN KEY (rol_name) REFERENCES roles(nombre) ON UPDATE CASCADE
);

-- 5. Historial de IPs
CREATE TABLE iphys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_username VARCHAR(20) NOT NULL,
    rol_name VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    fecha_acceso DATETIME DEFAULT CURRENT_TIMESTAMP(),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    
    FOREIGN KEY (usuario_username) REFERENCES usuarios(username) ON UPDATE CASCADE,
    FOREIGN KEY (rol_name) REFERENCES roles(nombre) ON UPDATE CASCADE
);


-- =======================================================
-- 3. GESTIÓN DE VEHÍCULOS
-- =======================================================

-- 6. Tipos / Categorías de Vehículo (SUV, Sedán, Pickup, etc.)
CREATE TABLE tipos_vehiculo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    precio_base_dia DECIMAL(10, 2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);

-- 7. Vehículos (Catálogo y Estado actual)
CREATE TABLE vehiculos (
    placa VARCHAR(10) PRIMARY KEY,
    tipo_nombre VARCHAR(50) NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio INT NOT NULL,
    color VARCHAR(30) NOT NULL,
    kilometraje INT NOT NULL DEFAULT 0,
    estado_disponibilidad ENUM('DISPONIBLE', 'ALQUILADO', 'MANTENIMIENTO') NOT NULL DEFAULT 'DISPONIBLE',
    imagen_url VARCHAR(255) NOT NULL DEFAULT 'assets/vehiculos/default.png',
    observaciones TEXT,
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (tipo_nombre) REFERENCES tipos_vehiculo(nombre) ON UPDATE CASCADE,
    INDEX idx_vehiculos_estado (estado_disponibilidad)
);

-- =======================================================
-- 4. CLIENTES
-- =======================================================

-- 8. Clientes registrados (Arrendatarios)
CREATE TABLE clientes (
    tipo_documento ENUM('DNI', 'PASAPORTE', 'CE', 'RUC') NOT NULL DEFAULT 'DNI',
    numero_documento VARCHAR(20) PRIMARY KEY,
    razon_social VARCHAR(150) NOT NULL,              -- Nombres y Apellidos o Razón Social empresa
    licencia_conducir VARCHAR(30) NULL,              -- Vital para alquiler de vehículos
    correo VARCHAR(100),
    telefono VARCHAR(20),
    direccion TEXT,
    pais VARCHAR(50) NOT NULL DEFAULT 'Perú',
    activo TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
);


-- =======================================================
-- 5. OPERACIONES: CONTRATOS, LIQUIDACIÓN Y FACTURACIÓN
-- =======================================================

CREATE TABLE contratos_alquiler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa_vehiculo VARCHAR(10) NOT NULL,
    cliente_doc VARCHAR(20) NOT NULL,
    usuario_creacion VARCHAR(20) NOT NULL,
    
    fecha_hora_salida DATETIME NOT NULL,
    fecha_hora_devolucion_estimada DATETIME NOT NULL,
    
    tarifa_dia_pactada DECIMAL(10, 2) NOT NULL,
    -- MONTO ADELANTO ELIMINADO
    
    kilometraje_salida INT NOT NULL,
    
    estado ENUM('ACTIVO', 'FINALIZADO', 'CANCELADO') NOT NULL DEFAULT 'ACTIVO',
    observaciones_salida TEXT,
    firmado TINYINT(1) NOT NULL DEFAULT 0,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (placa_vehiculo) REFERENCES vehiculos(placa) ON UPDATE CASCADE,
    FOREIGN KEY (cliente_doc) REFERENCES clientes(numero_documento) ON UPDATE CASCADE,
    FOREIGN KEY (usuario_creacion) REFERENCES usuarios(username) ON UPDATE CASCADE,
    INDEX idx_contratos_fechas (fecha_hora_salida, fecha_hora_devolucion_estimada)
);

-- 10. Liquidación de Contrato (Registro inmutable del cierre)
CREATE TABLE liquidaciones_contrato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL UNIQUE,
    usuario_cierre VARCHAR(20) NOT NULL,              -- Empleado que recibe el vehículo
    
    fecha_hora_devolucion_real DATETIME NOT NULL,
    kilometraje_llegada INT NOT NULL,
    responsable_retraso ENUM('NINGUNO', 'CLIENTE', 'EMPRESA') NOT NULL DEFAULT 'NINGUNO',
    observaciones_llegada TEXT,                       -- Daños nuevos, multas de limpieza, etc.
    firmado TINYINT(1) NOT NULL DEFAULT 0,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (contrato_id) REFERENCES contratos_alquiler(id) ON UPDATE CASCADE,
    FOREIGN KEY (usuario_cierre) REFERENCES usuarios(username) ON UPDATE CASCADE
);

-- 11. Cargos extras del alquiler (Daños, limpieza extrema, combustible faltante, etc.)
CREATE TABLE cargos_alquiler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    concepto VARCHAR(150) NOT NULL,                  -- ej: 'Multa por tapicería manchada con gaseosa'
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) AS (cantidad * precio_unitario) STORED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    FOREIGN KEY (contrato_id) REFERENCES contratos_alquiler(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 12. Correlativos de Series (Sunat / Control interno)
CREATE TABLE correlativos_series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_comprobante ENUM('BOLETA', 'FACTURA') NOT NULL,
    serie VARCHAR(4) NOT NULL,
    ultimo_numero INT NOT NULL DEFAULT 0,
    limite_numero INT NOT NULL DEFAULT 99999999,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    
    CONSTRAINT uq_tipo_serie UNIQUE (tipo_comprobante, serie)
);

-- 13. Comprobantes de Pago (Exclusivo para contratos de alquiler)
CREATE TABLE comprobantes_pago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    contrato_id INT NOT NULL,
    usuario_emision VARCHAR(20) NOT NULL,

    -- Emisor
    emisor_ruc VARCHAR(11) NOT NULL DEFAULT '20123456789',
    emisor_razon_social VARCHAR(150) NOT NULL DEFAULT 'RENT A CAR S.A.C.',
    emisor_direccion VARCHAR(255) NOT NULL DEFAULT 'Av. Principal 123, Lima, Perú',

    -- Receptor / Cliente
    cliente_tipo_doc ENUM('DNI', 'RUC', 'CE', 'PASAPORTE') NOT NULL DEFAULT 'DNI',
    cliente_num_doc VARCHAR(15) NOT NULL,
    cliente_razon_social VARCHAR(150) NOT NULL,
    cliente_direccion VARCHAR(255),

    -- Comprobante
    tipo_comprobante ENUM('BOLETA', 'FACTURA') NOT NULL DEFAULT 'BOLETA',
    serie VARCHAR(4) NOT NULL,
    numero VARCHAR(8) NOT NULL,
    fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP(),
    estado_comprobante ENUM('EMITIDO', 'ANULADO') NOT NULL DEFAULT 'EMITIDO',

    -- Importes Desglosados
    subtotal_alquiler DECIMAL(10, 2) NOT NULL,
    total_cargos_extra DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    
    op_gravadas DECIMAL(10, 2) NOT NULL,
    igv DECIMAL(10, 2) NOT NULL,
    monto_total DECIMAL(10, 2) NOT NULL,
    
    metodo_pago ENUM('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'MIXTO') NOT NULL DEFAULT 'EFECTIVO',
    observaciones VARCHAR(500),

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP(),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),

    -- Relaciones y Restricciones
    FOREIGN KEY (contrato_id) REFERENCES contratos_alquiler(id) ON UPDATE CASCADE,
    FOREIGN KEY (usuario_emision) REFERENCES usuarios(username) ON UPDATE CASCADE,
    
    CONSTRAINT uq_comprobante_contrato UNIQUE (contrato_id),
    CONSTRAINT uq_comprobante_serie_numero UNIQUE (serie, numero)
);