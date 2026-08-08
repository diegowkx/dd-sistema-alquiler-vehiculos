<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class EstadiasController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('ESTADIAS');

        require_once __DIR__ . '/../models/Habitaciones.php';
        $habitacionesModel = new Habitaciones($pdo);
        $habitacionesDisponibles = $habitacionesModel->getAllDisponibles();

        $title = "Gestión de Estadías";
        require_once __DIR__ . '/../views/estadias/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        require_once __DIR__ . '/../models/Estadias.php';
        $model = new Estadias($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? '')));
        $estadosValidos = ['EN_CURSO', 'FINALIZADA', 'ANULADA'];
        if (!in_array($estado, $estadosValidos, true)) {
            $estado = null;
        }

        echo json_encode(['success' => true, 'data' => $model->getAll($estado)]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de estadía no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        $model = new Estadias($pdo);

        $estadia = $model->getById($id);
        if ($estadia) {
            $estadia['cargos'] = $model->getCargos($id);
            $estadia['total_cargos'] = $model->getTotalCargos($id);
            echo json_encode(['success' => true, 'data' => $estadia]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Estadía no encontrada']);
        }
        exit;
    }

    /**
     * Buscador del modal de Check-In (?c=estadias&a=buscarReservasAJAX&q=...).
     * Filtra reservas CONFIRMADA por nombre/documento de cliente o número de
     * habitación, mostrando cada resultado concatenado (cliente + hab + fechas)
     * para poder elegir la reserva correcta cuando un cliente tiene varias.
     */
    public function buscarReservasAJAX(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        $termino = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($termino) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        require_once __DIR__ . '/../models/Reservas.php';
        $model = new Reservas($pdo);

        echo json_encode(['success' => true, 'data' => $model->buscarConfirmadasParaCheckin($termino)]);
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $modo = trim((string)($_POST['modo'] ?? 'reserva')); // 'reserva' | 'walkin'
        $reservaId = filter_var($_POST['reserva_id'] ?? null, FILTER_VALIDATE_INT);
        $clienteDoc = trim((string)($_POST['cliente_doc'] ?? ''));
        $numeroHabitacion = trim((string)($_POST['numero_habitacion'] ?? ''));
        $precioRaw = $_POST['precio_noche_pactado'] ?? '';

        $usuarioUsername = $_SESSION['user_name'] ?? null;
        if (!$usuarioUsername) {
            echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario. Inicie sesión nuevamente.']);
            exit;
        }

        if (!is_numeric($precioRaw) || (float)$precioRaw <= 0) {
            echo json_encode(['success' => false, 'message' => 'El precio por noche pactado debe ser un número mayor a 0.']);
            exit;
        }
        $precioNoche = round((float)$precioRaw, 2);

        require_once __DIR__ . '/../models/Estadias.php';
        require_once __DIR__ . '/../models/Reservas.php';
        require_once __DIR__ . '/../models/Clientes.php';
        require_once __DIR__ . '/../models/Habitaciones.php';
        $estadiasModel = new Estadias($pdo);
        $reservasModel = new Reservas($pdo);
        $clientesModel = new Clientes($pdo);
        $habitacionesModel = new Habitaciones($pdo);

        try {
            if ($modo === 'reserva') {
                if (!$reservaId) {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar una reserva de la lista de resultados.']);
                    exit;
                }
                $reserva = $reservasModel->getById($reservaId);
                if (!$reserva) {
                    echo json_encode(['success' => false, 'message' => 'La reserva seleccionada no existe.']);
                    exit;
                }
                if ($reserva['estado'] !== 'CONFIRMADA') {
                    echo json_encode(['success' => false, 'message' => "La reserva ya no está en estado CONFIRMADA (actual: {$reserva['estado']})."]);
                    exit;
                }
                $clienteDoc = $reserva['cliente_doc'];
                $numeroHabitacion = $reserva['numero_habitacion'];
            } else {
                if ($clienteDoc === '') {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar un cliente.']);
                    exit;
                }
                if (!$clientesModel->existeDocumento($clienteDoc)) {
                    echo json_encode(['success' => false, 'message' => 'El cliente seleccionado no existe.']);
                    exit;
                }
                if ($numeroHabitacion === '') {
                    echo json_encode(['success' => false, 'message' => 'Debe seleccionar una habitación.']);
                    exit;
                }
                $reservaId = null;
            }

            $habitacion = $habitacionesModel->getByNumero($numeroHabitacion);
            if (!$habitacion) {
                echo json_encode(['success' => false, 'message' => 'La habitación seleccionada no existe.']);
                exit;
            }
            // Se acepta DISPONIBLE (walk-in) o RESERVADA (esta misma reserva la tomó).
            if (!in_array($habitacion['estado_disponibilidad'], ['DISPONIBLE', 'RESERVADA'], true)) {
                echo json_encode(['success' => false, 'message' => "La habitación {$numeroHabitacion} no está disponible para check-in (estado actual: {$habitacion['estado_disponibilidad']})."]);
                exit;
            }

            $pdo->beginTransaction();

            $idEstadia = $estadiasModel->create([
                'reserva_id'           => $reservaId,
                'cliente_doc'          => $clienteDoc,
                'numero_habitacion'    => $numeroHabitacion,
                'usuario_checkin'      => $usuarioUsername,
                'precio_noche_pactado' => $precioNoche,
            ]);

            if (!$idEstadia) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'No se pudo registrar el check-in.']);
                exit;
            }

            // La reserva pasa a CHECKED_IN (si el check-in vino de una reserva).
            if ($reservaId) {
                $reservasModel->updateEstado($reservaId, 'CHECKED_IN');
            }

            // La habitación pasa a OCUPADA.
            $habitacionesModel->updateEstado($numeroHabitacion, 'OCUPADA');

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Check-in registrado correctamente', 'data' => ['id' => $idEstadia]]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en EstadiasController::guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al registrar el check-in']);
        }
        exit;
    }

    public function checkout(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        $usuarioUsername = $_SESSION['user_name'] ?? null;
        if (!$usuarioUsername) {
            echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Estadias($pdo);
        $habitacionesModel = new Habitaciones($pdo);

        $estadia = $model->getById($id);
        if (!$estadia) {
            echo json_encode(['success' => false, 'message' => 'Estadía no encontrada.']);
            exit;
        }
        if ($estadia['estado'] !== 'EN_CURSO') {
            echo json_encode(['success' => false, 'message' => "No se puede finalizar una estadía en estado '{$estadia['estado']}'."]);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $resultado = $model->checkout($id, $usuarioUsername);
            $habitacionesModel->updateEstado($estadia['numero_habitacion'], 'DISPONIBLE');

            $pdo->commit();

            echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Check-out registrado correctamente' : 'No se pudo finalizar la estadía']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en EstadiasController::checkout: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al finalizar la estadía']);
        }
        exit;
    }

    /**
     * Anula una estadía registrada por error. Revierte la reserva a CONFIRMADA
     * (si vino de una) y libera/regresa la habitación a su estado previo.
     */
    public function anular(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        require_once __DIR__ . '/../models/Reservas.php';
        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Estadias($pdo);
        $reservasModel = new Reservas($pdo);
        $habitacionesModel = new Habitaciones($pdo);

        $estadia = $model->getById($id);
        if (!$estadia) {
            echo json_encode(['success' => false, 'message' => 'Estadía no encontrada.']);
            exit;
        }
        if ($estadia['estado'] !== 'EN_CURSO') {
            echo json_encode(['success' => false, 'message' => "No se puede anular una estadía en estado '{$estadia['estado']}'."]);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $resultado = $model->anular($id);

            if ($estadia['reserva_id']) {
                $reservasModel->updateEstado((int)$estadia['reserva_id'], 'CONFIRMADA');
                $habitacionesModel->updateEstado($estadia['numero_habitacion'], 'RESERVADA');
            } else {
                $habitacionesModel->updateEstado($estadia['numero_habitacion'], 'DISPONIBLE');
            }

            $pdo->commit();

            echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Estadía anulada correctamente' : 'No se pudo anular la estadía']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en EstadiasController::anular: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al anular la estadía']);
        }
        exit;
    }

    // --- Cargos extra ---

    public function listarCargos(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        $estadiaId = filter_var($_GET['estadia_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$estadiaId) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        $model = new Estadias($pdo);

        echo json_encode(['success' => true, 'data' => $model->getCargos($estadiaId), 'total' => $model->getTotalCargos($estadiaId)]);
        exit;
    }

    public function agregarCargo(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $estadiaId = filter_var($_POST['estadia_id'] ?? null, FILTER_VALIDATE_INT);
        $concepto = trim((string)($_POST['concepto'] ?? ''));
        $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
        $precioRaw = $_POST['precio_unitario'] ?? '';

        if (!$estadiaId || $concepto === '' || mb_strlen($concepto) > 150) {
            echo json_encode(['success' => false, 'message' => 'Datos de cargo no válidos.']);
            exit;
        }
        if (!$cantidad || $cantidad < 1) {
            echo json_encode(['success' => false, 'message' => 'La cantidad debe ser un entero mayor a 0.']);
            exit;
        }
        if (!is_numeric($precioRaw) || (float)$precioRaw < 0) {
            echo json_encode(['success' => false, 'message' => 'El precio unitario no es válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        $model = new Estadias($pdo);

        $estadia = $model->getById($estadiaId);
        if (!$estadia || $estadia['estado'] !== 'EN_CURSO') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden agregar cargos a una estadía en curso.']);
            exit;
        }

        $ok = $model->addCargo($estadiaId, $concepto, $cantidad, round((float)$precioRaw, 2));

        echo json_encode([
            'success' => (bool)$ok,
            'message' => $ok ? 'Cargo agregado correctamente' : 'No se pudo agregar el cargo',
            'data'    => ['cargos' => $model->getCargos($estadiaId), 'total' => $model->getTotalCargos($estadiaId)],
        ]);
        exit;
    }

    public function eliminarCargo(PDO $pdo): void {
        $this->requierePermisoApi('ESTADIAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $estadiaId = filter_var($_POST['estadia_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !$estadiaId) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            exit;
        }

        require_once __DIR__ . '/../models/Estadias.php';
        $model = new Estadias($pdo);

        $estadia = $model->getById($estadiaId);
        if (!$estadia || $estadia['estado'] !== 'EN_CURSO') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden eliminar cargos de una estadía en curso.']);
            exit;
        }

        $ok = $model->deleteCargo($id);

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Cargo eliminado' : 'No se pudo eliminar el cargo',
            'data'    => ['cargos' => $model->getCargos($estadiaId), 'total' => $model->getTotalCargos($estadiaId)],
        ]);
        exit;
    }
}