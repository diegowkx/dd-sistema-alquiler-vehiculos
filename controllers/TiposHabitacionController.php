<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class TiposHabitacionController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('TIPOS_HABITACION');

        $title = "Tipos de Habitación";
        require_once __DIR__ . '/../views/tipos_habitacion/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('TIPOS_HABITACION');

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $model = new TiposHabitacion($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $estado = 'ACTIVO';
        }

        $data = $estado === 'INACTIVO' ? $model->getAllInactive() : $model->getAll();

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('TIPOS_HABITACION');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de tipo de habitación no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $model = new TiposHabitacion($pdo);

        $tipo = $model->getById($id);
        if ($tipo) {
            echo json_encode(['success' => true, 'data' => $tipo]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación consultado no existe']);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('TIPOS_HABITACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $esEdicion = $id > 0;

        $nombre = strtoupper(trim((string)($_POST['nombre'] ?? '')));
        $precioRaw = $_POST['precio_base_noche'] ?? null;
        $capacidadRaw = $_POST['capacidad_personas'] ?? null;
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));

        // --- VALIDACIONES DE ENTRADA Y REGLAS DE NEGOCIO ---

        // 1. Validar Nombre
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del tipo de habitación es obligatorio.']);
            exit;
        }

        $longitudNombre = mb_strlen($nombre);
        if ($longitudNombre < 3 || $longitudNombre > 50) {
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener entre 3 y 50 caracteres.']);
            exit;
        }

        if (!preg_match('/^[A-Z0-9ÁÉÍÓÚÑ\s\.-]+$/u', $nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre solo puede contener letras, números, espacios, puntos y guiones.']);
            exit;
        }

        // 2. Validar Precio Base por Noche
        if ($precioRaw === null || !is_numeric($precioRaw)) {
            echo json_encode(['success' => false, 'message' => 'El precio base por noche debe ser un número válido.']);
            exit;
        }

        $precio = (float)$precioRaw;
        if ($precio <= 0) {
            echo json_encode(['success' => false, 'message' => 'El precio base por noche debe ser mayor a S/ 0.00.']);
            exit;
        }

        if ($precio > 99999999.99) {
            echo json_encode(['success' => false, 'message' => 'El precio excede el límite permitido por el sistema.']);
            exit;
        }

        // 3. Validar Capacidad de Personas
        if ($capacidadRaw === null || filter_var($capacidadRaw, FILTER_VALIDATE_INT) === false) {
            echo json_encode(['success' => false, 'message' => 'La capacidad de personas debe ser un número entero.']);
            exit;
        }

        $capacidad = (int)$capacidadRaw;
        if ($capacidad < 1 || $capacidad > 50) {
            echo json_encode(['success' => false, 'message' => 'La capacidad debe ser de al menos 1 persona y no superar las 50 personas.']);
            exit;
        }

        // 4. Validar Descripción (Opcional)
        if ($descripcion !== '' && mb_strlen($descripcion) > 500) {
            echo json_encode(['success' => false, 'message' => 'La descripción no puede exceder los 500 caracteres.']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $model = new TiposHabitacion($pdo);

        // 5. Validar existencia previa si es edición
        if ($esEdicion) {
            $tipoExistente = $model->getById($id);
            if (!$tipoExistente) {
                echo json_encode(['success' => false, 'message' => 'El tipo de habitación que intenta editar no existe.']);
                exit;
            }
        }

        // 6. Validar Duplicidad de Nombre
        if ($model->existeNombre($nombre, $esEdicion ? $id : null)) {
            echo json_encode(['success' => false, 'message' => "El tipo '{$nombre}' ya se encuentra registrado."]);
            exit;
        }

        $data = [
            'nombre' => $nombre,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
            'precio_base_noche' => round($precio, 2),
            'capacidad_personas' => $capacidad
        ];

        // --- EJECUCIÓN DE LA OPERACIÓN ---
        try {
            $resultado = $esEdicion ? $model->update($id, $data) : $model->create($data);
            $mensajeOk = $esEdicion ? 'Tipo de habitación actualizado correctamente' : 'Tipo de habitación creado correctamente';
            $mensajeError = $esEdicion ? 'No se pudo actualizar el tipo de habitación' : 'No se pudo crear el tipo de habitación';

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? $mensajeOk : $mensajeError
            ]);
        } catch (Throwable $e) {
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "El tipo '{$nombre}' ya se encuentra registrado."]);
            } else {
                error_log('Error en TiposHabitacionController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error inesperado de base de datos al guardar']);
            }
        }
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('TIPOS_HABITACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $model = new TiposHabitacion($pdo);

        // 1. Obtener tipo de habitación
        $tipo = $model->getById($id);
        if (!$tipo) {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación especificado no existe']);
            exit;
        }

        if ((int)$tipo['activo'] === 0) {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación ya se encuentra desactivado']);
            exit;
        }

        // 2. VALIDACIÓN: Verificar si hay habitaciones asignadas a este tipo
        $cantidadHabitaciones = $model->contarHabitacionesAsociadas($tipo['nombre']);
        if ($cantidadHabitaciones > 0) {
            echo json_encode([
                'success' => false,
                'message' => "No se puede desactivar el tipo '{$tipo['nombre']}' porque tiene {$cantidadHabitaciones} habitación(es) asociada(s). Reasigne o elimine las habitaciones primero."
            ]);
            exit;
        }

        // 3. Eliminar / Desactivar
        $resultado = $model->delete($id);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Tipo de habitación desactivado correctamente' : 'No se pudo desactivar el tipo de habitación'
        ]);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('TIPOS_HABITACION');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposHabitacion.php';
        $model = new TiposHabitacion($pdo);

        $tipo = $model->getById($id);
        if (!$tipo) {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación especificado no existe']);
            exit;
        }

        if ((int)$tipo['activo'] === 1) {
            echo json_encode(['success' => false, 'message' => 'El tipo de habitación ya se encuentra activo']);
            exit;
        }

        $resultado = $model->restore($id);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Tipo de habitación restaurado correctamente' : 'No se pudo restaurar el tipo de habitación'
        ]);
        exit;
    }
}