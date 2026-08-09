<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class DashboardController {

    use ChecksPermisos;

    /**
     * Renderiza la vista principal del Dashboard
     */
    public function index(PDO $pdo): void {

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }

        $this->requierePermiso('DASHBOARD');

        $title = "Panel Principal - Sistema Rent a Car";
        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    /**
     * Endpoint API: Obtiene los ingresos totales y cantidad de contratos por rango de fechas
     */
    public function getMetricas(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        $fechaInicio = filter_input(INPUT_GET, 'fecha_inicio', FILTER_DEFAULT) ?? date('Y-m-d');
        $fechaFin    = filter_input(INPUT_GET, 'fecha_fin', FILTER_DEFAULT) ?? date('Y-m-d');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $data = $dashboardModel->getIngresosYContratosPorFecha((string)$fechaInicio, (string)$fechaFin);

        echo json_encode([
            'success' => true,
            'data'    => [
                'total_ventas'  => (int)$data['total_contratos'],
                'ingreso_total' => number_format((float)$data['ingreso_total'], 2, '.', '')
            ]
        ]);
        exit;
    }

    /**
     * Endpoint API: Obtiene las entregas y devoluciones pendientes
     */
    public function getOperacionesProximas(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        echo json_encode([
            'success' => true,
            'data'    => $dashboardModel->getOperacionesProximas()
        ]);
        exit;
    }

    /**
     * Endpoint API: Obtiene el estado actual de disponibilidad de la flota
     */
    public function getEstadoFlota(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        echo json_encode([
            'success' => true,
            'data'    => $dashboardModel->getEstadoActualFlota()
        ]);
        exit;
    }

    /**
     * Endpoint API: Obtiene los clientes recurrentes o de mayor facturación
     */
    public function getTopClientes(PDO $pdo): void {
        $this->requierePermisoApi('DASHBOARD');
        header('Content-Type: application/json');

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        echo json_encode([
            'success' => true,
            'data'    => $dashboardModel->getTopClientes()
        ]);
        exit;
    }

    /**
     * Endpoint API: Listado DataTable de historial de IPs
     */
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

    /**
     * Endpoint API: Consulta de detalle por ID de un registro de IP
     */
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

        if ($detalle !== false) {
            echo json_encode(['success' => true, 'data' => $detalle]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registro de IP no encontrado']);
        }
        exit;
    }
}