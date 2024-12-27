<?php

declare(strict_types=1);

class SuperGlobals implements SuperGlobalsInterface
{
    private readonly array $__get;
    private readonly array $__post;
    private readonly array $__cookies;
    private readonly array $__files;
    private readonly array $__server;
    private readonly array $__request;

    public function __construct()
    {
        $this->__get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $this->__post = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? [];
        $this->__cookies = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $this->__server = $_SERVER; //filter_input_array(INPUT_SERVER, FILTER_DEFAULT) ?? [];
        $this->__files = filter_var_array($_FILES, FILTER_DEFAULT) ?? [];
        $this->__request = $_REQUEST;
    }

    public function request(?string $key = null) : mixed
    {
        return $this->getVariabale($key, $this->__request);
    }

    public function get(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->__get);
    }

    /**
     * Get the value of postVar.
     */
    public function post(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->__post);
    }

    public function cookies(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->__cookies);
    }

    /**
     * Get the value of filesVar.
     */
    public function files(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->__files);
    }

    public function server(?string $key = null): mixed
    {
        if (null != $key) {
            if (! isset($this->__server[strtoupper($key)])) {
                return '';
            }
            return $this->__server[strtoupper($key)];
        }
        return array_map('strip_tags', $this->__server ?? []);
    }

    public function emptyGlobals() : void
    {
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_COOKIE = [];
        $_FILES = [];
    }

    private function getVariabale(?string $key, array $var) : mixed
    {
        if ($var == []) {
            return [];
        }
        if (null != $key) {
            return $var[$key] ?? null;
        }
        // $var['profileUpload'] = array_map('strip_tags', $var['profileUpload'] ?? []);
        return $var;
    }
}