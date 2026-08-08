<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class UsuariosController {

    use ChecksPermisos;

    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('USUARIOS');

        require_once __DIR__ . '/../models/Roles.php';
        $rolesModel = new Roles($pdo);
        $rolesDisponibles = $rolesModel->getAll();

        $title = "Gestión de Usuarios";
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('USUARIOS');

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuariosModel = new Usuarios($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        $data = $estado === 'INACTIVO' ? $usuariosModel->getAllInactive() : $usuariosModel->getAll();

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('USUARIOS');

        $documentoId = trim((string)($_GET['documento_id'] ?? ''));
        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'Documento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuariosModel = new Usuarios($pdo);

        $usuario = $usuariosModel->getByDocumento($documentoId);
        if ($usuario) {
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        exit;
    }

    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('USUARIOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $documentoId = trim((string)($_POST['documento_id'] ?? ''));
        $esEdicion   = !empty($_POST['es_edicion']) && $_POST['es_edicion'] === '1';

        $data = [
            'rol_name'  => trim((string)($_POST['rol_name'] ?? '')),
            'username'  => trim((string)($_POST['username'] ?? '')),
            'nombres'   => trim((string)($_POST['nombres'] ?? '')),
            'apellidos' => trim((string)($_POST['apellidos'] ?? '')),
            'correo'    => trim((string)($_POST['correo'] ?? '')),
            'telefono'  => trim((string)($_POST['telefono'] ?? '')),
            'password'  => $_POST['password'] ?? '',
        ];
        
        // 1. Campos obligatorios principales
        if ($documentoId === '' || $data['username'] === '' || $data['nombres'] === '' ||
            $data['apellidos'] === '' || $data['correo'] === '' || $data['rol_name'] === '') {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben completarse.']);
            exit;
        }

        // 2. Validación del documento
        if (!ctype_digit($documentoId) || strlen($documentoId) < 8 || strlen($documentoId) > 15) {
            echo json_encode(['success' => false, 'message' => 'El documento debe ser numérico y tener entre 8 y 15 dígitos.']);
            exit;
        }

        // 3. Formato del Correo Electrónico
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
            exit;
        }

        // 4. Formato Username (Solo alfanumérico y _, 4 a 20 chars)
        if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $data['username'])) {
            echo json_encode(['success' => false, 'message' => 'El usuario solo permite caracteres alfanuméricos y entre 4 a 20 caracteres.']);
            exit;
        }

        // 5. Validación Contraseña
        if (!$esEdicion && (empty($data['password']) || strlen($data['password']) < 6)) {
            echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios y debe tener al menos 6 caracteres.']);
            exit;
        }
        if ($esEdicion && !empty($data['password']) && strlen($data['password']) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña cambiada debe tener al menos 6 caracteres.']);
            exit;
        }

        // 6. Validación opcional del Teléfono
        if ($data['telefono'] !== '' && (!ctype_digit($data['telefono']) || strlen($data['telefono']) < 7 || strlen($data['telefono']) > 15)) {
            echo json_encode(['success' => false, 'message' => 'El teléfono debe contener entre 7 y 15 dígitos.']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuariosModel = new Usuarios($pdo);
        /*=======================================================
                 VALIDACIÓN DE PROTECCIÓN EN USUARIOS
        =======================================================*/
        // Si un usuario está editando SU PROPIO perfil:
        if ($esEdicion && $documentoId === (string)($_SESSION['user_id'] ?? '')) {
            $usuarioActual = $usuariosModel->getByDocumento($documentoId);

            // Solo si el usuario actual es ADMINISTRADOR y está intentando cambiar su rol a otro diferente
            if ($usuarioActual && strtoupper($usuarioActual['rol_name']) === 'ADMINISTRADOR' && $data['rol_name'] !== 'ADMINISTRADOR') {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Como ADMINISTRADOR, no puedes quitarte tu propio rol de administración.'
                ]);
                exit;
            }
        }
        // Verificaciones de Unicidad
        if ($usuariosModel->existeUsername($data['username'], $esEdicion ? $documentoId : null)) {
            echo json_encode(['success' => false, 'message' => "El usuario '{$data['username']}' ya está en uso."]);
            exit;
        }
        if ($usuariosModel->existeCorreo($data['correo'], $esEdicion ? $documentoId : null)) {
            echo json_encode(['success' => false, 'message' => "El correo '{$data['correo']}' ya está registrado."]);
            exit;
        }

        try {
            if ($esEdicion) {
                $resultado = $usuariosModel->update($documentoId, $data);
                $mensajeOk = 'Usuario actualizado correctamente';
                $mensajeError = 'No se pudo actualizar el usuario';
            } else {
                if ($usuariosModel->existeDocumento($documentoId)) {
                    echo json_encode(['success' => false, 'message' => "El documento '{$documentoId}' ya está registrado."]);
                    exit;
                }
                $resultado = $usuariosModel->create(array_merge($data, ['documento_id' => $documentoId]));
                $mensajeOk = 'Usuario creado correctamente';
                $mensajeError = 'No se pudo crear el usuario';
            }

            echo json_encode(['success' => $resultado, 'message' => $resultado ? $mensajeOk : $mensajeError]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => 'Conflicto de duplicidad: El documento, usuario o correo ya existen.']);
            } else {
                error_log('Error en UsuariosController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error interno en la base de datos']);
            }
        }
        exit;
    }

    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('USUARIOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $documentoId = trim((string)($_POST['documento_id'] ?? ''));
        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'Documento no válido']);
            exit;
        }

        if ($documentoId === (string)($_SESSION['user_id'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propio usuario']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuariosModel = new Usuarios($pdo);

        $resultado = $usuariosModel->delete($documentoId);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Usuario desactivado correctamente' : 'No se pudo desactivar el usuario']);
        exit;
    }

    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('USUARIOS');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $documentoId = trim((string)($_POST['documento_id'] ?? ''));
        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'Documento no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuariosModel = new Usuarios($pdo);

        $resultado = $usuariosModel->restore($documentoId);
        echo json_encode(['success' => $resultado, 'message' => $resultado ? 'Usuario restaurado correctamente' : 'No se pudo restaurar el usuario']);
        exit;
    }
}