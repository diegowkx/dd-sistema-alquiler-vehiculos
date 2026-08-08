<?php

declare(strict_types=1);

class Comprobantes {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(?string $estado = null, ?string $tipoComprobante = null): array {
        $sql = "
            SELECT cp.id, cp.tipo_origen, cp.reserva_id, cp.estadia_id, cp.usuario_emision,
                   cp.cliente_tipo_doc, cp.cliente_num_doc, cp.cliente_razon_social,
                   cp.tipo_comprobante, cp.serie, cp.numero, cp.fecha_emision, cp.estado_comprobante,
                   cp.subtotal_hospedaje, cp.total_cargos_extra, cp.descuento, cp.monto_adelanto_aplicado,
                   cp.op_gravadas, cp.igv, cp.monto_total, cp.metodo_pago, cp.observaciones,
                   COALESCE(e.numero_habitacion, r.numero_habitacion) AS numero_habitacion
            FROM comprobantes_pago cp
            LEFT JOIN estadias e ON cp.estadia_id = e.id
            LEFT JOIN reservas r ON cp.reserva_id = r.id
        ";
        $conds = [];
        $params = [];
        if ($estado !== null && $estado !== '') {
            $conds[] = "cp.estado_comprobante = ?";
            $params[] = $estado;
        }
        if ($tipoComprobante !== null && $tipoComprobante !== '') {
            $conds[] = "cp.tipo_comprobante = ?";
            $params[] = $tipoComprobante;
        }
        if ($conds) {
            $sql .= " WHERE " . implode(' AND ', $conds);
        }
        $sql .= " ORDER BY cp.fecha_emision DESC, cp.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT cp.*, COALESCE(e.numero_habitacion, r.numero_habitacion) AS numero_habitacion,
                   e.fecha_hora_checkin, e.fecha_hora_checkout, e.precio_noche_pactado
            FROM comprobantes_pago cp
            LEFT JOIN estadias e ON cp.estadia_id = e.id
            LEFT JOIN reservas r ON cp.reserva_id = r.id
            WHERE cp.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Reserva (bloquea con FOR UPDATE) y consume el siguiente número de una
     * serie activa para el tipo de comprobante indicado. Debe llamarse dentro
     * de una transacción ya abierta por el controller, junto al INSERT en
     * comprobantes_pago, para que la numeración quede atómica.
     */
    public function obtenerYReservarNumero(string $tipoComprobante): array {
        $stmt = $this->pdo->prepare("
            SELECT id, serie, ultimo_numero, limite_numero
            FROM correlativos_series
            WHERE tipo_comprobante = ? AND activo = 1
            ORDER BY id ASC
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$tipoComprobante]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            throw new RuntimeException("No hay una serie activa configurada para {$tipoComprobante}.");
        }

        $nuevoNumero = (int)$fila['ultimo_numero'] + 1;
        if ($nuevoNumero > (int)$fila['limite_numero']) {
            throw new RuntimeException("La serie {$fila['serie']} alcanzó su límite de numeración.");
        }

        $upd = $this->pdo->prepare("UPDATE correlativos_series SET ultimo_numero = ? WHERE id = ?");
        $upd->execute([$nuevoNumero, $fila['id']]);

        return [
            'serie'  => $fila['serie'],
            'numero' => str_pad((string)$nuevoNumero, 8, '0', STR_PAD_LEFT),
        ];
    }

    public function existeComprobanteParaEstadia(int $estadiaId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM comprobantes_pago WHERE estadia_id = ?");
        $stmt->execute([$estadiaId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existeAdelantoFacturadoParaReserva(int $reservaId): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM comprobantes_pago
            WHERE reserva_id = ? AND tipo_origen = 'RESERVA' AND estado_comprobante = 'EMITIDO'
        ");
        $stmt->execute([$reservaId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO comprobantes_pago (
                tipo_origen, reserva_id, estadia_id, usuario_emision,
                cliente_tipo_doc, cliente_num_doc, cliente_razon_social, cliente_direccion,
                tipo_comprobante, serie, numero,
                subtotal_hospedaje, total_cargos_extra, descuento, monto_adelanto_aplicado,
                op_gravadas, igv, monto_total,
                metodo_pago, observaciones
            ) VALUES (
                :tipo_origen, :reserva_id, :estadia_id, :usuario_emision,
                :cliente_tipo_doc, :cliente_num_doc, :cliente_razon_social, :cliente_direccion,
                :tipo_comprobante, :serie, :numero,
                :subtotal_hospedaje, :total_cargos_extra, :descuento, :monto_adelanto_aplicado,
                :op_gravadas, :igv, :monto_total,
                :metodo_pago, :observaciones
            )
        ");
        $ok = $stmt->execute([
            ':tipo_origen'             => $data['tipo_origen'],
            ':reserva_id'              => $data['reserva_id'] ?: null,
            ':estadia_id'              => $data['estadia_id'] ?: null,
            ':usuario_emision'         => $data['usuario_emision'],
            ':cliente_tipo_doc'        => $data['cliente_tipo_doc'],
            ':cliente_num_doc'         => $data['cliente_num_doc'],
            ':cliente_razon_social'    => $data['cliente_razon_social'],
            ':cliente_direccion'       => $data['cliente_direccion'] ?: null,
            ':tipo_comprobante'        => $data['tipo_comprobante'],
            ':serie'                   => $data['serie'],
            ':numero'                  => $data['numero'],
            ':subtotal_hospedaje'      => $data['subtotal_hospedaje'],
            ':total_cargos_extra'      => $data['total_cargos_extra'],
            ':descuento'               => $data['descuento'],
            ':monto_adelanto_aplicado' => $data['monto_adelanto_aplicado'],
            ':op_gravadas'             => $data['op_gravadas'],
            ':igv'                     => $data['igv'],
            ':monto_total'             => $data['monto_total'],
            ':metodo_pago'             => $data['metodo_pago'],
            ':observaciones'           => $data['observaciones'] !== '' ? $data['observaciones'] : null,
        ]);
        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function anular(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE comprobantes_pago SET estado_comprobante = 'ANULADO'
            WHERE id = ? AND estado_comprobante = 'EMITIDO'
        ");
        return $stmt->execute([$id]);
    }
}