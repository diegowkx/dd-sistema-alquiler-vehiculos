<?php

declare(strict_types=1);

class Contratos {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private const SELECT_BASE = "
        SELECT c.id, c.placa_vehiculo, c.cliente_doc, c.usuario_creacion,
               c.fecha_hora_salida, c.fecha_hora_devolucion_estimada,
               c.tarifa_dia_pactada, c.kilometraje_salida,
               c.estado, c.observaciones_salida, c.firmado,
               c.created_at, c.updated_at,
               v.marca, v.modelo, v.anio, v.color,
               cl.razon_social AS cliente_nombre, cl.tipo_documento AS cliente_tipo_doc
        FROM contratos_alquiler c
        INNER JOIN vehiculos v ON v.placa = c.placa_vehiculo
        INNER JOIN clientes cl ON cl.numero_documento = c.cliente_doc
    ";

    /**
     * ACTIVO = pestaña principal (firmados y no firmados, mientras no se haya finalizado/cancelado)
     */
    public function getActivos(): array {
        $stmt = $this->pdo->query(self::SELECT_BASE . " WHERE c.estado = 'ACTIVO' ORDER BY c.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFinalizados(): array {
        $stmt = $this->pdo->query(self::SELECT_BASE . " WHERE c.estado = 'FINALIZADO' ORDER BY c.updated_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCancelados(): array {
        $stmt = $this->pdo->query(self::SELECT_BASE . " WHERE c.estado = 'CANCELADO' ORDER BY c.updated_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare(self::SELECT_BASE . " WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cálculo de días y monto estimado/real entre dos fechas, con PRORRATEO de horas
     * (en vez de redondear siempre hacia arriba a un día completo extra).
     *
     * Regla:
     *  - Si la duración total es <= 24 horas, se cobra 1 día completo (mínimo de alquiler).
     *  - Si supera 24 horas, se cobran los días completos + la fracción proporcional
     *    de la tarifa diaria por las horas restantes (ej: 2 días y 6 horas = 2 + 6/24 días).
     *
     * @return array{horas_totales: float, dias_completos: int, horas_restantes: float, fraccion_dia: float, monto_total: float}
     */
    public function calcularDiasYMonto(string $fechaInicio, string $fechaFin, float $tarifaDia): array {
        $ini = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $horasTotales = ($fin->getTimestamp() - $ini->getTimestamp()) / 3600;

        if ($horasTotales <= 0) {
            throw new InvalidArgumentException('La fecha final debe ser posterior a la fecha inicial.');
        }

        if ($horasTotales <= 24) {
            $diasCompletos = 1;
            $horasRestantes = 0.0;
            $fraccionDia = 0.0;
        } else {
            $diasCompletos = (int) floor($horasTotales / 24);
            $horasRestantes = $horasTotales - ($diasCompletos * 24);
            $fraccionDia = $horasRestantes / 24;
        }

        $montoTotal = ($diasCompletos * $tarifaDia) + ($fraccionDia * $tarifaDia);

        return [
            'horas_totales'   => round($horasTotales, 2),
            'dias_completos'  => $diasCompletos,
            'horas_restantes' => round($horasRestantes, 2),
            'fraccion_dia'    => round($fraccionDia, 4),
            'monto_total'     => round($montoTotal, 2),
        ];
    }

    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO contratos_alquiler (
                placa_vehiculo, cliente_doc, usuario_creacion,
                fecha_hora_salida, fecha_hora_devolucion_estimada,
                tarifa_dia_pactada, kilometraje_salida, observaciones_salida
            ) VALUES (
                :placa_vehiculo, :cliente_doc, :usuario_creacion,
                :fecha_hora_salida, :fecha_hora_devolucion_estimada,
                :tarifa_dia_pactada, :kilometraje_salida, :observaciones_salida
            )
        ");

        $ok = $stmt->execute([
            ':placa_vehiculo'                 => $data['placa_vehiculo'],
            ':cliente_doc'                     => $data['cliente_doc'],
            ':usuario_creacion'                 => $data['usuario_creacion'],
            ':fecha_hora_salida'                => $data['fecha_hora_salida'],
            ':fecha_hora_devolucion_estimada'   => $data['fecha_hora_devolucion_estimada'],
            ':tarifa_dia_pactada'               => $data['tarifa_dia_pactada'],
            ':kilometraje_salida'               => $data['kilometraje_salida'],
            ':observaciones_salida'             => $data['observaciones_salida'] ?: null,
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Solo permitido mientras el contrato no esté firmado (se valida también en el controller).
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE contratos_alquiler
            SET placa_vehiculo = :placa_vehiculo,
                cliente_doc = :cliente_doc,
                fecha_hora_salida = :fecha_hora_salida,
                fecha_hora_devolucion_estimada = :fecha_hora_devolucion_estimada,
                tarifa_dia_pactada = :tarifa_dia_pactada,
                kilometraje_salida = :kilometraje_salida,
                observaciones_salida = :observaciones_salida
            WHERE id = :id AND firmado = 0 AND estado = 'ACTIVO'
        ");

        return $stmt->execute([
            ':placa_vehiculo'                 => $data['placa_vehiculo'],
            ':cliente_doc'                     => $data['cliente_doc'],
            ':fecha_hora_salida'                => $data['fecha_hora_salida'],
            ':fecha_hora_devolucion_estimada'   => $data['fecha_hora_devolucion_estimada'],
            ':tarifa_dia_pactada'               => $data['tarifa_dia_pactada'],
            ':kilometraje_salida'               => $data['kilometraje_salida'],
            ':observaciones_salida'             => $data['observaciones_salida'] ?: null,
            ':id'                                => $id,
        ]) && $stmt->rowCount() > 0;
    }

    /**
     * Firma el contrato: bloquea edición/cancelación y marca el vehículo como ALQUILADO.
     * Transacción para mantener consistencia entre contrato y vehículo.
     */
    public function firmar(int $id): bool {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE contratos_alquiler SET firmado = 1
                WHERE id = :id AND firmado = 0 AND estado = 'ACTIVO'
            ");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $contrato = $this->getById($id);

            $stmtVeh = $this->pdo->prepare("UPDATE vehiculos SET estado_disponibilidad = 'ALQUILADO' WHERE placa = ?");
            $stmtVeh->execute([$contrato['placa_vehiculo']]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cancela el contrato (solo si no está firmado). El vehículo no llegó a salir, así
     * que no requiere cambios de estado (nunca se marcó como ALQUILADO).
     */
    public function cancelar(int $id): bool {
        $stmt = $this->pdo->prepare("
            UPDATE contratos_alquiler SET estado = 'CANCELADO'
            WHERE id = :id AND firmado = 0 AND estado = 'ACTIVO'
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function existeContratoActivoParaVehiculo(string $placa): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM contratos_alquiler
            WHERE placa_vehiculo = ? AND estado = 'ACTIVO'
        ");
        $stmt->execute([$placa]);
        return (int)$stmt->fetchColumn() > 0;
    }
}