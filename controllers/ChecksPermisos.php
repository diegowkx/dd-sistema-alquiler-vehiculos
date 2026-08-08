<?php

declare(strict_types=1);

/**
 * Trait reutilizable en todos los controllers del sistema.
 *
 * - requierePermiso(): para rutas de PÁGINA COMPLETA (ej. index()). Redirige.
 * - requierePermisoApi(): para endpoints AJAX/fetch que siempre deben responder JSON
 *   (listar, obtener, guardar, eliminar, etc.), sin importar si el request no manda
 *   cabeceras de AJAX.
 *
 * Úsalo en CADA acción del controller, no solo en index():
 *
 *   class RolesController {
 *       use ChecksPermisos;
 *
 *       public function index(PDO $pdo) {
 *           $this->requierePermiso('ROLES');
 *           ...
 *       }
 *
 *       public function guardar(PDO $pdo) {
 *           $this->requierePermisoApi('ROLES');
 *           ...
 *       }
 *   }
 */
trait ChecksPermisos {

    protected function requierePermiso(string $permiso): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login&error=sesion_expirada');
            exit;
        }

        if (!$this->tienePermiso($permiso)) {
            http_response_code(403);
            $title = "Acceso denegado";
            require_once __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    protected function requierePermisoApi(string $permiso): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Inicie sesión nuevamente.']);
            exit;
        }

        if (!$this->tienePermiso($permiso)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para realizar esta acción.']);
            exit;
        }
    }

    /**
     * Para rutas accesibles a CUALQUIER usuario logueado, sin exigir un permiso puntual
     * (ej. el Dashboard, al que aterrizan todos los roles tras el login).
     * Lo que cambia por rol dentro de esas rutas se decide con tienePermiso() en el controller.
     */
    protected function requiereSesion(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login&error=sesion_expirada');
            exit;
        }
    }

    /**
     * Chequeo puntual que NO corta la ejecución (ocultar botones, lógica condicional).
     */
    protected function tienePermiso(string $permiso): bool {
        $permisos = $_SESSION['permisos'] ?? [];
        return is_array($permisos) && in_array(strtoupper($permiso), $permisos, true);
    }
}