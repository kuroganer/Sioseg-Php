<?php

namespace App\Core;

class Router
{
    protected array $routes = [];

    public function addRoute(string $method, string $uri, string $action): void
    {
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        $uriPattern = preg_quote($uri, '#');
        $uriPattern = preg_replace('/\\\{([a-zA-Z0-9_]+)\\\}/', '(?P<$1>[^/]+)', $uriPattern);
        $uriPattern = '#^' . $uriPattern . '/?$#';

        $this->routes[$method][$uriPattern] = $action;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): URI original: " . $uri);

        // --- REMOÇÃO CORRETA DO SUBDIRECTORY ---
       $subdirectory = defined('SUBDIRECTORY') ? rtrim(SUBDIRECTORY, '/') : '';
if (!empty($subdirectory)) {
    if (strpos($uri, $subdirectory) === 0) {
        $uri = substr($uri, strlen($subdirectory));
        if ($uri === '') {
            $uri = '/';
        }
    }
}
        error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): URI após remover SUBDIRECTORY: " . $uri);

        // Normaliza a URI
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): URI final normalizada para roteamento: " . $uri);

        if (!isset($this->routes[$method])) {
            error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): Nenhuma rota definida para o método " . $method);
            $this->handleNotFound();
            return;
        }

        foreach ($this->routes[$method] as $routePattern => $action) {
            error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): Tentando casar URI '" . $uri . "' com padrão '" . $routePattern . "'");

            if (preg_match($routePattern, $uri, $matches)) {
                $paramsAssociative = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $params = array_values($paramsAssociative);

                error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): Rota encontrada! Chamando " . $action);
                $this->callAction($action, $params);
                return;
            }
        }

        error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::dispatch): Nenhuma rota correspondente encontrada para URI: " . $uri . " e Método: " . $method);
        $this->handleNotFound();
    }

    protected function callAction(string $action, array $params): void
    {
        list($controllerName, $methodName) = explode('@', $action);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::callAction): Classe do controller não encontrada: $controllerClass");
            $this->handleNotFound();
            return;
        }

        $controllerInstance = new $controllerClass();

        if (!method_exists($controllerInstance, $methodName)) {
            error_log(date('[d-M-Y H:i:s e]') . " DEBUG (Router::callAction): Método '$methodName' não encontrado no controller '$controllerClass'.");
            $this->handleNotFound();
            return;
        }

        error_log(date('[d-M-Y H:i:s e]') . " DEBUG: $action foi chamado.");
        call_user_func_array([$controllerInstance, $methodName], $params);
    }

    protected function handleNotFound(): void
    {
        header("HTTP/1.0 404 Not Found");
        if (defined('APP_ROOT') && file_exists(APP_ROOT . '/app/Views/errors/404.php')) {
            require_once APP_ROOT . '/app/Views/errors/404.php';
        } else {
            echo "404 - Página Não Encontrada (Arquivo 404.php ausente)";
        }
    }
}
