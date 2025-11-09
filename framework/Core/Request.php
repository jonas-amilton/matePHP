<?php

namespace Framework\Core;


class Request
{
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }


    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return strtok($uri, '?') ?: '/';
    }


    public function input(string $key = null)
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }
        if ($key === null) return $data;
        return $data[$key] ?? null;
    }


    public function all(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            $data = $_POST;
        }
        return $data;
    }
}
