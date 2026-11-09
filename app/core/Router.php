<?php
/**
 * CORE: Router - maps a URL and HTTP method to a controller action.
 *
 * The URL table itself is in app/config/routes.php.
 */
class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, string $action): void
    {
        // Register a page that opens from a normal link (GET)
        $this->routes['GET'][trim($path, '/')] = $action;
    }

    public function post(string $path, string $action): void
    {
        // Register a form submission (POST)
        $this->routes['POST'][trim($path, '/')] = $action;
    }

    public function dispatch(string $url, string $method): void
    {
        // Find the matching route and run its controller method; show the 404 page if none matches
        $url = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if (!isset($this->routes[$method][$url])) {
            http_response_code(404);
            require APP_ROOT . '/views/errors/404.php';
            return;
        }

        [$controllerName, $action] = explode('@', $this->routes[$method][$url]);
        $controller = new $controllerName();
        $controller->$action();
    }
}
