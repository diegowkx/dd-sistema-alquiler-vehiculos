<?php

declare(strict_types=1);

class Estadias {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(?string $estado = null): array {
        $sql = "
            SELECT e.id, e.reserva_id, e.cliente_doc, e.numero_habitacion, e.usuario_checkin, e.usuario_checkout,
                   e.fecha_hora_checkin, e.fecha_hora_checkout, e.precio_noche_pactado, e.estado,
                   e.created_at, e.updated_at,
                   c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   h.tipo_nombre AS habitacion_tipo,
                   COALESCE(SUM(ce.subtotal), 0) AS total_cargos
            FROM estadias e
            INNER JOIN clientes c ON e.cliente_doc = c.numero_documento
            INNER JOIN habitaciones h ON e.numero_habitacion = h.numero_habitacion
            LEFT JOIN cargos_estadia ce ON ce.estadia_id = e.id
        ";
        $params = [];
        if ($estado !== null && $estado !== '') {
            $sql .= " WHERE e.estado = ?";
            $params[] = $estado;
        }
        $sql .= " GROUP BY e.id ORDER BY e.fecha_hora_checkin DESC, e.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT e.*, c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                   h.tipo_nombre AS habitacion_tipo
            FROM estadias e
            INNER JOIN clientes c ON e.cliente_doc = c.numero_documento
            INNER JOIN habitaciones h ON e.numero_habitacion = h.numero_habitacion
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO estadias (reserva_id, cliente_doc, numero_habitacion, usuario_checkin,
                                   fecha_hora_checkin, precio_noche_pactado)
            VALUES (:reserva_id, :cliente_doc, :numero_habitacion, :usuario_checkin, NOW(), :precio_noche_pactado)
        ");
        $ok = $stmt->execute([
            ':reserva_id'           => $data['reserva_id'] ?: null,
            ':cliente_doc'          => $data['cliente_doc'],
            ':numero_habitacion'    => $data['numero_habitacion'],
            ':usuario_checkin'      => $data['usuario_checkin'],
            ':precio_noche_pactado' => $data['precio_noche_pactado'],
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function checkout(int $id, string $usuarioCheckout): bool {
        $stmt = $this->pdo->prepare("
            UPDATE estadias
            SET fecha_hora_checkout = NOW(), usuario_checkout = ?, estado = 'FINALIZADA'
            WHERE id = ? AND estado = 'EN_CURSO'
        ");
        return $stmt->execute([$usuarioCheckout, $id]);
    }

    public function anular(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE estadias SET estado = 'ANULADA' WHERE id = ? AND estado = 'EN_CURSO'");
        return $stmt->execute([$id]);
    }

    // --- Cargos extra ---

    public function getCargos(int $estadiaId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM cargos_estadia WHERE estadia_id = ? ORDER BY id ASC");
        $stmt->execute([$estadiaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCargo(int $estadiaId, string $concepto, int $cantidad, float $precioUnitario): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO cargos_estadia (estadia_id, concepto, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");
        $ok = $stmt->execute([$estadiaId, $concepto, $cantidad, $precioUnitario]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function deleteCargo(int $cargoId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cargos_estadia WHERE id = ?");
        return $stmt->execute([$cargoId]);
    }

    public function getTotalCargos(int $estadiaId): float {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM cargos_estadia WHERE estadia_id = ?");
        $stmt->execute([$estadiaId]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Estadías FINALIZADA sin comprobante emitido aún. Usado por el buscador del
     * módulo de Facturación (?c=facturacion&a=buscarAJAX&origen=estadia).
     * Excluye estadías que ya tienen un comprobante en comprobantes_pago.
     */
    public function buscarFinalizadasParaFacturar(string $termino, int $limite = 15): array {
        $like = '%' . $termino . '%';
        $limite = max(1, min($limite, 50));
        $stmt = $this->pdo->prepare("
            SELECT e.id, e.cliente_doc, c.razon_social AS cliente_nombre, c.tipo_documento AS cliente_tipo_doc,
                e.numero_habitacion, e.fecha_hora_checkin, e.fecha_hora_checkout, e.precio_noche_pactado
            FROM estadias e
            INNER JOIN clientes c ON e.cliente_doc = c.numero_documento
            LEFT JOIN comprobantes_pago cp ON cp.estadia_id = e.id
            WHERE e.estado = 'FINALIZADA'
            AND cp.id IS NULL
            AND (c.razon_social LIKE ? OR c.numero_documento LIKE ? OR e.numero_habitacion LIKE ?)
            ORDER BY e.fecha_hora_checkout DESC
            LIMIT " . $limite . "
        ");
        $stmt->execute([$like, $like, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}