<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class DashboardDefaultController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        
        $title = "Panel de Inicio";

        // Instancia del modelo base preparado con PDO
        require_once __DIR__ . '/../models/DashboardDefault.php';
        $dashboardDefaultModel = new DashboardDefault($pdo);


        require_once __DIR__ . '/../views/dashboard_default/index.php';
    }
}