<?php

declare(strict_types=1);

class PermisosRoles {

    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve los NOMBRES de permisos asignados a un rol.
     * Es lo que ya usas en AuthController::process_login() para llenar $_SESSION['permisos'].
     */
    public function getPermisosByRolNombre(string $rolNombre): array {
        $stmt = $this->pdo->prepare("
            SELECT permiso_nombre 
            FROM permisos_roles 
            WHERE rol_nombre = ?
        ");
        $stmt->execute([strtoupper(trim($rolNombre))]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Todas las asignaciones agrupadas por rol. Útil para precargar
     * el modal "Editar rol" con los checkboxes ya marcados.
     */
    public function getAllAgrupado(): array {
        $stmt = $this->pdo->query("
            SELECT rol_nombre, permiso_nombre 
            FROM permisos_roles 
            ORDER BY rol_nombre ASC, permiso_nombre ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $agrupado = [];
        foreach ($rows as $row) {
            $agrupado[$row['rol_nombre']][] = $row['permiso_nombre'];
        }
        return $agrupado;
    }

    public function tieneRolPermiso(string $rolNombre, string $permisoNombre): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM permisos_roles 
            WHERE rol_nombre = ? AND permiso_nombre = ?
        ");
        $stmt->execute([strtoupper(trim($rolNombre)), strtoupper(trim($permisoNombre))]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Reemplaza POR COMPLETO el set de permisos de un rol.
     * Esto es lo que llamas desde el modal de crear/editar rol:
     * borra todo lo anterior e inserta el nuevo set marcado en los checkboxes.
     *
     * $permisosNombres = ['HABITACIONES', 'RESERVAS', 'CLIENTES', ...]
     */
    public function syncPermisos(string $rolNombre, array $permisosNombres): bool {
        $rolNombre = strtoupper(trim($rolNombre));

        try {
            $this->pdo->beginTransaction();

            $stmtDelete = $this->pdo->prepare("DELETE FROM permisos_roles WHERE rol_nombre = ?");
            $stmtDelete->execute([$rolNombre]);

            if (!empty($permisosNombres)) {
                $stmtInsert = $this->pdo->prepare("
                    INSERT INTO permisos_roles (rol_nombre, permiso_nombre) VALUES (?, ?)
                ");
                foreach (array_unique($permisosNombres) as $permisoNombre) {
                    $stmtInsert->execute([$rolNombre, strtoupper(trim((string) $permisoNombre))]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log('Error en PermisosRoles::syncPermisos: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Borra todas las asignaciones de un rol. El FK ya tiene ON DELETE CASCADE
     * si borras el rol directamente, pero esto sirve para casos puntuales.
     */
    public function eliminarPorRol(string $rolNombre): bool {
        $stmt = $this->pdo->prepare("DELETE FROM permisos_roles WHERE rol_nombre = ?");
        return $stmt->execute([strtoupper(trim($rolNombre))]);
    }
}