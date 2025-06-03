<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiClient implements ApiClientInterface
{
    protected int $statusCode = 0;
    protected array $responseHeaders = [];
    protected string $responseContent = '';
    protected array $defaultHeaders = [];
    protected Closure $responseHeaderCallback;
    protected string $url;

    public function __construct(string $url, array $defaultHeaders = [])
    {
        $this->defaultHeaders = $defaultHeaders;
        $this->responseHeaderCallback = function ($ch, $header) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            }
            $this->responseHeaders[strtolower(trim($header[0]))] = trim($header[1]);
            return $len;
        };
        $this->url = $url;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    protected function processApiWithGuzzle(array $curlArray, array $params) : ResponseInterface
    {
        $client = new Client();
        if (empty($url)) {
            $url = $this->url;
        }
        if (! empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        $response = $client->request('GET', $url, [
            'headers' => $this->defaultHeaders,
        ]);
        $this->statusCode = $response->getStatusCode();
        $this->responseHeaders = $response->getHeaders();
        return $response;
    }

    protected function processApiWithCurl(array $curlArray, array $params) : mixed
    {
        $ch = curl_init();

        $curlOptions = [CURLOPT_URL => $this->url . (! empty($params) ? '?' . http_build_query($params) : ''),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $_ENV['STRIPE_SECRET_KEY'],
            CURLOPT_HEADERFUNCTION => $this->responseHeaderCallback,
        ];
        if (! empty($curlArray)) {
            foreach ($curlArray as $key => $value) {
                $curlOptions[$key] = $value;
            }
        }
        curl_setopt_array($ch, $curlOptions);
        $response = curl_exec($ch);
        $this->statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $response;
    }
}