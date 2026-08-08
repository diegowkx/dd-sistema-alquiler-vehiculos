<?php

declare(strict_types=1);

class TiposHabitacion {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT id, nombre, descripcion, precio_base_noche, capacidad_personas, activo, created_at, updated_at
            FROM tipos_habitacion WHERE activo = 1 ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT id, nombre, descripcion, precio_base_noche, capacidad_personas, activo, created_at, updated_at
            FROM tipos_habitacion WHERE activo = 0 ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, descripcion, precio_base_noche, capacidad_personas, activo, created_at, updated_at
            FROM tipos_habitacion WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByNombre(string $nombre): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, descripcion, precio_base_noche, capacidad_personas, activo, created_at, updated_at
            FROM tipos_habitacion WHERE nombre = ?
        ");
        $stmt->execute([trim($nombre)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO tipos_habitacion (nombre, descripcion, precio_base_noche, capacidad_personas)
            VALUES (:nombre, :descripcion, :precio_base_noche, :capacidad_personas)
        ");
        return $stmt->execute([
            ':nombre'             => trim((string)$data['nombre']),
            ':descripcion'        => !empty($data['descripcion']) ? trim((string)$data['descripcion']) : null,
            ':precio_base_noche'  => (float)$data['precio_base_noche'],
            ':capacidad_personas' => (int)($data['capacidad_personas'] ?? 1),
        ]);
    }

    public function update(int $id, array $data): bool {
        $this->pdo->beginTransaction();

        try {
            // 1. Obtener el nombre anterior para verificar si cambió
            $tipoAnterior = $this->getById($id);
            if (!$tipoAnterior) {
                $this->pdo->rollBack();
                return false;
            }

            $nuevoNombre = trim((string)$data['nombre']);
            $nombreAnterior = $tipoAnterior['nombre'];

            // 2. Actualizar tipos_habitacion
            $stmt = $this->pdo->prepare("
                UPDATE tipos_habitacion
                SET nombre = :nombre, 
                    descripcion = :descripcion,
                    precio_base_noche = :precio_base_noche, 
                    capacidad_personas = :capacidad_personas
                WHERE id = :id
            ");

            $resultado = $stmt->execute([
                ':nombre'             => $nuevoNombre,
                ':descripcion'        => !empty($data['descripcion']) ? trim((string)$data['descripcion']) : null,
                ':precio_base_noche'  => (float)$data['precio_base_noche'],
                ':capacidad_personas' => (int)($data['capacidad_personas'] ?? 1),
                ':id'                 => $id,
            ]);

            // 3. Si el nombre cambió y la actualización fue exitosa, actualizar la FK por nombre en habitaciones
            if ($resultado && $nombreAnterior !== $nuevoNombre) {
                $stmtHab = $this->pdo->prepare("
                    UPDATE habitaciones 
                    SET tipo_nombre = :nuevo_nombre 
                    WHERE tipo_nombre = :nombre_anterior
                ");
                $stmtHab->execute([
                    ':nuevo_nombre'    => $nuevoNombre,
                    ':nombre_anterior' => $nombreAnterior
                ]);
            }

            $this->pdo->commit();
            return $resultado;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE tipos_habitacion SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE tipos_habitacion SET activo = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tipos_habitacion WHERE nombre = ? AND id != ?");
            $stmt->execute([trim($nombre), $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tipos_habitacion WHERE nombre = ?");
            $stmt->execute([trim($nombre)]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Cuenta cuántas habitaciones están vinculadas a un tipo de habitación por su nombre.
     */
    public function contarHabitacionesAsociadas(string $tipoNombre): int {
        $sql = "SELECT COUNT(*) FROM habitaciones WHERE tipo_nombre = :tipo_nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tipo_nombre' => trim($tipoNombre)]);
        return (int)$stmt->fetchColumn();
    }
}