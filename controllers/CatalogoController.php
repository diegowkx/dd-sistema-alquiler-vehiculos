<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';
require_once __DIR__ . '/../models/TiposVehiculo.php';
require_once __DIR__ . '/../models/Vehiculos.php';

class CatalogoController {

    use ChecksPermisos;

    /**
     * Renderiza la vista del catálogo de vehículos.
     */
    public function index(PDO $pdo): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }

        $this->requierePermiso('CATALOGO');

        $tiposModel = new TiposVehiculo($pdo);
        $tipos = $tiposModel->getAll();
        
        // Extraemos los nombres de las categorías activas
        $categorias = !empty($tipos) ? array_column($tipos, 'nombre') : [];

        $title = "Catálogo de Vehículos";
        require_once __DIR__ . '/../views/catalogo/index.php';
    }

    /**
     * API JSON que retorna los vehículos para el catálogo.
     */
    public function listarVehiculos(PDO $pdo): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->requierePermisoApi('CATALOGO');
        header('Content-Type: application/json');

        try {
            $vehiculosModel = new Vehiculos($pdo);
            // Usamos getAll() que ya trae el campo 'precio_base_dia' haciendo el JOIN
            $vehiculos = $vehiculosModel->getAll();

            echo json_encode([
                'success' => true,
                'data'    => $vehiculos
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener vehículos: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}