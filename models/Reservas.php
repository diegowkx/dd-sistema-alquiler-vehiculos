<?php

declare(strict_types=1);

class Reservas {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(?string $estado = null): array {
        $sql = "
            SELECT r.id, r.cliente_doc, r.numero_habitacion, r.usuario_username,
                   r.fecha_checkin_prevista, r.fecha_checkout_prevista, r.monto_adelanto,
                   r.estado, r.observaciones, r.created_at, r.updated_at,
                   c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   h.tipo_nombre AS habitacion_tipo, h.piso AS habitacion_piso,
                   t.precio_base_noche
            FROM reservas r
            INNER JOIN clientes c ON r.cliente_doc = c.numero_documento
            INNER JOIN habitaciones h ON r.numero_habitacion = h.numero_habitacion
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
        ";
        $params = [];
        if ($estado !== null && $estado !== '') {
            $sql .= " WHERE r.estado = ?";
            $params[] = $estado;
        }
        $sql .= " ORDER BY r.fecha_checkin_prevista DESC, r.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.cliente_doc, r.numero_habitacion, r.usuario_username,
                   r.fecha_checkin_prevista, r.fecha_checkout_prevista, r.monto_adelanto,
                   r.estado, r.observaciones, r.created_at, r.updated_at,
                   c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   h.tipo_nombre AS habitacion_tipo
            FROM reservas r
            INNER JOIN clientes c ON r.cliente_doc = c.numero_documento
            INNER JOIN habitaciones h ON r.numero_habitacion = h.numero_habitacion
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * true si la habitación ya tiene una reserva CONFIRMADA/CHECKED_IN cuyo rango
     * de fechas se cruza con el indicado. $excludeId permite ignorar la propia
     * reserva al editar.
     */
    public function existeSolapamiento(string $numeroHabitacion, string $checkin, string $checkout, ?int $excludeId = null): bool {
        $sql = "
            SELECT COUNT(*) FROM reservas
            WHERE numero_habitacion = ?
              AND estado IN ('CONFIRMADA', 'CHECKED_IN')
              AND fecha_checkin_prevista < ?
              AND fecha_checkout_prevista > ?
        ";
        $params = [$numeroHabitacion, $checkout, $checkin];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Buscador para el modal de Check-In de Estadías (?c=estadias&a=buscarReservasAJAX).
     * Filtra reservas CONFIRMADA por nombre/documento de cliente o número de
     * habitación, para poder distinguir cuál reserva es cuando un mismo cliente
     * tiene varias reservas activas.
     */
    public function buscarConfirmadasParaCheckin(string $termino): array {
        $like = '%' . $termino . '%';
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.cliente_doc, r.numero_habitacion, r.fecha_checkin_prevista,
                   r.fecha_checkout_prevista, r.monto_adelanto,
                   c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   h.tipo_nombre AS habitacion_tipo, t.precio_base_noche
            FROM reservas r
            INNER JOIN clientes c ON r.cliente_doc = c.numero_documento
            INNER JOIN habitaciones h ON r.numero_habitacion = h.numero_habitacion
            INNER JOIN tipos_habitacion t ON h.tipo_nombre = t.nombre
            WHERE r.estado = 'CONFIRMADA'
              AND (c.razon_social LIKE ? OR c.numero_documento LIKE ? OR r.numero_habitacion LIKE ?)
            ORDER BY r.fecha_checkin_prevista ASC
            LIMIT 15
        ");
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservas (cliente_doc, numero_habitacion, usuario_username, fecha_checkin_prevista,
                                   fecha_checkout_prevista, monto_adelanto, observaciones)
            VALUES (:cliente_doc, :numero_habitacion, :usuario_username, :fecha_checkin_prevista,
                    :fecha_checkout_prevista, :monto_adelanto, :observaciones)
        ");
        $ok = $stmt->execute([
            ':cliente_doc'             => $data['cliente_doc'],
            ':numero_habitacion'       => $data['numero_habitacion'],
            ':usuario_username'        => $data['usuario_username'],
            ':fecha_checkin_prevista'  => $data['fecha_checkin_prevista'],
            ':fecha_checkout_prevista' => $data['fecha_checkout_prevista'],
            ':monto_adelanto'          => $data['monto_adelanto'] ?? 0,
            ':observaciones'           => $data['observaciones'] !== '' ? $data['observaciones'] : null,
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Solo permite editar fechas, adelanto y observaciones. El cliente y la
     * habitación no se reasignan aquí (habría que cancelar y crear una nueva);
     * el estado se maneja con updateEstado().
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE reservas
            SET fecha_checkin_prevista = :fecha_checkin_prevista,
                fecha_checkout_prevista = :fecha_checkout_prevista,
                monto_adelanto = :monto_adelanto,
                observaciones = :observaciones
            WHERE id = :id
        ");
        return $stmt->execute([
            ':fecha_checkin_prevista'  => $data['fecha_checkin_prevista'],
            ':fecha_checkout_prevista' => $data['fecha_checkout_prevista'],
            ':monto_adelanto'          => $data['monto_adelanto'] ?? 0,
            ':observaciones'           => $data['observaciones'] !== '' ? $data['observaciones'] : null,
            ':id'                      => $id,
        ]);
    }

    public function updateEstado(int $id, string $nuevoEstado): bool {
        $stmt = $this->pdo->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
        return $stmt->execute([$nuevoEstado, $id]);
    }

    /**
     * Reservas CONFIRMADA/CHECKED_IN con adelanto pendiente de facturar. Usado por
     * el buscador del módulo de Facturación (?c=facturacion&a=buscarAJAX&origen=reserva).
     * Excluye reservas cuyo adelanto ya tiene un comprobante EMITIDO.
     */
    public function buscarConAdelantoParaFacturar(string $termino, int $limite = 15): array {
        $like = '%' . $termino . '%';
        $limite = max(1, min($limite, 50));
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.cliente_doc, c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   r.numero_habitacion, r.monto_adelanto, r.fecha_checkin_prevista, r.fecha_checkout_prevista, r.estado
            FROM reservas r
            INNER JOIN clientes c ON r.cliente_doc = c.numero_documento
            LEFT JOIN comprobantes_pago cp
                   ON cp.reserva_id = r.id AND cp.tipo_origen = 'RESERVA' AND cp.estado_comprobante = 'EMITIDO'
            WHERE r.estado IN ('CONFIRMADA', 'CHECKED_IN')
              AND r.monto_adelanto > 0
              AND cp.id IS NULL
              AND (c.razon_social LIKE ? OR c.numero_documento LIKE ? OR r.numero_habitacion LIKE ?)
            ORDER BY r.fecha_checkin_prevista DESC
            LIMIT " . $limite . "
        ");
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}