<?php

declare(strict_types=1);

require_once __DIR__ . '/ChecksPermisos.php';

class RolesController {

    use ChecksPermisos;

    /**
     * Vista principal del módulo (tabla + modal). Solo quien tenga el permiso ROLES entra aquí.
     */
    public function index(PDO $pdo): void {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }
        $this->requierePermiso('ROLES');

        require_once __DIR__ . '/../models/Permisos.php';
        $permisosModel = new Permisos($pdo);
        $permisosDisponibles = $permisosModel->getAll();

        $title = "Gestión de Roles y Permisos";
        require_once __DIR__ . '/../views/roles/index.php';
    }

    /**
     * Lista roles en JSON para el DataTable, con sus permisos ya incluidos para pintar badges.
     * Acepta ?estado=ACTIVO|INACTIVO para las pestañas.
     */
    public function listar(PDO $pdo): void {
        $this->requierePermisoApi('ROLES');

        require_once __DIR__ . '/../models/Roles.php';
        require_once __DIR__ . '/../models/PermisosRoles.php';

        $rolesModel = new Roles($pdo);
        $permisosRolesModel = new PermisosRoles($pdo);

        $estado = strtoupper(trim((string)($_GET['estado'] ?? 'ACTIVO')));
        
        // Validación de parámetro estado
        if (!in_array($estado, ['ACTIVO', 'INACTIVO'], true)) {
            $estado = 'ACTIVO';
        }

        $roles = $estado === 'INACTIVO' ? $rolesModel->getAllInactive() : $rolesModel->getAll();

        $permisosPorRol = $permisosRolesModel->getAllAgrupado();
        foreach ($roles as &$rol) {
            $rol['permisos'] = $permisosPorRol[$rol['nombre']] ?? [];
        }
        unset($rol);

        echo json_encode(['success' => true, 'data' => $roles]);
        exit;
    }

    /**
     * Rol + permisos asignados, para precargar el modal de edición.
     */
    public function obtener(PDO $pdo): void {
        $this->requierePermisoApi('ROLES');

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de rol no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Roles.php';
        $rolesModel = new Roles($pdo);

        $rol = $rolesModel->getByIdConPermisos($id);
        if ($rol) {
            echo json_encode(['success' => true, 'data' => $rol]);
        } else {
            echo json_encode(['success' => false, 'message' => 'El rol consultado no existe en el sistema']);
        }
        exit;
    }

    /**
     * Crea o actualiza un rol junto con su set de permisos (checkboxes del modal).
     * $_POST['permisos'] llega como array gracias al name="permisos[]" del form.
     */
    public function guardar(PDO $pdo): void {
        $this->requierePermisoApi('ROLES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $esEdicion = $id > 0;
        $nombre = strtoupper(trim((string)($_POST['nombre'] ?? '')));
        $permisos = $_POST['permisos'] ?? [];

        // --- VALIDACIONES DE ENTRADA Y REGLAS DE NEGOCIO ---

        // 1. Sanitizar el array de permisos
        if (!is_array($permisos)) {
            $permisos = [];
        }
        $permisos = array_values(array_filter(array_map('trim', $permisos)));

        // 2. Validación: Nombre obligatorio
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre del rol es obligatorio.']);
            exit;
        }

        // 3. Validación: Longitud mínima y máxima (VARCHAR 50)
        $longitudNombre = mb_strlen($nombre);
        if ($longitudNombre < 3) {
            echo json_encode(['success' => false, 'message' => 'El nombre del rol debe tener al menos 3 caracteres.']);
            exit;
        }
        if ($longitudNombre > 50) {
            echo json_encode(['success' => false, 'message' => 'El nombre del rol no debe superar los 50 caracteres.']);
            exit;
        }

        // 4. Validación: Caracteres permitidos (solo letras, números, espacios, guiones bajos o medios)
        if (!preg_match('/^[A-Z0-9_ -]+$/', $nombre)) {
            echo json_encode(['success' => false, 'message' => 'El nombre del rol solo puede contener letras, números, espacios y guiones.']);
            exit;
        }

        // 5. Validación: Al menos un permiso asignado
        if (empty($permisos)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos un módulo/permiso para el rol.']);
            exit;
        }

        require_once __DIR__ . '/../models/Roles.php';
        require_once __DIR__ . '/../models/Permisos.php';

        $rolesModel = new Roles($pdo);
        $permisosModel = new Permisos($pdo);

        // Obtenemos todos los permisos del sistema para validación de BD
        $todosLosPermisosBD = array_column($permisosModel->getAll(), 'nombre');

        // 6. Validación de Edición: Asegurar que el rol a editar exista
        if ($esEdicion) {
            $rolExistente = $rolesModel->getById($id);
            if (!$rolExistente) {
                echo json_encode(['success' => false, 'message' => 'El rol que intenta editar ya no existe.']);
                exit;
            }

            // CANDADO A: Impedir renombrar el rol ADMINISTRADOR
            if (strtoupper($rolExistente['nombre']) === 'ADMINISTRADOR' && $nombre !== 'ADMINISTRADOR') {
                echo json_encode(['success' => false, 'message' => 'No está permitido cambiar el nombre del rol ADMINISTRADOR.']);
                exit;
            }
        }

        // CANDADO B: El rol ADMINISTRADOR debe incluir TODOS los permisos del sistema
        if ($nombre === 'ADMINISTRADOR') {
            if (count($permisos) < count($todosLosPermisosBD)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El rol ADMINISTRADOR debe contar con TODOS los permisos del sistema sin excepción.'
                ]);
                exit;
            }
        }

        // 7. Validación: Duplicidad del nombre de rol
        if ($rolesModel->existeNombre($nombre, $esEdicion ? $id : null)) {
            echo json_encode(['success' => false, 'message' => "El rol '{$nombre}' ya se encuentra registrado."]);
            exit;
        }

        // 8. Validación Integridad FK: Validar que todos los permisos enviados existan en la tabla `permisos`
        $permisosInvalidos = array_diff($permisos, $todosLosPermisosBD);

        if (!empty($permisosInvalidos)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Uno o más permisos seleccionados no son válidos en el sistema: ' . implode(', ', $permisosInvalidos)
            ]);
            exit;
        }

        // --- EJECUCIÓN DE OPERACIÓN ---
        try {
            if ($esEdicion) {
                $resultado = $rolesModel->updateConPermisos($id, $nombre, $permisos);
                $mensajeOk = 'Rol actualizado correctamente';
                $mensajeError = 'No se pudo actualizar el rol en la base de datos';
            } else {
                $resultado = $rolesModel->createConPermisos($nombre, $permisos);
                $mensajeOk = 'Rol creado correctamente';
                $mensajeError = 'No se pudo crear el rol en la base de datos';
            }

            echo json_encode([
                'success' => $resultado,
                'message' => $resultado ? $mensajeOk : $mensajeError
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                echo json_encode(['success' => false, 'message' => "Conflicto de duplicidad o llave foránea con el rol '{$nombre}'."]);
            } else {
                error_log('Error en RolesController::guardar: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error inesperado de base de datos al procesar el rol']);
            }
        }
        exit;
    }

    /**
     * Soft delete (activo = 0). No borra usuarios ni permisos asignados,
     * solo desactiva el rol para que no aparezca disponible.
     */
    public function eliminar(PDO $pdo): void {
        $this->requierePermisoApi('ROLES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de rol no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Roles.php';
        $rolesModel = new Roles($pdo);

        // Validación: Asegurar que el rol existe y está activo antes de desactivar
        $rol = $rolesModel->getById($id);
        if (!$rol) {
            echo json_encode(['success' => false, 'message' => 'El rol especificado no existe']);
            exit;
        }

        if ((int)$rol['activo'] === 0) {
            echo json_encode(['success' => false, 'message' => 'El rol ya se encuentra desactivado']);
            exit;
        }

        $nombreRol = strtoupper($rol['nombre']);

        // CANDADO C: Prohibido desactivar el rol ADMINISTRADOR
        if ($nombreRol === 'ADMINISTRADOR') {
            echo json_encode([
                'success' => false,
                'message' => 'El rol ADMINISTRADOR es fundamental para el sistema y no se puede desactivar.'
            ]);
            exit;
        }

        // CANDADO D: Prohibido desactivar el propio rol con el que el usuario tiene la sesión iniciada
        $miRolActual = strtoupper((string)($_SESSION['rol_name'] ?? $_SESSION['user_role'] ?? ''));
        if ($miRolActual === $nombreRol) {
            echo json_encode([
                'success' => false,
                'message' => 'No puedes desactivar el rol al que perteneces en tu sesión actual.'
            ]);
            exit;
        }

        $resultado = $rolesModel->delete($id);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Rol desactivado correctamente' : 'No se pudo desactivar el rol'
        ]);
        exit;
    }

    /**
     * Restaura un rol previamente desactivado (activo = 1).
     */
    public function restaurar(PDO $pdo): void {
        $this->requierePermisoApi('ROLES');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de rol no válido']);
            exit;
        }

        require_once __DIR__ . '/../models/Roles.php';
        $rolesModel = new Roles($pdo);

        // Validación: Asegurar que el rol existe y está inactivo antes de restaurar
        $rol = $rolesModel->getById($id);
        if (!$rol) {
            echo json_encode(['success' => false, 'message' => 'El rol especificado no existe']);
            exit;
        }

        if ((int)$rol['activo'] === 1) {
            echo json_encode(['success' => false, 'message' => 'El rol ya se encuentra activo']);
            exit;
        }

        $resultado = $rolesModel->restore($id);
        echo json_encode([
            'success' => $resultado,
            'message' => $resultado ? 'Rol restaurado correctamente' : 'No se pudo restaurar el rol'
        ]);
        exit;
    }
}