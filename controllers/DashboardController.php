<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class DashboardController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }

        $this->requierePermiso('DASHBOARD');

        $title = "Panel Principal - Dashboard Hotelero";
        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    public function reporteVentas(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $fechaInicio = trim((string)($_GET['fecha_inicio'] ?? date('Y-m-d')));
        $fechaFin    = trim((string)($_GET['fecha_fin'] ?? date('Y-m-d')));

        $data = $dashboardModel->getIngresosYEstadiasPorFecha($fechaInicio, $fechaFin);

        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);
        exit;
    }

    public function reporteOcupacion(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $data = $dashboardModel->getOcupacionActualHabitaciones();

        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);
        exit;
    }

    public function listarIps(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        echo json_encode([
            'success' => true,
            'data'    => $dashboardModel->getHistorialIps()
        ]);
        exit;
    }

    public function getIpById(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de registro no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $detalle = $dashboardModel->getIpDetalleById($id);

        if ($detalle) {
            echo json_encode(['success' => true, 'data' => $detalle]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registro de IP no encontrado']);
        }
        exit;
    }
}