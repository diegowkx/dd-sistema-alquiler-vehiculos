<?php

declare(strict_types=1);

class Dashboard {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getIngresosYEstadiasPorFecha(string $fechaInicio, string $fechaFin): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(e.id) AS total_estadias,
                IFNULL(SUM(cp.monto_total), 0.00) AS ingreso_total
            FROM estadias e
            LEFT JOIN comprobantes_pago cp ON cp.estadia_id = e.id AND cp.estado_comprobante = 'EMITIDO'
            WHERE e.estado = 'FINALIZADA'
              AND DATE(e.fecha_hora_checkout) BETWEEN :fecha_inicio AND :fecha_fin
        ");

        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_estadias' => 0, 'ingreso_total' => '0.00'];
    }

    public function getOcupacionActualHabitaciones(): array {
        $stmt = $this->pdo->query("
            SELECT 
                h.numero_habitacion, 
                h.tipo_nombre, 
                h.piso, 
                h.estado_disponibilidad,
                t.precio_base_noche,
                c.razon_social AS cliente_actual
            FROM habitaciones h
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
            LEFT JOIN estadias e ON h.numero_habitacion = e.numero_habitacion AND e.estado = 'EN_CURSO'
            LEFT JOIN clientes c ON e.cliente_doc = c.numero_documento
            WHERE h.activo = 1
            ORDER BY h.piso ASC, h.numero_habitacion ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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