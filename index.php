<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Obtener controller y action de forma segura
$controllerName = isset($_GET['c']) ? strtolower($_GET['c']) : 'dashboard';
$actionName     = isset($_GET['a']) ? strtolower($_GET['a']) : 'index';

// EVITAR LLAMAR MÉTODOS MÁGICOS DE PHP (__construct, __destruct, __call, __toString, etc.)
if (strpos($actionName, '__') === 0) {
    http_response_code(400);
    exit;
}

// Sanitizar: solo letras, números y guion bajo
$controllerName = preg_replace('/[^a-z0-9_]/', '', $controllerName);
$actionName     = preg_replace('/[^a-z0-9_]/', '', $actionName);

// Si quedó vacío tras sanitizar, usar valores por defecto
if ($controllerName === '') $controllerName = 'dashboard';
if ($actionName === '')     $actionName = 'index';

// Si no está logueado y no está en auth. Responder JSON para peticiones AJAX, redirigir al login para páginas normales.
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if (!isset($_SESSION['user_id']) && $controllerName !== 'auth') {
    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Su sesión ha expirado. Inicie sesión nuevamente.']);
        exit;
    }

    header('Location: ' . BASE_URL . '?c=auth&a=login');
    exit;
}

// Convertir 'tipos_habitacion' a 'TiposHabitacion' (Soporta snake_case y lo convierte a CamelCase)
$formattedControllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $controllerName)));

// Resolver controlador
$className = $formattedControllerName . 'Controller';
$controllerFile = __DIR__ . '/controllers/' . $className . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    exit;
}

require_once $controllerFile;

if (!class_exists($className)) {
    http_response_code(500);
    exit;
}

$controller = new $className();

if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    exit;
}

// Ejecutar acción
$controller->$actionName($pdo);