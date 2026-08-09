<?php

declare(strict_types=1);

require_once __DIR__ . '/Contratos.php';

class Liquidaciones {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // -------------------------------------------------------------
    // Liquidación
    // -------------------------------------------------------------

    public function getByContratoId(int $contratoId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, contrato_id, usuario_cierre, fecha_hora_devolucion_real,
                   kilometraje_llegada, responsable_retraso, observaciones_llegada,
                   firmado, created_at, updated_at
            FROM liquidaciones_contrato
            WHERE contrato_id = ?
        ");
        $stmt->execute([$contratoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea o actualiza el borrador de liquidación (mientras no esté firmada).
     */
    public function guardarBorrador(int $contratoId, array $data): bool {
        $existente = $this->getByContratoId($contratoId);

        if ($existente && $existente['firmado'] == 1) {
            throw new RuntimeException('La liquidación ya está firmada y no puede modificarse.');
        }

        if ($existente) {
            $stmt = $this->pdo->prepare("
                UPDATE liquidaciones_contrato
                SET usuario_cierre = :usuario_cierre,
                    fecha_hora_devolucion_real = :fecha_hora_devolucion_real,
                    kilometraje_llegada = :kilometraje_llegada,
                    responsable_retraso = :responsable_retraso,
                    observaciones_llegada = :observaciones_llegada
                WHERE contrato_id = :contrato_id AND firmado = 0
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO liquidaciones_contrato (
                    contrato_id, usuario_cierre, fecha_hora_devolucion_real,
                    kilometraje_llegada, responsable_retraso, observaciones_llegada
                ) VALUES (
                    :contrato_id, :usuario_cierre, :fecha_hora_devolucion_real,
                    :kilometraje_llegada, :responsable_retraso, :observaciones_llegada
                )
            ");
        }

        return $stmt->execute([
            ':contrato_id'                  => $contratoId,
            ':usuario_cierre'                => $data['usuario_cierre'],
            ':fecha_hora_devolucion_real'    => $data['fecha_hora_devolucion_real'],
            ':kilometraje_llegada'           => $data['kilometraje_llegada'],
            ':responsable_retraso'           => $data['responsable_retraso'],
            ':observaciones_llegada'         => $data['observaciones_llegada'] ?: null,
        ]);
    }

    /**
     * Firma la liquidación: bloquea todo, finaliza el contrato y libera/actualiza el vehículo.
     * Transacción para mantener consistencia entre liquidación, contrato y vehículo.
     */
    public function firmar(int $contratoId): bool {
        $this->pdo->beginTransaction();
        try {
            $liquidacion = $this->getByContratoId($contratoId);
            if (!$liquidacion || $liquidacion['firmado'] == 1) {
                $this->pdo->rollBack();
                return false;
            }

            $stmt = $this->pdo->prepare("
                UPDATE liquidaciones_contrato SET firmado = 1
                WHERE contrato_id = :contrato_id AND firmado = 0
            ");
            $stmt->execute([':contrato_id' => $contratoId]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }

            $stmtContrato = $this->pdo->prepare("
                UPDATE contratos_alquiler SET estado = 'FINALIZADO'
                WHERE id = :id AND estado = 'ACTIVO' AND firmado = 1
            ");
            $stmtContrato->execute([':id' => $contratoId]);

            $contratoStmt = $this->pdo->prepare("SELECT placa_vehiculo FROM contratos_alquiler WHERE id = ?");
            $contratoStmt->execute([$contratoId]);
            $placa = $contratoStmt->fetchColumn();

            $stmtVeh = $this->pdo->prepare("
                UPDATE vehiculos
                SET estado_disponibilidad = 'DISPONIBLE', kilometraje = :kilometraje
                WHERE placa = :placa
            ");
            $stmtVeh->execute([
                ':kilometraje' => $liquidacion['kilometraje_llegada'],
                ':placa'       => $placa,
            ]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // -------------------------------------------------------------
    // Cargos de alquiler
    // -------------------------------------------------------------

    public function getCargos(int $contratoId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, contrato_id, concepto, cantidad, precio_unitario, subtotal, created_at
            FROM cargos_alquiler
            WHERE contrato_id = ?
            ORDER BY created_at ASC
        ");
        $stmt->execute([$contratoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function liquidacionFirmada(int $contratoId): bool {
        $liq = $this->getByContratoId($contratoId);
        return $liq && $liq['firmado'] == 1;
    }

    public function agregarCargo(int $contratoId, array $data): int|false {
        if ($this->liquidacionFirmada($contratoId)) {
            throw new RuntimeException('La liquidación ya está firmada, no se pueden agregar cargos.');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO cargos_alquiler (contrato_id, concepto, cantidad, precio_unitario)
            VALUES (:contrato_id, :concepto, :cantidad, :precio_unitario)
        ");
        $ok = $stmt->execute([
            ':contrato_id'     => $contratoId,
            ':concepto'         => $data['concepto'],
            ':cantidad'         => $data['cantidad'],
            ':precio_unitario'  => $data['precio_unitario'],
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    public function eliminarCargo(int $cargoId): bool {
        $stmt = $this->pdo->prepare("SELECT contrato_id FROM cargos_alquiler WHERE id = ?");
        $stmt->execute([$cargoId]);
        $contratoId = $stmt->fetchColumn();

        if ($contratoId === false) {
            return false;
        }
        if ($this->liquidacionFirmada((int)$contratoId)) {
            throw new RuntimeException('La liquidación ya está firmada, no se pueden eliminar cargos.');
        }

        $stmt = $this->pdo->prepare("DELETE FROM cargos_alquiler WHERE id = ?");
        return $stmt->execute([$cargoId]);
    }

    public function totalCargos(int $contratoId): float {
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM cargos_alquiler WHERE contrato_id = ?");
        $stmt->execute([$contratoId]);
        return (float)$stmt->fetchColumn();
    }

    // -------------------------------------------------------------
    // Cálculo del monto final de la liquidación
    // -------------------------------------------------------------

    /**
     * Calcula el monto del alquiler propiamente (días/horas) más los cargos extra.
     *
     * Regla de responsable_retraso cuando la devolución real es posterior a la estimada:
     *  - 'EMPRESA': el retraso no se le cobra al cliente -> se calcula solo hasta la fecha estimada.
     *  - 'CLIENTE' o 'NINGUNO': se cobra según la fecha real completa (con prorrateo de horas extra).
     * Si el cliente devuelve antes o justo a tiempo, siempre se calcula con la fecha real.
     */
    public function calcularMontoLiquidacion(array $contrato, string $fechaDevolucionReal, string $responsableRetraso): array {
        $contratosModel = new Contratos($this->pdo);

        $fechaParaCalculo = $fechaDevolucionReal;

        $huboRetraso = (strtotime($fechaDevolucionReal) > strtotime($contrato['fecha_hora_devolucion_estimada']));
        if ($huboRetraso && $responsableRetraso === 'EMPRESA') {
            $fechaParaCalculo = $contrato['fecha_hora_devolucion_estimada'];
        }

        $calculoAlquiler = $contratosModel->calcularDiasYMonto(
            $contrato['fecha_hora_salida'],
            $fechaParaCalculo,
            (float)$contrato['tarifa_dia_pactada']
        );

        $totalCargos = $this->totalCargos((int)$contrato['id']);
        $totalFinal = round($calculoAlquiler['monto_total'] + $totalCargos, 2);

        return [
            'hubo_retraso'       => $huboRetraso,
            'calculo_alquiler'   => $calculoAlquiler,
            'total_cargos'       => round($totalCargos, 2),
            'total_final'        => $totalFinal,
        ];
    }
}