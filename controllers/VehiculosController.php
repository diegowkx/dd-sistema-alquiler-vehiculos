<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class VehiculosController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('VEHICULOS');

        require_once __DIR__ . '/../models/TiposVehiculo.php';
        $tiposModel = new TiposVehiculo($pdo);
        $tiposDisponibles = $tiposModel->getAll();

        // Obtener la lista de imágenes dinámicamente desde assets/vehiculos/
        $imagenesDisponibles = $this->obtenerImagenesDisponibles();

        $title = "Gestión de Vehículos";
        require_once __DIR__ . '/../views/vehiculos/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $estado = 'ACTIVO';
        }

        $data = $estado === 'INACTIVO' ? $model->getAllInactive() : $model->getAll();

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function getDisponiblesAJAX(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        echo json_encode(['success' => true, 'data' => $model->getAllDisponibles()]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        $placa = trim((string)($_GET['placa'] ?? ''));
        if ($placa === '') {
            echo json_encode(['success' => false, 'message' => 'Placa de vehículo no válida']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        $vehiculo = $model->getByPlaca($placa);
        if ($vehiculo) {
            echo json_encode(['success' => true, 'data' => $vehiculo]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $placa = strtoupper(trim((string)($_POST['placa'] ?? '')));
        $placaOriginal = strtoupper(trim((string)($_POST['placa_original'] ?? '')));
        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';
        $marca = trim((string)($_POST['marca'] ?? ''));
        $modeloNombre = trim((string)($_POST['modelo'] ?? ''));
        $anioRaw = $_POST['anio'] ?? null;
        $color = trim((string)($_POST['color'] ?? ''));
        $kilometrajeRaw = $_POST['kilometraje'] ?? 0;
        $imagenUrl = trim((string)($_POST['imagen_url'] ?? ''));
        $observaciones = trim((string)($_POST['observaciones'] ?? ''));

        // 1. Validar Placa
        if ($placa === '') {
            echo json_encode(['success' => false, 'message' => 'La placa es obligatoria.']);
            exit;
        }

        if (mb_strlen($placa) > 10 || !preg_match('/^[A-Z0-9\.-]+$/', $placa)) {
            echo json_encode(['success' => false, 'message' => 'La placa debe ser alfanumérica y de máximo 10 caracteres.']);
            exit;
        }

        // 2. Validar Marca y Modelo
        if ($marca === '' || $modeloNombre === '') {
            echo json_encode(['success' => false, 'message' => 'La marca y el modelo son obligatorios.']);
            exit;
        }

        // 3. Validar Año
        if ($anioRaw === null || filter_var($anioRaw, FILTER_VALIDATE_INT) === false) {
            echo json_encode(['success' => false, 'message' => 'El año debe ser un número entero válido.']);
            exit;
        }
        $anio = (int)$anioRaw;
        $anioActual = (int)date('Y');
        if ($anio < 1900 || $anio > ($anioActual + 1)) {
            echo json_encode(['success' => false, 'message' => "El año debe estar entre 1900 y " . ($anioActual + 1) . "."]);
            exit;
        }

        // 4. Validar Color
        if ($color === '') {
            echo json_encode(['success' => false, 'message' => 'El color es obligatorio.']);
            exit;
        }

        // 5. Validar Kilometraje
        if (filter_var($kilometrajeRaw, FILTER_VALIDATE_INT) === false || (int)$kilometrajeRaw < 0) {
            echo json_encode(['success' => false, 'message' => 'El kilometraje debe ser un número entero mayor o igual a 0.']);
            exit;
        }
        $kilometraje = (int)$kilometrajeRaw;

        // 6. Validar Tipo de Vehículo
        $tipoNombre = trim($_POST['tipo_nombre'] ?? '');

        if ($tipoNombre === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un tipo de vehículo.']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposVehiculo.php';
        $tiposModel = new TiposVehiculo($pdo);
        if (!$tiposModel->existeNombre($tipoNombre)) {
            echo json_encode(['success' => false, 'message' => 'El tipo de vehículo seleccionado no es válido o no existe.']);
            exit;
        }

        // 7. Validar Observaciones
        if ($observaciones !== '' && mb_strlen($observaciones) > 500) {
            echo json_encode(['success' => false, 'message' => 'Las observaciones no pueden exceder los 500 caracteres.']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        // --- RESTRICCIÓN DE EDICIÓN SI ESTÁ ALQUILADO ---
        if ($esEdicion) {
            $placaABuscar = $placaOriginal !== '' ? $placaOriginal : $placa;
            $vehiculoActual = $model->getByPlaca($placaABuscar); 
            if (!$vehiculoActual) {
                echo json_encode(['success' => false, 'message' => 'El vehículo a actualizar no existe.']);
                exit;
            }

            $estadoActual = $vehiculoActual['estado_disponibilidad'] ?? '';
            if ($estadoActual === 'ALQUILADO') {
                if ($vehiculoActual['tipo_nombre'] !== $tipoNombre) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "No se puede cambiar el tipo de vehículo mientras se encuentre en estado 'ALQUILADO'."
                    ]);
                    exit;
                }
            }

            // Validar si cambió la placa y la nueva placa ya la tiene OTRO vehículo registrado
            if ($placa !== $placaABuscar && $model->existePlaca($placa)) {
                echo json_encode(['success' => false, 'message' => "La nueva placa '{$placa}' ya pertenece a otro vehículo."]);
                exit;
            }
        }

        // Estructuración del array de datos
        $data = [
            'tipo_nombre'   => $tipoNombre,
            'marca'         => $marca,
            'modelo'        => $modeloNombre,
            'anio'          => $anio,
            'color'         => $color,
            'kilometraje'   => $kilometraje,
            'observaciones' => $observaciones !== '' ? $observaciones : null
        ];

        // Se agrega la imagen solo si fue enviada
        if ($imagenUrl !== '') {
            $data['imagen_url'] = $imagenUrl;
        }

        try {
            if ($esEdicion) {
                $placaTarget = $placaOriginal !== '' ? $placaOriginal : $placa;
                $resultado = $model->update($placaTarget, $data);
                $mensajeOk = 'Vehículo actualizado correctamente';
                $mensajeError = 'No se pudo actualizar el vehículo';
            } else {
                if ($model->existePlaca($placa)) {
                    echo json_encode(['success' => false, 'message' => "La placa '{$placa}' ya se encuentra registrada."]);
                    exit;
                }
                $resultado = $model->create(array_merge($data, ['placa' => $placa]));
                $mensajeOk = 'Vehículo creado correctamente';
                $mensajeError = 'No se pudo crear el vehículo';
            }

            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensajeOk : $mensajeError]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "La placa '{$placa}' ya existe."]);
            } else {
                error_log('Error en VehiculosController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar']);
            }
        }
        exit;
    }

    public function cambiarPlaca(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $placaActual = strtoupper(trim((string)($_POST['placa_actual'] ?? '')));
        $nuevaPlaca = strtoupper(trim((string)($_POST['nueva_placa'] ?? '')));

        if ($placaActual === '' || $nuevaPlaca === '') {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar una placa válida.']);
            exit;
        }

        if (mb_strlen($nuevaPlaca) > 10 || !preg_match('/^[A-Z0-9\.-]+$/', $nuevaPlaca)) {
            echo json_encode(['success' => false, 'message' => 'La nueva placa debe ser alfanumérica y de máximo 10 caracteres.']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        if ($placaActual === $nuevaPlaca) {
            echo json_encode(['success' => false, 'message' => 'La nueva placa es idéntica a la actual.']);
            exit;
        }

        if ($model->existePlaca($nuevaPlaca)) {
            echo json_encode(['success' => false, 'message' => "La placa '{$nuevaPlaca}' ya pertenece a otro vehículo."]);
            exit;
        }

        try {
            $resultado = $model->cambiarPlaca($placaActual, $nuevaPlaca);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado 
                    ? "Placa actualizada correctamente de '{$placaActual}' a '{$nuevaPlaca}'."
                    : 'No se pudo actualizar la placa del vehículo.'
            ]);
        } catch (PDOException $e) {
            error_log('Error en VehiculosController::cambiarPlaca: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error de base de datos al cambiar la placa.']);
        }
        exit;
    }

    public function updateEstado(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $placa = strtoupper(trim((string)($_POST['placa'] ?? '')));
        $nuevoEstado = strtoupper(trim((string)($_POST['estado_disponibilidad'] ?? '')));
        $estadosPermitidos = ['DISPONIBLE', 'ALQUILADO', 'MANTENIMIENTO'];

        if ($placa === '' || !in_array($nuevoEstado, $estadosPermitidos, true)) {
            echo json_encode(['success' => false, 'message' => 'Datos de estado no válidos']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        $vehiculo = $model->getByPlaca($placa);
        if (!$vehiculo) {
            echo json_encode(['success' => false, 'message' => 'El vehículo no existe.']);
            exit;
        }

        $estadoActual = $vehiculo['estado_disponibilidad'];

        // --- PROTECCIÓN DE ESTADOS CRÍTICOS ---
        if ($estadoActual === 'ALQUILADO' && $nuevoEstado !== 'ALQUILADO') {
            echo json_encode([
                'success' => false, 
                'message' => 'El vehículo está actualmente alquilado. Debe realizar el proceso de devolución para cambiar su estado.'
            ]);
            exit;
        }

        if ($nuevoEstado === 'ALQUILADO' && $estadoActual !== 'ALQUILADO') {
            echo json_encode([
                'success' => false, 
                'message' => 'No se puede marcar como ALQUILADO manualmente. Registre un alquiler desde el módulo correspondiente.'
            ]);
            exit;
        }

        $resultado = $model->updateEstado($placa, $nuevoEstado);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? "Estado actualizado a {$nuevoEstado}" : 'No se pudo actualizar el estado']);
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $placa = strtoupper(trim((string)($_POST['placa'] ?? '')));
        if ($placa === '') {
            echo json_encode(['success' => false, 'message' => 'Placa no válida']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        $vehiculo = $model->getByPlaca($placa);

        if (!$vehiculo) {
            echo json_encode(['success' => false, 'message' => 'El vehículo no existe.']);
            exit;
        }

        if ($vehiculo['estado_disponibilidad'] === 'ALQUILADO') {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede desactivar el vehículo '{$placa}' porque se encuentra actualmente ALQUILADO."
            ]);
            exit;
        }

        $resultado = $model->delete($placa);
        echo json_encode([
            'success' => $resultado, 
            'message' => $resultado ? 'Vehículo desactivado correctamente' : 'No se pudo desactivar el vehículo'
        ]);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('VEHICULOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $placa = strtoupper(trim((string)($_POST['placa'] ?? '')));
        if ($placa === '') {
            echo json_encode(['success' => false, 'message' => 'Placa no válida']);
            exit;
        }

        require_once __DIR__ . '/../models/Vehiculos.php';
        $model = new Vehiculos($pdo);

        $vehiculo = $model->getByPlaca($placa);
        if (!$vehiculo) {
            echo json_encode(['success' => false, 'message' => 'El vehículo no existe o no se puede encontrar.']);
            exit;
        }

        require_once __DIR__ . '/../models/TiposVehiculo.php';
        $tiposModel = new TiposVehiculo($pdo);
        if (!$tiposModel->existeNombre($vehiculo['tipo_nombre'])) {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede restaurar. El tipo de vehículo '{$vehiculo['tipo_nombre']}' ya no existe en el sistema."
            ]);
            exit;
        }

        $resultado = $model->restore($placa);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Vehículo restaurado correctamente' : 'No se pudo restaurar']);
        exit;
    }

    /**
     * Escanea el directorio assets/vehiculos/ y retorna las rutas relativas.
     */
    private function obtenerImagenesDisponibles(): array {
        $directorio = __DIR__ . '/../assets/vehiculos/';
        $imagenes = [];

        if (is_dir($directorio)) {
            $archivos = scandir($directorio);
            foreach ($archivos as $archivo) {
                if ($archivo !== '.' && $archivo !== '..' && preg_match('/\.(png|jpg|jpeg|webp)$/i', $archivo)) {
                    $imagenes[] = 'assets/vehiculos/' . $archivo;
                }
            }
        }

        return !empty($imagenes) ? $imagenes : ['assets/vehiculos/default.png'];
    }
}