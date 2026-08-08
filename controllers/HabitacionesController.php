<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class HabitacionesController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('HABITACIONES');

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $tiposModel = new TiposHabitacion($pdo);
        $tiposDisponibles = $tiposModel->getAll();

        $title = "Gestión de Habitaciones";
        require_once __DIR__ . '/../views/habitaciones/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $estado = 'ACTIVO';
        }

        $data = $estado === 'INACTIVO' ? $model->getAllInactive() : $model->getAll();

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function getDisponiblesAJAX(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        echo json_encode(['success' => true, 'data' => $model->getAllDisponibles()]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        $numero = trim((string)($_GET['numero_habitacion'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de habitación no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        $habitacion = $model->getByNumero($numero);
        if ($habitacion) {
            echo json_encode(['success' => true, 'data' => $habitacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Habitación no encontrada']);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = strtoupper(trim((string)($_POST['numero_habitacion'] ?? '')));
        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';
        $tipoNombre = strtoupper(trim((string)($_POST['tipo_nombre'] ?? '')));
        $pisoRaw = $_POST['piso'] ?? null;
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        // 1. Validar N° Habitación
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'El número de habitación es obligatorio.']);
            exit;
        }

        if (mb_strlen($numero) > 10 || !preg_match('/^[A-Z0-9\.-]+$/', $numero)) {
            echo json_encode(['success' => false, 'message' => 'El número de habitación debe ser alfanumérico y de máximo 10 caracteres.']);
            exit;
        }

        // 2. Validar Piso
        if ($pisoRaw === null || filter_var($pisoRaw, FILTER_VALIDATE_INT) === false) {
            echo json_encode(['success' => false, 'message' => 'El piso debe ser un número entero válido.']);
            exit;
        }

        $piso = (int)$pisoRaw;
        if ($piso < 1 || $piso > 99) {
            echo json_encode(['success' => false, 'message' => 'El piso debe estar en el rango de 1 a 99.']);
            exit;
        }

        // 3. Validar Tipo de Habitación
        if ($tipoNombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un tipo de habitación.']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $tiposModel = new TiposHabitacion($pdo);
        if (!$tiposModel->existeNombre($tipoNombre)) {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación seleccionado no es válido o no existe.']);
            exit;
        }

        // 4. Validar Observaciones
        if ($observaciones !== '' && mb_strlen($observaciones) > 500) {
            echo json_encode(['success' => false, 'message' => 'Las observaciones no pueden exceder los 500 caracteres.']);
            exit;
        }

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        // --- NUEVA CASUÍSTICA: RESTRICCIÓN DE EDICIÓN CUANDO ESTÁ OCUPADA/RESERVADA ---
        if ($esEdicion) {
            $habitacionActual = $model->getByNumero($numero);
            if (!$habitacionActual) {
                echo json_encode(['success' => false, 'message' => 'La habitación a actualizar no existe.']);
                exit;
            }

            // Si está OCUPADA o RESERVADA, NO se debe permitir cambiarle el Tipo de Habitación
            $estadoActual = $habitacionActual['estado_disponibilidad'] ?? '';
            if (in_array($estadoActual, ['OCUPADA', 'RESERVADA'], true)) {
                if ($habitacionActual['tipo_nombre'] !== $tipoNombre) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "No se puede cambiar el tipo de habitación mientras se encuentre en estado '{$estadoActual}'."
                    ]);
                    exit;
                }
            }
        }

        $data = [
            'tipo_nombre' => $tipoNombre,
            'piso' => $piso,
            'observaciones' => $observaciones !== '' ? $observaciones : null
        ];

        try {
            if ($esEdicion) {
                $resultado = $model->update($numero, $data);
                $mensajeOk = 'Habitación actualizada correctamente';
                $mensajeError = 'No se pudo actualizar la habitación';
            } else {
                if ($model->existeNumero($numero)) {
                    echo json_encode(['success' => false, 'message' => "La habitación '{$numero}' ya se encuentra registrada."]);
                    exit;
                }
                $resultado = $model->create(array_merge($data, ['numero_habitacion' => $numero]));
                $mensajeOk = 'Habitación creada correctamente';
                $mensajeError = 'No se pudo crear la habitación';
            }

            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensajeOk : $mensajeError]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "La habitación '{$numero}' ya existe."]);
            } else {
                error_log('Error en HabitacionesController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar']);
            }
        }
        exit;
    }

    public function updateEstado(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = trim((string)($_POST['numero_habitacion'] ?? ''));
        $nuevoEstado = strtoupper(trim((string)($_POST['estado_disponibilidad'] ?? '')));
        $estadosPermitidos = ['DISPONIBLE', 'OCUPADA', 'RESERVADA', 'MANTENIMIENTO', 'LIMPIEZA'];

        if ($numero === '' || !in_array($nuevoEstado, $estadosPermitidos, true)) {
            echo json_encode(['success' => false, 'message' => 'Datos de estado no válidos']);
            exit;
        }

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        $habitacion = $model->getByNumero($numero);
        if (!$habitacion) {
            echo json_encode(['success' => false, 'message' => 'La habitación no existe.']);
            exit;
        }

        $estadoActual = $habitacion['estado_disponibilidad'];

        // --- NUEVA CASUÍSTICA: PROTECCIÓN DE ESTADOS CRÍTICOS ---
        // 1. No permitir forzar a DISPONIBLE o LIMPIEZA si la habitación está OCUPADA (Debe hacerse vía Check-out)
        if ($estadoActual === 'OCUPADA' && in_array($nuevoEstado, ['DISPONIBLE', 'LIMPIEZA'], true)) {
            echo json_encode([
                'success' => false, 
                'message' => 'La habitación está ocupada. Debe realizar el proceso de Check-out para liberar el estado.'
            ]);
            exit;
        }

        // 2. No permitir pasar a OCUPADA manualmente si no viene desde un proceso de Recepción / Check-in
        if ($nuevoEstado === 'OCUPADA' && $estadoActual !== 'OCUPADA') {
            echo json_encode([
                'success' => false, 
                'message' => 'No se puede marcar como OCUPADA manualmente. Registre un hospedaje desde Recepción.'
            ]);
            exit;
        }

        // 3. No permitir forzar a DISPONIBLE o LIMPIEZA si la habitación está RESERVADA (Debe hacerse vía Reservación)
        if ($estadoActual === 'RESERVADA' && in_array($nuevoEstado, ['DISPONIBLE', 'LIMPIEZA'], true)) {
            echo json_encode([
                'success' => false, 
                'message' => 'La habitación está reservada. Debe realizar el proceso de cancelación o no-show a la reserva asociada.'
            ]);
            exit;
        }

        // 4. No permitir pasar a RESERVADA manualmente si no viene desde un proceso de Recepción / Reservas
        if ($nuevoEstado === 'RESERVADA' && $estadoActual !== 'RESERVADA') {
            echo json_encode([
                'success' => false, 
                'message' => 'No se puede marcar como RESERVADA manualmente. Registre una reserva desde Recepción.'
            ]);
            exit;
        }


        // 5. No permitir forzar a MANTENIMIENTO si está OCUPADA o RESERVADA
        if ($nuevoEstado === 'MANTENIMIENTO' && in_array($estadoActual, ['OCUPADA', 'RESERVADA'], true)) {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede mandar a mantenimiento una habitación que está actualmente {$estadoActual}."
            ]);
            exit;
        }

        $resultado = $model->updateEstado($numero, $nuevoEstado);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? "Estado actualizado a {$nuevoEstado}" : 'No se pudo actualizar el estado']);
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = trim((string)($_POST['numero_habitacion'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de habitación no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        $habitacion = $model->getByNumero($numero);

        if (!$habitacion) {
            echo json_encode(['success' => false, 'message' => 'La habitación no existe.']);
            exit;
        }

        // Bloquear desactivación si la habitación está OCUPADA o RESERVADA
        if ($habitacion['estado_disponibilidad'] === 'OCUPADA') {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede desactivar la habitación N° {$numero} porque se encuentra actualmente OCUPADA."
            ]);
            exit;
        }

        if ($habitacion['estado_disponibilidad'] === 'RESERVADA') {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede desactivar la habitación N° {$numero} porque tiene una RESERVA activa."
            ]);
            exit;
        }

        $resultado = $model->delete($numero);
        echo json_encode([
            'success' => $resultado, 
            'message' => $resultado ? 'Habitación desactivada correctamente' : 'No se pudo desactivar la habitación'
        ]);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('HABITACIONES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = trim((string)($_POST['numero_habitacion'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de habitación no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Habitaciones.php';
        $model = new Habitaciones($pdo);

        $habitacion = $model->getByNumero($numero);
        if (!$habitacion) {
            echo json_encode(['success' => false, 'message' => 'La habitación no existe o no se puede encontrar.']);
            exit;
        }

        // --- NUEVA CASUÍSTICA: VALIDAR QUE EL TIPO DE HABITACIÓN AÚN EXISTA ---
        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $tiposModel = new TiposHabitacion($pdo);
        if (!$tiposModel->existeNombre($habitacion['tipo_nombre'])) {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede restaurar. El tipo de habitación '{$habitacion['tipo_nombre']}' ya no existe en el sistema."
            ]);
            exit;
        }

        $resultado = $model->restore($numero);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Habitación restaurada correctamente' : 'No se pudo restaurar']);
        exit;
    }
}