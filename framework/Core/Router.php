<?php

namespace Framework\Core;


class Router
{
    private array $routes = [];


    public function get(string $uri, array|callable $action)
    {
        $this->addRoute('GET', $uri, $action);
    }


    public function post(string $uri, array|callable $action)
    {
        $this->addRoute('POST', $uri, $action);
    }


    private function addRoute(string $method, string $uri, array|callable $action)
    {
        $this->routes[$method][$this->normalize($uri)] = $action;
    }


    private function normalize(string $uri): string
    {
        return rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
    }


    public function dispatch(Request $request)
    {
        $method = $request->method();
        $uri = $this->normalize($request->uri());


        $action = $this->routes[$method][$uri] ?? null;


        if (!$action) {
            return Response::json(['error' => 'Not Found'], 404);
        }


        if (is_array($action)) {
            [$controllerClass, $methodName] = $action;
            $controller = new $controllerClass();
            return $controller->$methodName($request);
        }


        return $action($request);
    }
}
