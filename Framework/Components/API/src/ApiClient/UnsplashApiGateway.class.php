<?php

declare(strict_types=1);

class UnsplashApiGateway extends AbstractApiGateway
{
    private const array DEFAULT_HEADERS = [
        'Authorization: Client-ID hWRHyuwotse2ZocmrcIT3tk_wJfXfMzwizBW3K5nCNI',
    ];
    private string $url = 'https://api.unsplash.com/photos/random';

    public function __construct(array $defaultHeaders = self::DEFAULT_HEADERS)
    {
        parent::__construct($defaultHeaders);
    }

    public function get(string $url = '', array $params = []): mixed
    {
        $ch = curl_init();
        if (empty($url)) {
            $url = $this->url;
        }
        if (! empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_HEADERFUNCTION => $this->responseHeaderCallback,
        ]);

        $response = curl_exec($ch);

        $this->statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (empty($response)) {
            throw new RuntimeException('Empty response from API');
        }

        return $response;
    }

    public function post(string $url = '', array $params = []): mixed
    {
        return '';
    }

    public function put(string $url = '', array $params = []): mixed
    {
        return '';
    }

    public function delete(string $url = '', array $params = []): mixed
    {
        return '';
    }
}