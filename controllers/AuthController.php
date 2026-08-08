<?php

declare(strict_types=1);

class AuthController {

    /**
     * Obtiene la IP real del cliente (filtrando proxies y Cloudflare)
     */
    private function obtenerIpCliente(): string {
        $ip = '';

        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $ip_candidate) {
                $ip_candidate = trim($ip_candidate);
                if (filter_var($ip_candidate, FILTER_VALIDATE_IP)) {
                    $ip = $ip_candidate;
                    break;
                }
            }
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        if ($ip === '::1') {
            $ip = '127.0.0.1';
        }

        return $ip;
    }

    /**
     * Decide a qué landing va cada rol, basándose en el permiso 'DASHBOARD'
     * (nunca comparando el nombre del rol). Dos rutas, dos controllers, dos vistas:
     *
     * - Con 'DASHBOARD'    -> ?c=dashboard          (panel completo, típicamente ADMINISTRADOR)
     * - Sin 'DASHBOARD'    -> ?c=dashboard_default   (landing simple para el resto de roles)
     *
     * Un rol nuevo que crees desde el modal de Roles cae automáticamente en el
     * fallback correcto según si le marcaste el checkbox 'DASHBOARD' o no.
     */
    private function getRedirectPorPermisos(array $permisos): string {
        if (in_array('DASHBOARD', $permisos, true)) {
            return '?c=dashboard';
        }
        return '?c=dashboard_default';
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . $this->getRedirectPorPermisos($_SESSION['permisos'] ?? []));
            exit;
        }

        $error = '';
        if (isset($_GET['error'])) {
            $errores = [
                'credenciales'    => 'Usuario o contraseña incorrectos.',
                'cuenta_inactiva' => 'Su cuenta ha sido desactivada. Contacte al administrador.',
                'sesion_expirada' => 'Su sesión ha expirado. Inicie sesión nuevamente.',
            ];
            $error = $errores[$_GET['error']] ?? '';
        }

        $success = '';

        $title = "Iniciar sesión";
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function process_login(PDO $pdo) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?c=auth&a=login');
            exit;
        }

        $emailInput    = strtolower(trim($_POST['email'] ?? ''));
        $passwordInput = $_POST['password'] ?? '';

        if (empty($emailInput) || empty($passwordInput)) {
            header('Location: ' . BASE_URL . '?c=auth&a=login&error=credenciales');
            exit;
        }

        require_once __DIR__ . '/../models/Usuarios.php';
        $usuarioModel = new Usuarios($pdo);

        $usuario = $usuarioModel->authenticate($emailInput, $passwordInput);

        if (!$usuario) {
            header('Location: ' . BASE_URL . '?c=auth&a=login&error=credenciales');
            exit;
        }

        session_unset();

        $_SESSION['user_id']        = $usuario['documento_id'];
        $_SESSION['user_name']      = $usuario['username'];
        $_SESSION['user_nombres']   = $usuario['nombres'];
        $_SESSION['user_apellidos'] = $usuario['apellidos'];
        $_SESSION['user_rol']       = $usuario['rol_name'];
        $_SESSION['user_email']     = $usuario['correo'];
        $_SESSION['user_telefono']  = $usuario['telefono'] ?? '';

        require_once __DIR__ . '/../models/PermisosRoles.php';
        $rolesModel = new PermisosRoles($pdo);
        $_SESSION['permisos'] = $rolesModel->getPermisosByRolNombre($usuario['rol_name']);

        require_once __DIR__ . '/../models/Dashboard.php';
        $dashboardModel = new Dashboard($pdo);

        $ipCliente = $this->obtenerIpCliente();
        $dashboardModel->registrarAccesoIp(
            $usuario['username'],
            $usuario['rol_name'],
            $ipCliente
        );

        $redirect = $this->getRedirectPorPermisos($_SESSION['permisos']);

        header('Location: ' . BASE_URL . $redirect);
        exit;
    }

    public function logout() {
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . '?c=auth&a=login');
        exit;
    }
}