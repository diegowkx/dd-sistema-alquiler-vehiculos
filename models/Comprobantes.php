<?php

declare(strict_types=1);

require_once __DIR__ . '/Contratos.php';
require_once __DIR__ . '/Liquidaciones.php';

class Comprobantes {

    private PDO $pdo;
    private const IGV = 0.18;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Contratos FINALIZADOS (liquidación firmada) que todavía no tienen comprobante emitido.
     */
    public function getPendientes(): array {
        $stmt = $this->pdo->query("
            SELECT c.id, c.placa_vehiculo, c.cliente_doc, c.fecha_hora_salida,
                   c.fecha_hora_devolucion_estimada, c.tarifa_dia_pactada,
                   v.marca, v.modelo, v.anio,
                   cl.razon_social AS cliente_nombre, cl.tipo_documento AS cliente_tipo_doc,
                   l.fecha_hora_devolucion_real, l.responsable_retraso
            FROM contratos_alquiler c
            INNER JOIN vehiculos v ON v.placa = c.placa_vehiculo
            INNER JOIN clientes cl ON cl.numero_documento = c.cliente_doc
            INNER JOIN liquidaciones_contrato l ON l.contrato_id = c.id AND l.firmado = 1
            LEFT JOIN comprobantes_pago cp ON cp.contrato_id = c.id
            WHERE c.estado = 'FINALIZADO' AND cp.id IS NULL
            ORDER BY l.updated_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendientesPorCliente(string $clienteDoc): array {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.placa_vehiculo, c.cliente_doc, c.fecha_hora_salida,
                   c.fecha_hora_devolucion_estimada, c.tarifa_dia_pactada,
                   v.marca, v.modelo, v.anio,
                   cl.razon_social AS cliente_nombre, cl.tipo_documento AS cliente_tipo_doc,
                   l.fecha_hora_devolucion_real, l.responsable_retraso
            FROM contratos_alquiler c
            INNER JOIN vehiculos v ON v.placa = c.placa_vehiculo
            INNER JOIN clientes cl ON cl.numero_documento = c.cliente_doc
            INNER JOIN liquidaciones_contrato l ON l.contrato_id = c.id AND l.firmado = 1
            LEFT JOIN comprobantes_pago cp ON cp.contrato_id = c.id
            WHERE c.estado = 'FINALIZADO' AND cp.id IS NULL AND c.cliente_doc = ?
            ORDER BY l.updated_at DESC
        ");
        $stmt->execute([$clienteDoc]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getContratoPendientePorId(int $contratoId): array|false {
        $stmt = $this->pdo->prepare("
            SELECT c.id, c.placa_vehiculo, c.cliente_doc, c.fecha_hora_salida,
                   c.fecha_hora_devolucion_estimada, c.tarifa_dia_pactada,
                   v.marca, v.modelo, v.anio,
                   cl.razon_social AS cliente_nombre, cl.tipo_documento AS cliente_tipo_doc,
                   cl.direccion AS cliente_direccion,
                   l.fecha_hora_devolucion_real, l.responsable_retraso
            FROM contratos_alquiler c
            INNER JOIN vehiculos v ON v.placa = c.placa_vehiculo
            INNER JOIN clientes cl ON cl.numero_documento = c.cliente_doc
            INNER JOIN liquidaciones_contrato l ON l.contrato_id = c.id AND l.firmado = 1
            LEFT JOIN comprobantes_pago cp ON cp.contrato_id = c.id
            WHERE c.estado = 'FINALIZADO' AND cp.id IS NULL AND c.id = ?
        ");
        $stmt->execute([$contratoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Monto que debe pagar el cliente por ese contrato (alquiler + cargos), sin descuento.
     */
    public function montoAPagar(array $contratoPendiente): float {
        $liquidacionesModel = new Liquidaciones($this->pdo);
        $calculo = $liquidacionesModel->calcularMontoLiquidacion(
            $contratoPendiente,
            $contratoPendiente['fecha_hora_devolucion_real'],
            $contratoPendiente['responsable_retraso']
        );
        return $calculo['total_final'];
    }

    public function getComprobantes(): array {
        $stmt = $this->pdo->query("
            SELECT id, contrato_id, tipo_comprobante, serie, numero, fecha_emision,
                   estado_comprobante, cliente_num_doc, cliente_razon_social,
                   subtotal_alquiler, total_cargos_extra, descuento, op_gravadas, igv, monto_total,
                   metodo_pago
            FROM comprobantes_pago
            ORDER BY fecha_emision DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM comprobantes_pago WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByContratoId(int $contratoId): array|false {
        $stmt = $this->pdo->prepare("SELECT * FROM comprobantes_pago WHERE contrato_id = ?");
        $stmt->execute([$contratoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la serie activa por defecto para un tipo de comprobante.
     */
    private function serieActivaPorDefecto(string $tipoComprobante): ?string {
        $stmt = $this->pdo->prepare("
            SELECT serie FROM correlativos_series
            WHERE tipo_comprobante = ? AND activo = 1
            ORDER BY id ASC LIMIT 1
        ");
        $stmt->execute([$tipoComprobante]);
        $serie = $stmt->fetchColumn();
        return $serie !== false ? $serie : null;
    }

    /**
     * Genera el comprobante de forma completa y segura ante concurrencia:
     * bloquea la fila de correlativos_series (FOR UPDATE), obtiene el siguiente número,
     * calcula el desglose de IGV y registra el comprobante en una sola transacción.
     */
    public function generar(array $data): int {
        $this->pdo->beginTransaction();
        try {
            $tipoComprobante = $data['tipo_comprobante'];
            $serie = $data['serie'] ?? $this->serieActivaPorDefecto($tipoComprobante);

            if (!$serie) {
                throw new RuntimeException("No hay una serie activa configurada para {$tipoComprobante}.");
            }

            $stmtSerie = $this->pdo->prepare("
                SELECT id, ultimo_numero, limite_numero FROM correlativos_series
                WHERE tipo_comprobante = :tipo AND serie = :serie AND activo = 1
                FOR UPDATE
            ");
            $stmtSerie->execute([':tipo' => $tipoComprobante, ':serie' => $serie]);
            $serieRow = $stmtSerie->fetch(PDO::FETCH_ASSOC);

            if (!$serieRow) {
                throw new RuntimeException('La serie indicada no existe o no está activa.');
            }

            $siguienteNumero = (int)$serieRow['ultimo_numero'] + 1;
            if ($siguienteNumero > (int)$serieRow['limite_numero']) {
                throw new RuntimeException('Se alcanzó el límite de numeración de la serie.');
            }

            $stmtUpdate = $this->pdo->prepare("UPDATE correlativos_series SET ultimo_numero = :n WHERE id = :id");
            $stmtUpdate->execute([':n' => $siguienteNumero, ':id' => $serieRow['id']]);

            $numeroFormateado = str_pad((string)$siguienteNumero, 8, '0', STR_PAD_LEFT);

            // Desglose de IGV (el monto final ya incluye IGV, estilo boleta/factura de consumo)
            $montoFinal = round($data['monto_a_pagar'] - $data['descuento'], 2);
            if ($montoFinal <= 0) {
                throw new RuntimeException('El monto a pagar después del descuento debe ser mayor a 0.');
            }
            $opGravadas = round($montoFinal / (1 + self::IGV), 2);
            $igv = round($montoFinal - $opGravadas, 2);

            $stmt = $this->pdo->prepare("
                INSERT INTO comprobantes_pago (
                    contrato_id, usuario_emision,
                    cliente_tipo_doc, cliente_num_doc, cliente_razon_social, cliente_direccion,
                    tipo_comprobante, serie, numero,
                    subtotal_alquiler, total_cargos_extra, descuento,
                    op_gravadas, igv, monto_total, metodo_pago, observaciones
                ) VALUES (
                    :contrato_id, :usuario_emision,
                    :cliente_tipo_doc, :cliente_num_doc, :cliente_razon_social, :cliente_direccion,
                    :tipo_comprobante, :serie, :numero,
                    :subtotal_alquiler, :total_cargos_extra, :descuento,
                    :op_gravadas, :igv, :monto_total, :metodo_pago, :observaciones
                )
            ");

            $stmt->execute([
                ':contrato_id'          => $data['contrato_id'],
                ':usuario_emision'       => $data['usuario_emision'],
                ':cliente_tipo_doc'      => $data['cliente_tipo_doc'],
                ':cliente_num_doc'       => $data['cliente_num_doc'],
                ':cliente_razon_social'  => $data['cliente_razon_social'],
                ':cliente_direccion'     => $data['cliente_direccion'] ?: null,
                ':tipo_comprobante'      => $tipoComprobante,
                ':serie'                 => $serie,
                ':numero'                => $numeroFormateado,
                ':subtotal_alquiler'     => $data['subtotal_alquiler'],
                ':total_cargos_extra'    => $data['total_cargos_extra'],
                ':descuento'             => $data['descuento'],
                ':op_gravadas'           => $opGravadas,
                ':igv'                   => $igv,
                ':monto_total'           => $montoFinal,
                ':metodo_pago'           => $data['metodo_pago'],
                ':observaciones'         => $data['observaciones'] ?: null,
            ]);

            $id = (int)$this->pdo->lastInsertId();
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}