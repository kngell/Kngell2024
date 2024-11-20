<?php

declare(strict_types=1);

class SuperGlobals implements SuperGlobalsInterface
{
    private readonly array $_get;
    private readonly array $_post;
    private readonly array $_cookies;
    private readonly array $_files;
    private readonly array $_server;

    public function __construct()
    {
        $this->_get = filter_input_array(INPUT_GET, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $this->_post = filter_input_array(INPUT_POST, FILTER_DEFAULT) ?? [];
        $this->_cookies = filter_input_array(INPUT_COOKIE, FILTER_SANITIZE_SPECIAL_CHARS) ?? [];
        $this->_server = $_SERVER; //filter_input_array(INPUT_SERVER, FILTER_DEFAULT) ?? [];
        $this->_files = filter_var_array($_FILES, FILTER_DEFAULT) ?? [];
    }

    /**
     * Get the value of getVar.
     */
    public function _Get(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->_get);
    }

    /**
     * Get the value of postVar.
     */
    public function _Post(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->_post);
    }

    /**
     * Get the value of cookiesVar.
     */
    public function _Cookies(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->_cookies);
    }

    /**
     * Get the value of filesVar.
     */
    public function _Files(?string $key = null): mixed
    {
        return $this->getVariabale($key, $this->_files);
    }

    /**
     * Get the value of serverVar.
     */
    public function _Server(?string $key = null): mixed
    {
        if (null != $key) {
            if (! isset($this->_server[strtoupper($key)])) {
                return '';
            }
            return $this->_server[strtoupper($key)];
        }
        return array_map('strip_tags', $this->_server ?? []);
    }

    protected function emptyGlobals() : void
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
        $var['profileUpload'] = array_map('strip_tags', $var['profileUpload'] ?? []);
        return $var;
    }
}