<?php

declare(strict_types=1);

class CargosEstadia {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getByEstadiaId(int $estadiaId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, estadia_id, concepto, cantidad, precio_unitario, subtotal, created_at, updated_at
            FROM cargos_estadia
            WHERE estadia_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$estadiaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, estadia_id, concepto, cantidad, precio_unitario, subtotal, created_at, updated_at
            FROM cargos_estadia WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta el cargo y devuelve el ID generado (int) o false si falla.
     */
    public function create(array $data): int|false {
        $stmt = $this->pdo->prepare("
            INSERT INTO cargos_estadia (estadia_id, concepto, cantidad, precio_unitario)
            VALUES (:estadia_id, :concepto, :cantidad, :precio_unitario)
        ");
        $executed = $stmt->execute([
            ':estadia_id'      => (int)$data['estadia_id'],
            ':concepto'        => trim((string)$data['concepto']),
            ':cantidad'        => (int)($data['cantidad'] ?? 1),
            ':precio_unitario' => (float)$data['precio_unitario'],
        ]);

        return $executed ? (int)$this->pdo->lastInsertId() : false;
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->pdo->prepare("
            UPDATE cargos_estadia
            SET concepto = :concepto, cantidad = :cantidad, precio_unitario = :precio_unitario
            WHERE id = :id
        ");
        return $stmt->execute([
            ':concepto'        => trim((string)$data['concepto']),
            ':cantidad'        => (int)($data['cantidad'] ?? 1),
            ':precio_unitario' => (float)$data['precio_unitario'],
            ':id'              => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM cargos_estadia WHERE id = ?");
        return $stmt->execute([$id]);
    }
}