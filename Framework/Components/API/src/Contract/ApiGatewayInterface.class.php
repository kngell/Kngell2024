<?php

declare(strict_types=1);

interface ApiClientInterface
{
    /**
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function get(string $url = '', array $params = []): mixed;

    /**
     * @param string $url
     * @param array $data
     * @return array
     */
    public function post(string $url = '', array $data = []): array;

    /**
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function put(string $url = '', array $params = []): mixed;

    /**
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function delete(string $url = '', array $params = []): mixed;

    public function getStatusCode(): int;

    public function getResponseHeaders(): array;

    public function getAccessToken() : string;
}