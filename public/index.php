<?php
/**
 * Front controller - every request enters the application here.
 */

// ===== 1. Paths and configuration =====
define('APP_ROOT', dirname(__DIR__) . '/app');

require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/core/helpers.php';

// ===== 2. Autoload core classes, controllers and models =====
spl_autoload_register(function ($class) {
    foreach (['core', 'controllers', 'models'] as $folder) {
        $file = APP_ROOT . "/$folder/$class.php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ===== 3. Session (login state) =====
// Secure session cookie: not readable by JavaScript, not sent from other sites
ini_set('session.gc_maxlifetime', (string) Auth::TIMEOUT_SECONDS);
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
Auth::enforceTimeout();

// ===== 4. Routing: load the URL table and run the matching controller =====
$router = new Router();
require_once APP_ROOT . '/config/routes.php';

$router->dispatch($_GET['url'] ?? '', $_SERVER['REQUEST_METHOD']);
