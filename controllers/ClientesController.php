<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class ClientesController {

    use ChecksPermisos;

    private const TIPOS_DOCUMENTO = ['DNI', 'PASAPORTE', 'CE', 'RUC'];

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('CLIENTES');

        $title = "Gestión de Clientes";
        require_once __DIR__ . '/../views/clientes/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $estado = 'ACTIVO';
        }

        $data = $estado === 'INACTIVO' ? $model->getAllInactive() : $model->getAll();

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /**
     * Autocompletado de clientes por documento, razón social o licencia de conducir.
     */
    public function buscarAJAX(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $termino = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($termino) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $model->buscar($termino)]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        $numero = trim((string)($_GET['numero_documento'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de documento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $cliente = $model->getByDocumento($numero);
        if ($cliente) {
            echo json_encode(['success' => true, 'data' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        }
        exit;
    }

    /**
     * Reglas de formato por tipo de documento.
     */
    private function validarNumeroDocumento(string $tipo, string $numero): ?string {
        switch ($tipo) {
            case 'DNI':
                if (!preg_match('/^\d{8}$/', $numero)) {
                    return 'El DNI debe tener exactamente 8 dígitos numéricos.';
                }
                break;
            case 'RUC':
                if (!preg_match('/^\d{11}$/', $numero)) {
                    return 'El RUC debe tener exactamente 11 dígitos numéricos.';
                }
                break;
            case 'CE':
                if (!preg_match('/^[A-Z0-9]{9,12}$/', $numero)) {
                    return 'El Carné de Extranjería debe tener entre 9 y 12 caracteres.';
                }
                break;
            case 'PASAPORTE':
                if (!preg_match('/^[A-Z0-9]{8,15}$/', $numero)) {
                    return 'El Pasaporte debe tener entre 8 y 15 caracteres alfanuméricos.';
                }
                break;
            default:
                return 'Tipo de documento no válido.';
        }
        return null;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $esEdicion = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';
        $tipoDocumento = strtoupper(trim((string)($_POST['tipo_documento'] ?? '')));
        $numeroDocumento = strtoupper(trim((string)($_POST['numero_documento'] ?? '')));
        $razonSocial = trim((string)($_POST['razon_social'] ?? ''));
        $licenciaConducir = strtoupper(trim((string)($_POST['licencia_conducir'] ?? '')));
        $correo = trim((string)($_POST['correo'] ?? ''));
        $telefono = trim((string)($_POST['telefono'] ?? ''));
        $direccion = trim((string)($_POST['direccion'] ?? ''));
        $pais = trim((string)($_POST['pais'] ?? ''));

        // 1. Tipo de documento
        if (!in_array($tipoDocumento, self::TIPOS_DOCUMENTO, true)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un tipo de documento válido.']);
            exit;
        }

        // 2. Número de documento
        if ($numeroDocumento === '') {
            echo json_encode(['success' => false, 'message' => 'El número de documento es obligatorio.']);
            exit;
        }

        $errorDoc = $this->validarNumeroDocumento($tipoDocumento, $numeroDocumento);
        if ($errorDoc !== null) {
            echo json_encode(['success' => false, 'message' => $errorDoc]);
            exit;
        }

        // 3. Razón social / Nombre
        if ($razonSocial === '' || mb_strlen($razonSocial) > 150) {
            echo json_encode(['success' => false, 'message' => 'La razón social / nombre es obligatorio y no puede exceder 150 caracteres.']);
            exit;
        }

        // 4. Licencia de conducir (Opcional, máx 30 caracteres)
        if ($licenciaConducir !== '' && mb_strlen($licenciaConducir) > 30) {
            echo json_encode(['success' => false, 'message' => 'La licencia de conducir no puede exceder 30 caracteres.']);
            exit;
        }

        // 5. Correo
        if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El correo ingresado no es válido.']);
            exit;
        }

        // 6. Teléfono
        if ($telefono !== '' && !preg_match('/^[0-9+\s()-]{6,20}$/', $telefono)) {
            echo json_encode(['success' => false, 'message' => 'El teléfono ingresado no es válido.']);
            exit;
        }

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $data = [
            'tipo_documento'    => $tipoDocumento,
            'numero_documento'  => $numeroDocumento,
            'razon_social'      => $razonSocial,
            'licencia_conducir' => $licenciaConducir,
            'correo'            => $correo,
            'telefono'          => $telefono,
            'direccion'         => $direccion,
            'pais'              => $pais,
        ];

        try {
            if ($esEdicion) {
                $original = trim((string)($_POST['numero_documento_original'] ?? $numeroDocumento));

                $clienteActual = $model->getByDocumento($original);
                if (!$clienteActual) {
                    echo json_encode(['success' => false, 'message' => 'El cliente a actualizar no existe.']);
                    exit;
                }

                $resultado = $model->update($original, $data);
                $mensajeOk = 'Cliente actualizado correctamente';
                $mensajeError = 'No se pudo actualizar el cliente';
            } else {
                if ($model->existeDocumento($numeroDocumento)) {
                    echo json_encode(['success' => false, 'message' => "Ya existe un cliente registrado con el documento '{$numeroDocumento}'."]);
                    exit;
                }
                $resultado = $model->create($data);
                $mensajeOk = 'Cliente registrado correctamente';
                $mensajeError = 'No se pudo registrar el cliente';
            }

            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensajeOk : $mensajeError]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "El documento '{$numeroDocumento}' ya se encuentra registrado."]);
            } else {
                error_log('Error en ClientesController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al guardar']);
            }
        }
        exit;
    }

    /**
     * Corrige el tipo/número de documento (PK) de un cliente ya registrado.
     * Propaga los cambios a contratos_alquiler y comprobantes_pago vía CASCADE.
     */
    public function cambiarDocumento(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numeroActual = trim((string)($_POST['numero_documento_actual'] ?? ''));
        $nuevoTipo = strtoupper(trim((string)($_POST['nuevo_tipo_documento'] ?? '')));
        $nuevoNumero = strtoupper(trim((string)($_POST['nuevo_numero_documento'] ?? '')));

        if ($numeroActual === '') {
            echo json_encode(['success' => false, 'message' => 'Cliente no válido.']);
            exit;
        }

        if (!in_array($nuevoTipo, self::TIPOS_DOCUMENTO, true)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un tipo de documento válido.']);
            exit;
        }

        $errorDoc = $this->validarNumeroDocumento($nuevoTipo, $nuevoNumero);
        if ($errorDoc !== null) {
            echo json_encode(['success' => false, 'message' => $errorDoc]);
            exit;
        }

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $clienteActual = $model->getByDocumento($numeroActual);
        if (!$clienteActual) {
            echo json_encode(['success' => false, 'message' => 'El cliente no existe.']);
            exit;
        }

        if ($nuevoTipo === $clienteActual['tipo_documento'] && $nuevoNumero === $clienteActual['numero_documento']) {
            echo json_encode(['success' => false, 'message' => 'No hay cambios: el tipo y número ingresados son los mismos que ya tiene el cliente.']);
            exit;
        }

        if ($nuevoNumero !== $numeroActual && $model->existeDocumento($nuevoNumero)) {
            echo json_encode(['success' => false, 'message' => "Ya existe otro cliente registrado con el documento '{$nuevoNumero}'."]);
            exit;
        }

        try {
            $resultado = $model->cambiarDocumento($numeroActual, $nuevoTipo, $nuevoNumero);
            echo json_encode([
                'success' => $resultado,
                'message' => $resultado
                    ? "Documento corregido correctamente. Se actualizó el historial de contratos y comprobantes a '{$nuevoNumero}'."
                    : 'No se pudo corregir el documento del cliente',
                'nuevo_numero_documento' => $nuevoNumero,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "El documento '{$nuevoNumero}' ya se encuentra registrado en otro cliente."]);
            } else {
                error_log('Error en ClientesController::cambiarDocumento: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error de base de datos al corregir el documento']);
            }
        }
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = trim((string)($_POST['numero_documento'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de documento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $cliente = $model->getByDocumento($numero);
        if (!$cliente) {
            echo json_encode(['success' => false, 'message' => 'El cliente no existe.']);
            exit;
        }

        $resultado = $model->delete($numero);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Cliente desactivado correctamente' : 'No se pudo desactivar el cliente'
        ]);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('CLIENTES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $numero = trim((string)($_POST['numero_documento'] ?? ''));
        if ($numero === '') {
            echo json_encode(['success' => false, 'message' => 'Número de documento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Clientes.php';
        $model = new Clientes($pdo);

        $cliente = $model->getByDocumento($numero);
        if (!$cliente) {
            echo json_encode(['success' => false, 'message' => 'El cliente no existe o no se puede encontrar.']);
            exit;
        }

        $resultado = $model->restore($numero);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Cliente restaurado correctamente' : 'No se pudo restaurar']);
        exit;
    }
}