<?php

declare(strict_types=1);

class SuperGlobals implements SuperGlobalsInterface
{
    private array $get;
    private array $post;
    private array $cookies;
    private array $files;
    private array $server;
    private array $request;

    public function __construct()
    {
        $this->get = $this->sanitizeGetParameters($_GET);
        $this->post = $this->sanitizeArray($_POST, 'htmlspecialchars');
        $this->cookies = $this->sanitizeArray($_COOKIE, 'htmlspecialchars');
        $this->files = $_FILES;
        $this->server = $this->sanitizeServerVariables($_SERVER);
        $this->request = array_merge($this->get, $this->post);
    }

    public function request(?string $key = null): mixed
    {
        return $this->getVariable($key, $this->request);
    }

    public function get(?string $key = null): mixed
    {
        return $this->getVariable($key, $this->get);
    }

    public function post(?string $key = null): mixed
    {
        return $this->getVariable($key, $this->post);
    }

    public function cookies(?string $key = null): mixed
    {
        return $this->getVariable($key, $this->cookies);
    }

    public function files(?string $key = null): mixed
    {
        return $this->getVariable($key, $this->files);
    }

    public function server(?string $key = null): mixed
    {
        if ($key !== null) {
            $key = strtoupper($key);
            return $this->server[$key] ?? null;
        }
        return $this->server;
    }

    public function emptyGlobals(): void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_COOKIE = [];
        $_FILES = [];

        // Also clear the stored arrays
        $this->get = [];
        $this->post = [];
        $this->request = [];
        $this->cookies = [];
        $this->files = [];
        $this->server = [];
    }

    public function getRaw(?string $key = null): mixed
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }

    public function getPathId(?string $path = null): ?string
    {
        if ($path === null) {
            $path = $this->server('REQUEST_URI') ?? '';
        }

        $path = parse_url($path, PHP_URL_PATH) ?? '';
        $segments = explode('/', trim($path, '/'));
        $id = end($segments);

        if ($id && preg_match('/^[a-zA-Z0-9\-_]+$/', $id)) {
            return $id;
        }

        return null;
    }

    private function sanitizeGetParameters(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeGetParameters($value);
            } elseif (is_string($value)) {
                $result[$key] = preg_replace('/[^a-zA-Z0-9\-_\.\/]/', '', $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private function sanitizeArray(array $data, callable $callback): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value, $callback);
            } elseif (is_string($value)) {
                $result[$key] = $callback($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private function sanitizeServerVariables(array $server): array
    {
        $allowedKeys = [
            'HTTP_HOST', 'HTTP_USER_AGENT', 'HTTP_ACCEPT',
            'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING',
            'HTTPS', 'REQUEST_METHOD', 'REQUEST_URI',
            'QUERY_STRING', 'SCRIPT_NAME', 'SCRIPT_FILENAME',
            'PHP_SELF', 'REMOTE_ADDR', 'REMOTE_PORT',
            'SERVER_NAME', 'SERVER_PORT', 'SERVER_PROTOCOL',
            'REQUEST_TIME', 'REQUEST_TIME_FLOAT',
        ];

        $result = [];
        foreach ($server as $key => $value) {
            if (in_array($key, $allowedKeys, true)) {
                if (is_string($value)) {
                    $result[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    private function getVariable(?string $key, array $array): mixed
    {
        if ($key === null) {
            return $array;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $array;
            foreach ($keys as $segment) {
                if (!isset($value[$segment]) || !is_array($value)) {
                    return null;
                }
                $value = $value[$segment];
            }
            return $value;
        }

        return $array[$key] ?? null;
    }
}