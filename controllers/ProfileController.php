<?php

declare(strict_types=1);

class ProfileController {

    /**
     * Retorna los datos del usuario en sesión para rellenar el modal.
     */
    public function get(PDO $pdo): void {
        header('Content-Type: application/json');

        $documentoId = (string)($_SESSION['user_documento_id'] ?? $_SESSION['user_id'] ?? '');

        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuarioModel = new Usuarios($pdo);
        
        // Corregido: Llamar a getByDocumento en lugar de getByDocumentoId
        $usuario = $usuarioModel->getByDocumento($documentoId);

        if ($usuario) {
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        exit;
    }

    /**
     * Procesa la actualización del perfil personal.
     */
    public function update(PDO $pdo): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $documentoId = (string)($_SESSION['user_documento_id'] ?? $_SESSION['user_id'] ?? '');

        if ($documentoId === '') {
            echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuarioModel = new Usuarios($pdo);

        // Obtenemos los datos actuales del usuario para mantener los campos obligatorios del modelo
        $usuarioActual = $usuarioModel->getByDocumento($documentoId);
        if (!$usuarioActual) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        $correo   = strtolower(trim((string)($_POST['correo'] ?? '')));
        $telefono = trim((string)($_POST['telefono'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Ingrese un correo electrónico válido']);
            exit;
        }

        // Usamos existeCorreo del modelo para verificar si ya está registrado por otro usuario
        if ($usuarioModel->existeCorreo($correo, $documentoId)) {
            echo json_encode(['success' => false, 'message' => 'El correo ya pertenece a otro usuario']);
            exit;
        }

        // Construimos el array completo que exige el método update() de tu modelo
        $data = [
            'rol_name'  => $usuarioActual['rol_name'],
            'username'  => $usuarioActual['username'],
            'nombres'   => $usuarioActual['nombres'],
            'apellidos' => $usuarioActual['apellidos'],
            'correo'    => $correo,
            'telefono'  => $telefono !== '' ? $telefono : null,
            'password'  => $password // Si va vacío, el modelo respeta la contraseña actual
        ];

        try {
            $resultado = $usuarioModel->update($documentoId, $data);
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el perfil']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
        }
        exit;
    }
}