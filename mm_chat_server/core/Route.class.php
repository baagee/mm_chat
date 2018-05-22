<?php
namespace core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class Route
{
    private static $allowMethods = ['GET', 'POST', 'PUT', 'OPTIONS', 'DELETE'];
    private static $middlewareMap = [];

    private static function addRoute($rules, RouteCollector $r)
    {
        foreach ($rules as $rule) {
            $r->addRoute($rule[1], $rule[0], $rule[2]);
            if (isset($rule[3]) && !empty($rule[3])) {
                self::$middlewareMap[$rule[2]] = $rule[3];
            }
        }
    }

    public static function __init__()
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            $rules = include_once APP_DIR . '/routes.php';
            self::addRoute($rules, $r);
        });
        return self::dispatch($dispatcher);
    }

    protected static function dispatch($dispatcher)
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($httpMethod, self::$allowMethods)) {
            ob_end_clean();
            header("HTTP/1.1 405 Not Allowed");
            exit();
        }
        $uri = $_SERVER['REQUEST_URI'];
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                ob_end_clean();
                header("HTTP/1.1 404 Not Found");
                exit();
            case Dispatcher::METHOD_NOT_ALLOWED:
                ob_end_clean();
                header("HTTP/1.1 405 Not Allowed");
                $methods = implode(', ', $routeInfo[1]);
                header("Access-Control-Allow-Methods: " . $methods);
                exit();
            case Dispatcher::FOUND:
                header("Access-Control-Allow-Methods: " . $httpMethod);
                $handler = trim($routeInfo[1], '/');
                $h_array = explode('/', $handler);
                $vars = $routeInfo[2];
                $params = json_decode(file_get_contents('php://input'), true)??[];
                $params = array_merge($vars, $_GET, $_POST, $params);
                // 处理中间件
                $return = Middleware::execBeforeMiddleware($params, $routeInfo,self::$middlewareMap);
                $return['h_array'] = $h_array;
                return $return;
            default :
                ob_end_clean();
                header("HTTP/1.1 404 Not Found");
                exit();
        }
    }
}