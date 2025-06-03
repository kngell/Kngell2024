<?php

declare(strict_types=1);

class CurlApiGateway implements ApiGatewayInterface
{
    private int $statusCode;
    private array $responseHeaders = [];
    private Closure $responseHeaderCallback;

    public function __construct()
    {
        $this->responseHeaderCallback = function ($ch, $header) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }
            $this->responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        };
    }

    public function get(string $url, array $params = [], array $header = []): mixed
    {
        $ch = curl_init();

        if (! empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $header,
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

    public function post(string $url, array $params = []): mixed
    {
        return '';
    }

    public function put(string $url, array $params = []): mixed
    {
        return '';
    }

    public function delete(string $url, array $params = []): mixed
    {
        return '';
    }

    /**
     * Get the HTTP status code from the last request.
     *
     * @return int The HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }
}