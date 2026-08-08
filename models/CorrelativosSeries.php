<?php

declare(strict_types=1);

class CorrelativosSeries {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT id, tipo_comprobante, serie, ultimo_numero, limite_numero, activo
            FROM correlativos_series WHERE activo = 1 ORDER BY tipo_comprobante ASC, serie ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT id, tipo_comprobante, serie, ultimo_numero, limite_numero, activo
            FROM correlativos_series WHERE activo = 0 ORDER BY tipo_comprobante ASC, serie ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTipoYSerie(string $tipoComprobante, string $serie): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, tipo_comprobante, serie, ultimo_numero, limite_numero, activo
            FROM correlativos_series
            WHERE tipo_comprobante = ? AND serie = ? AND activo = 1
        ");
        $stmt->execute([$tipoComprobante, $serie]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerSeriePredeterminada(string $tipoComprobante, ?string $serie = null): string {
        if (!empty($serie)) {
            return strtoupper(trim($serie));
        }

        return (strtoupper($tipoComprobante) === 'FACTURA') ? 'F001' : 'B001';
    }

    public function getSiguienteCorrelativo(string $tipoComprobante, ?string $serie = null): string|false {
        $serieFinal = $this->obtenerSeriePredeterminada($tipoComprobante, $serie);
        $registro = $this->getByTipoYSerie($tipoComprobante, $serieFinal);

        if (!$registro) {
            return false;
        }

        $siguiente = (int)$registro['ultimo_numero'] + 1;
        if ($siguiente > (int)$registro['limite_numero']) {
            return false;
        }

        return str_pad((string)$siguiente, 8, '0', STR_PAD_LEFT);
    }

    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO correlativos_series (tipo_comprobante, serie, ultimo_numero, limite_numero)
            VALUES (:tipo_comprobante, :serie, :ultimo_numero, :limite_numero)
        ");
        $executed = $stmt->execute([
            ':tipo_comprobante' => strtoupper(trim((string)$data['tipo_comprobante'])),
            ':serie'            => strtoupper(trim((string)$data['serie'])),
            ':ultimo_numero'    => (int)($data['ultimo_numero'] ?? 0),
            ':limite_numero'    => (int)($data['limite_numero'] ?? 99999999),
        ]);

        return $executed ? (int)$this->pdo->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE correlativos_series
            SET ultimo_numero = :ultimo_numero, limite_numero = :limite_numero
            WHERE id = :id
        ");
        return $stmt->execute([
            ':ultimo_numero' => (int)$data['ultimo_numero'],
            ':limite_numero' => (int)$data['limite_numero'],
            ':id'            => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE correlativos_series SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE correlativos_series SET activo = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}