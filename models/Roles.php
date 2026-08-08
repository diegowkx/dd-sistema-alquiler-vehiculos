<?php

declare(strict_types=1);

require_once __DIR__ . '/PermisosRoles.php';

class Roles {

    private PDO $pdo;
    private PermisosRoles $permisosRolesModel;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->permisosRolesModel = new PermisosRoles($pdo);
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT id, nombre, activo, created_at, updated_at 
            FROM roles 
            WHERE activo = 1
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllInactive(): array {
        $stmt = $this->pdo->query("
            SELECT id, nombre, activo, created_at, updated_at 
            FROM roles 
            WHERE activo = 0
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, activo, created_at, updated_at 
            FROM roles 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByNombre(string $nombre): array|false {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, activo, created_at, updated_at 
            FROM roles 
            WHERE nombre = ?
        ");
        $stmt->execute([strtoupper(trim($nombre))]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Rol + lista de nombres de permisos asignados. Ideal para precargar
     * el modal de "Editar rol" (marcar los checkboxes correspondientes).
     */
    public function getByIdConPermisos(int $id): array|false {
        $rol = $this->getById($id);
        if (!$rol) {
            return false;
        }
        $rol['permisos'] = $this->permisosRolesModel->getPermisosByRolNombre($rol['nombre']);
        return $rol;
    }

    /**
     * Crea el rol y asigna su set inicial de permisos.
     * $permisos = ['HABITACIONES', 'RESERVAS', ...] (lo que venga marcado en el modal)
     */
    public function createConPermisos(string $nombre, array $permisos): bool {
        $nombre = strtoupper(trim($nombre));

        try {
            $stmt = $this->pdo->prepare("INSERT INTO roles (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
        } catch (PDOException $e) {
            error_log('Error en Roles::createConPermisos: ' . $e->getMessage());
            return false;
        }

        return $this->permisosRolesModel->syncPermisos($nombre, $permisos);
    }

    /**
     * Actualiza el nombre del rol y reemplaza su set de permisos.
     * Si el nombre cambia, el ON UPDATE CASCADE de las FKs ya propaga
     * el nuevo rol_nombre en permisos_roles y usuarios automáticamente.
     */
    public function updateConPermisos(int $id, string $nombre, array $permisos): bool {
        $rolActual = $this->getById($id);
        if (!$rolActual) {
            return false;
        }

        $nombreNuevo = strtoupper(trim($nombre));

        $stmt = $this->pdo->prepare("UPDATE roles SET nombre = ? WHERE id = ?");
        if (!$stmt->execute([$nombreNuevo, $id])) {
            return false;
        }

        return $this->permisosRolesModel->syncPermisos($nombreNuevo, $permisos);
    }

    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE roles SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE roles SET activo = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, ?int $excludeId = null): bool {
        $nombre = strtoupper(trim($nombre));
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM roles WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM roles WHERE nombre = ?");
            $stmt->execute([$nombre]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }
}