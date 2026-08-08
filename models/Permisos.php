<?php

declare(strict_types=1);

class Permisos {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Lista todos los módulos/permisos registrados en el sistema.
     * Son "fijos" en el sentido de que no se crean desde la UI operativa,
     * solo cuando tú agregas un módulo nuevo a la aplicación (ej. DASHBOARD_DEFAULT).
     */
    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT id, nombre, descripcion 
            FROM permisos 
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("SELECT id, nombre, descripcion FROM permisos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByNombre(string $nombre): array|false {
        $stmt = $this->pdo->prepare("SELECT id, nombre, descripcion FROM permisos WHERE nombre = ?");
        $stmt->execute([strtoupper(trim($nombre))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Da de alta un nuevo módulo/permiso. Úsalo solo cuando agregues
     * una sección nueva a la app (ej. crear 'DASHBOARD_DEFAULT').
     */
    public function create(string $nombre, ?string $descripcion = null): bool {
        $stmt = $this->pdo->prepare("INSERT INTO permisos (nombre, descripcion) VALUES (?, ?)");
        return $stmt->execute([strtoupper(trim($nombre)), $descripcion]);
    }

    public function update(int $id, string $nombre, ?string $descripcion = null): bool {
        $stmt = $this->pdo->prepare("UPDATE permisos SET nombre = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([strtoupper(trim($nombre)), $descripcion, $id]);
    }

    /**
     * Elimina un módulo. El FK en permisos_roles tiene ON DELETE CASCADE,
     * así que se limpian automáticamente las asignaciones a roles.
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM permisos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, ?int $excludeId = null): bool {
        $nombre = strtoupper(trim($nombre));
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM permisos WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }
}