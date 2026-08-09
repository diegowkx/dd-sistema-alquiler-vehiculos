-- =======================================================
-- INSERTS DE DATOS INICIALES
-- =======================================================

-- 1. ROLES
INSERT INTO roles (nombre, activo) VALUES 
('ADMINISTRADOR', 1),
('ASESOR', 1);

-- 2. PERMISOS (Módulos del Sistema)
INSERT INTO permisos (nombre, descripcion) VALUES 
('DASHBOARD', 'Panel general de métricas y estadísticas'),
('ROLES', 'Gestión de roles y privilegios'),
('USUARIOS', 'Gestión de empleados y credenciales'),
('TIPOS_VEHICULOS', 'Gestion de los tipos de vehiculos'),
('VEHICULOS', 'Gestión de la flota vehicular y estados'),
('CLIENTES', 'Registro y administración de arrendatarios'),
('CONTRATOS', 'Gestión de salidas, retornos, liquidación y contratos'),
('FACTURACION', 'Emisión de comprobantes de pago, boletas y facturas'),
('CATALOGO', 'Módulo visual para mostrar los autos actuales y sus datos');

-- 3. ASIGNACIÓN DE PERMISOS A ROLES
-- El ADMINISTRADOR tiene acceso a TODO
INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES 
('ADMINISTRADOR', 'DASHBOARD'),
('ADMINISTRADOR', 'ROLES'),
('ADMINISTRADOR', 'USUARIOS'),
('ADMINISTRADOR', 'TIPOS_VEHICULOS'),
('ADMINISTRADOR', 'VEHICULOS'),
('ADMINISTRADOR', 'CLIENTES'),
('ADMINISTRADOR', 'CONTRATOS'),
('ADMINISTRADOR', 'FACTURACION'),
('ADMINISTRADOR', 'CATALOGO');


-- El ASESOR tiene acceso a la operación del día a día (Sin dashboard, ni roles, ni usuarios)
INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES 
('ASESOR', 'TIPOS_VEHICULOS'),
('ASESOR', 'VEHICULOS'),
('ASESOR', 'CLIENTES'),
('ASESOR', 'CONTRATOS'),
('ASESOR', 'FACTURACION'),
('ASESOR', 'CATALOGO');

-- 4. USUARIOS
-- Contraseña para ambos usuarios (clave): $2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG
INSERT INTO usuarios (documento_id, rol_name, username, nombres, apellidos, correo, telefono, password_hash, activo) VALUES 
('10000001', 'ADMINISTRADOR', 'admin', 'Carlos', 'Administrador', 'admin@gestcarcore.com', '987654321', '$2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG', 1),
('20000002', 'ASESOR', 'asesor', 'Ana', 'Ventas', 'asesor@gestcarcore.com', '912345678', '$2y$10$VNgHI7pLLJc8otgTE/MMX.vLCVejzBT4mWskGyQ5AhA5vj2LSTZHG', 1);

-- 5. TIPOS DE VEHÍCULOS
INSERT INTO tipos_vehiculo (nombre, descripcion, precio_base_dia, activo) VALUES 
('Sedán', 'Vehículos compactos y cómodos para ciudad', 120.00, 1),
('SUV', 'Camionetas amplias ideales para viajes familiares y terrenos mixtos', 200.00, 1),
('Pickup', 'Camionetas 4x4 de carga y trabajo pesado', 250.00, 1);

-- 6. VEHÍCULOS (Catálogo inicial)
INSERT INTO vehiculos (placa, tipo_nombre, marca, modelo, anio, color, kilometraje, estado_disponibilidad, imagen_url, observaciones, activo) VALUES 
('ABC-123', 'Sedán', 'Toyota', 'Corolla', 2023, 'Blanco', 15000, 'DISPONIBLE', 'assets/vehiculos/corolla.png', 'Vehículo en excelente estado', 1),
('XYZ-789', 'SUV', 'Hyundai', 'Tucson', 2024, 'Gris Plata', 8000, 'DISPONIBLE', 'assets/vehiculos/tucson.png', 'Nuevo modelo con pantalla táctil', 1),
('MNO-456', 'Pickup', 'Toyota', 'Hilux', 2022, 'Rojo', 45000, 'DISPONIBLE', 'assets/vehiculos/hilux.png', 'Uso moderado, llantas nuevas', 1);

-- 7. CLIENTES (Arrendatarios de prueba)
INSERT INTO clientes (tipo_documento, numero_documento, razon_social, licencia_conducir, correo, telefono, direccion, pais, activo) VALUES 
('DNI', '45678912', 'Juan Pérez Gómez', 'Q45678912-A', 'juan.perez@gmail.com', '955443322', 'Av. Larco 456, Miraflores', 'Perú', 1),
('RUC', '20601234567', 'Constructora e Inmobiliaria S.A.C.', 'B98765432-Z', 'contacto@constructorasac.com', '014445566', 'Calle Los Pinos 789, San Isidro', 'Perú', 1);

-- 8. CORRELATIVOS DE SERIES (SUNAT)
INSERT INTO correlativos_series (tipo_comprobante, serie, ultimo_numero, limite_numero, activo) VALUES 
('BOLETA', 'B001', 0, 99999999, 1),
('FACTURA', 'F001', 0, 99999999, 1);