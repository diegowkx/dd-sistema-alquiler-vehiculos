<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class ReservasController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('RESERVAS');

        require_once __DIR__ . '/../models/Habitaciones.php';
        $habitacionesModel = new Habitaciones($pdo);
        $habitacionesDisponibles = $habitacionesModel->getAllDisponibles();

        $title = "Gestión de Reservas";
        require_once __DIR__ . '/../views/reservas/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('RESERVAS');

        require_once __DIR__ . '/../models/Reservas.php';
        $model = new Reservas($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? '')));
        $estadosValidos = ['CONFIRMADA', 'CHECKED_IN', 'CANCELADA', 'NO_SHOW'];
        if (!in_array($estado, $estadosValidos, true)) {
            $estado = null;
        }

        echo json_encode(['success' => true, 'data' => $model->getAll($estado)]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('RESERVAS');

        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID de reserva no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Reservas.php';
        $model = new Reservas($pdo);

        $reserva = $model->getById($id);
        if ($reserva) {
            echo json_encode(['success' => true, 'data' => $reserva]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Reserva no encontrada']);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('RESERVAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';
        $id = $esEdicion ? filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) : null;
        $clienteDoc = trim((string)($_POST['cliente_doc'] ?? ''));
        $numeroHabitacion = trim((string)($_POST['numero_habitacion'] ?? ''));
        $checkin = trim((string)($_POST['fecha_checkin_prevista'] ?? ''));
        $checkout = trim((string)($_POST['fecha_checkout_prevista'] ?? ''));
        $montoAdelantoRaw = $_POST['monto_adelanto'] ?? '0';
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        if ($esEdicion && !$id) {
            echo json_encode(['success' => false, 'message' => 'Reserva no válida.']);
            exit;
        }

        if (!$esEdicion && $clienteDoc === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un cliente.']);
            exit;
        }

        if (!$esEdicion && $numeroHabitacion === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una habitación.']);
            exit;
        }

        // 1. Validar formato de fechas y lógica temporal
        $checkinDate = DateTime::createFromFormat('Y-m-d', $checkin);
        $checkoutDate = DateTime::createFromFormat('Y-m-d', $checkout);
        
        if (!$checkinDate || !$checkoutDate) {
            echo json_encode(['success' => false, 'message' => 'Las fechas ingresadas no son válidas.']);
            exit;
        }

        // Validar que la fecha de check-in no sea anterior al día de hoy (prevención de reservas en el pasado)
        $hoy = new DateTime('today');
        if ($checkinDate < $hoy) {
            echo json_encode(['success' => false, 'message' => 'La fecha de check-in prevista no puede ser anterior a la fecha actual.']);
            exit;
        }

        if ($checkoutDate <= $checkinDate) {
            echo json_encode(['success' => false, 'message' => 'La fecha de check-out debe ser posterior a la de check-in.']);
            exit;
        }

        // 2. Validar adelanto
        if (!is_numeric($montoAdelantoRaw) || (float)$montoAdelantoRaw < 0) {
            echo json_encode(['success' => false, 'message' => 'El monto de adelanto debe ser un número mayor o igual a 0.']);
            exit;
        }
        $montoAdelanto = round((float)$montoAdelantoRaw, 2);

        // 3. Validar observaciones
        if ($observaciones !== '' && mb_strlen($observaciones) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Las observaciones no pueden exceder los 1000 caracteres.']);
            exit;
        }

        require_once __DIR__ . '/../models/Reservas.php';
        require_once __DIR__ . '/../models/Clientes.php';
        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Reservas($pdo);
        $clientesModel = new Clientes($pdo);
        $habitacionesModel = new Habitaciones($pdo);

        try {
            if ($esEdicion) {
                $reservaActual = $model->getById($id);
                if (!$reservaActual) {
                    echo json_encode(['success' => false, 'message' => 'La reserva a actualizar no existe.']);
                    exit;
                }

                // Solo se puede editar mientras esté CONFIRMADA (no si ya hizo check-in,
                // fue cancelada o marcada como no-show).
                if ($reservaActual['estado'] !== 'CONFIRMADA') {
                    echo json_encode(['success' => false, 'message' => "No se puede editar una reserva en estado '{$reservaActual['estado']}'."]);
                    exit;
                }

                // Nota: Asegúrate de que en el modelo se utilicen consultas preparadas (Prepared Statements) 
                // con marcadores (?) o(:parámetro) para garantizar la total prevención de Inyección SQL.
                if ($model->existeSolapamiento($reservaActual['numero_habitacion'], $checkin, $checkout, $id)) {
                    echo json_encode(['success' => false, 'message' => 'Ya existe otra reserva para esa habitación en esas fechas.']);
                    exit;
                }

                $resultado = $model->update($id, [
                    'fecha_checkin_prevista'  => $checkin,
                    'fecha_checkout_prevista' => $checkout,
                    'monto_adelanto'          => $montoAdelanto,
                    'observaciones'           => $observaciones,
                ]);

                echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Reserva actualizada correctamente' : 'No se pudo actualizar la reserva']);
                exit;
            }

            // --- Creación ---
            if (!$clientesModel->existeDocumento($clienteDoc)) {
                echo json_encode(['success' => false, 'message' => 'El cliente seleccionado no existe.']);
                exit;
            }

            $habitacion = $habitacionesModel->getByNumero($numeroHabitacion);
            if (!$habitacion) {
                echo json_encode(['success' => false, 'message' => 'La habitación seleccionada no existe.']);
                exit;
            }
            if ($habitacion['estado_disponibilidad'] !== 'DISPONIBLE') {
                echo json_encode(['success' => false, 'message' => "La habitación {$numeroHabitacion} no está disponible actualmente."]);
                exit;
            }

            if ($model->existeSolapamiento($numeroHabitacion, $checkin, $checkout)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una reserva para esa habitación en esas fechas.']);
                exit;
            }

            // Nota: Se obtiene el usuario logueado desde la sesión actual del sistema.
            $usuarioUsername = $_SESSION['user_name'] ?? null;
            if (!$usuarioUsername) {
                echo json_encode(['success' => false, 'message' => 'No se pudo identificar al usuario que registra la reserva. Inicie sesión nuevamente.']);
                exit;
            }

            $pdo->beginTransaction();

            $idReserva = $model->create([
                'cliente_doc'             => $clienteDoc,
                'numero_habitacion'       => $numeroHabitacion,
                'usuario_username'        => $usuarioUsername,
                'fecha_checkin_prevista'  => $checkin,
                'fecha_checkout_prevista' => $checkout,
                'monto_adelanto'          => $montoAdelanto,
                'observaciones'           => $observaciones,
            ]);

            if (!$idReserva) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'No se pudo crear la reserva']);
                exit;
            }

            // La habitación queda RESERVADA hasta que se confirme el check-in (Estadías)
            // o se cancele / marque como no-show.
            $habitacionesModel->updateEstado($numeroHabitacion, 'RESERVADA');

            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Reserva creada correctamente', 'data' => ['id' => $idReserva]]);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en ReservasController::guardar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar la reserva']);
        }
        exit;
    }

    /**
     * Cancela una reserva o la marca como No-Show. El paso a CHECKED_IN se hace
     * desde el módulo de Estadías (proceso de Recepción), no manualmente aquí —
     * mismo criterio que usaste para bloquear el estado OCUPADA en Habitaciones.
     */
    public function cambiarEstado(PDO $pdo): void {
        $this->requierePermisoApi('RESERVAS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nuevoEstado = strtoupper(trim((string)($_POST['estado'] ?? '')));

        if (!$id || !in_array($nuevoEstado, ['CANCELADA', 'NO_SHOW'], true)) {
            echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
            exit;
        }

        require_once __DIR__ . '/../models/Reservas.php';
        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Reservas($pdo);
        $habitacionesModel = new Habitaciones($pdo);

        $reserva = $model->getById($id);
        if (!$reserva) {
            echo json_encode(['success' => false, 'message' => 'La reserva no existe.']);
            exit;
        }

        if ($reserva['estado'] !== 'CONFIRMADA') {
            echo json_encode(['success' => false, 'message' => "No se puede cambiar el estado de una reserva '{$reserva['estado']}'."]);
            exit;
        }

        // Validar si intenta marcar como NO_SHOW antes de la fecha de check-in prevista
        if ($nuevoEstado === 'NO_SHOW') {
            $hoy = new DateTime('today');
            $checkinPrevisto = DateTime::createFromFormat('Y-m-d', $reserva['fecha_checkin_prevista']);
            
            if ($checkinPrevisto && $checkinPrevisto > $hoy) {
                echo json_encode(['success' => false, 'message' => 'No se puede marcar como No-Show una reserva cuya fecha de check-in aún no ha llegado.']);
                exit;
            }
        }

        try {
            $pdo->beginTransaction();

            $resultado = $model->updateEstado($id, $nuevoEstado);

            // Liberamos la habitación (si sigue marcada como RESERVADA por esta reserva).
            $habitacion = $habitacionesModel->getByNumero($reserva['numero_habitacion']);
            if ($habitacion && $habitacion['estado_disponibilidad'] === 'RESERVADA') {
                $habitacionesModel->updateEstado($reserva['numero_habitacion'], 'DISPONIBLE');
            }

            $pdo->commit();

            $mensaje = $nuevoEstado === 'CANCELADA' ? 'Reserva cancelada correctamente' : 'Reserva marcada como No-Show';
            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensaje : 'No se pudo actualizar la reserva']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error en ReservasController::cambiarEstado: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al actualizar la reserva']);
        }
        exit;
    }
}