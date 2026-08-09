<?php

declare(strict_types=1);

class Dashboard {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Obtiene el total de contratos de alquiler e ingresos cobrados dentro de un rango de fechas.
     */
    public function getIngresosYContratosPorFecha(string $fechaInicio, string $fechaFin): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(c.id) AS total_contratos,
                IFNULL(SUM(cp.monto_total), 0.00) AS ingreso_total
            FROM contratos_alquiler c
            LEFT JOIN comprobantes_pago cp ON cp.contrato_id = c.id AND cp.estado_comprobante = 'EMITIDO'
            WHERE c.estado != 'CANCELADO'
              AND DATE(c.fecha_hora_salida) BETWEEN :fecha_inicio AND :fecha_fin
        ");

        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_contratos' => 0, 'ingreso_total' => '0.00'];
    }

    /**
     * Obtiene las próximas entregas y devoluciones programadas.
     */
    public function getOperacionesProximas(): array {
        $stmt = $this->pdo->query("
            SELECT 
                c.id AS numero_contrato,
                cli.razon_social AS cliente_nombre,
                CONCAT(v.marca, ' ', v.modelo) AS vehiculo,
                v.placa,
                'ENTREGA' AS tipo_operacion,
                c.fecha_hora_salida AS fecha_programada,
                c.estado
            FROM contratos_alquiler c
            INNER JOIN vehiculos v ON c.placa_vehiculo = v.placa
            INNER JOIN clientes cli ON c.cliente_doc = cli.numero_documento
            WHERE c.estado IN ('RESERVADO', 'ACTIVO')
            ORDER BY c.fecha_hora_salida ASC
            LIMIT 15
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el estado actual de disponibilidad de toda la flota de vehículos
     * junto con el cliente actual en caso de estar alquilado.
     */
    public function getEstadoActualFlota(): array {
        $stmt = $this->pdo->query("
            SELECT 
                v.placa, 
                v.marca,
                v.modelo,
                v.anio,
                v.color,
                v.tipo_nombre, 
                v.estado_disponibilidad,
                v.kilometraje,
                tv.precio_base_dia,
                cli.razon_social AS cliente_actual,
                c.fecha_hora_devolucion_estimada
            FROM vehiculos v
            INNER JOIN tipos_vehiculo tv ON v.tipo_nombre = tv.nombre
            LEFT JOIN contratos_alquiler c ON v.placa = c.placa_vehiculo AND c.estado = 'ACTIVO'
            LEFT JOIN clientes cli ON c.cliente_doc = cli.numero_documento
            WHERE v.activo = 1
            ORDER BY v.marca ASC, v.modelo ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los clientes con mayor número de alquileres y gasto acumulado.
     */
    public function getTopClientes(): array {
        $stmt = $this->pdo->query("
            SELECT 
                cli.razon_social AS cliente_nombre,
                COUNT(c.id) AS total_alquileres,
                IFNULL(SUM(cp.monto_total), 0.00) AS total_gastado
            FROM clientes cli
            INNER JOIN contratos_alquiler c ON cli.numero_documento = c.cliente_doc
            LEFT JOIN comprobantes_pago cp ON cp.contrato_id = c.id AND cp.estado_comprobante = 'EMITIDO'
            WHERE c.estado != 'CANCELADO'
            GROUP BY cli.numero_documento, cli.razon_social
            ORDER BY total_gastado DESC
            LIMIT 10
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra el acceso de un usuario con su IP en la tabla iphys.
     */
    public function registrarAccesoIp(string $username, string $rol, string $ip): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO iphys (usuario_username, rol_name, ip_address, fecha_acceso) 
            VALUES (:username, :rol, :ip, NOW())
        ");

        return $stmt->execute([
            ':username' => $username,
            ':rol'      => $rol,
            ':ip'       => $ip
        ]);
    }

    /**
     * Obtiene el historial completo de IPs registradas con el nombre del usuario.
     */
    public function getHistorialIps(): array {
        $stmt = $this->pdo->query("
            SELECT 
                i.id,
                i.usuario_username,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario_nombre_completo,
                i.rol_name,
                i.ip_address,
                i.fecha_acceso
            FROM iphys i
            INNER JOIN usuarios u ON i.usuario_username = u.username
            ORDER BY i.fecha_acceso DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el detalle específico de un registro de IP.
     */
    public function getIpDetalleById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT 
                i.id,
                i.usuario_username,
                CONCAT(u.nombres, ' ', u.apellidos) AS usuario_nombre_completo,
                u.correo,
                u.telefono,
                i.rol_name,
                i.ip_address,
                i.fecha_acceso
            FROM iphys i
            INNER JOIN usuarios u ON i.usuario_username = u.username
            WHERE i.id = ?
        ");

        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}