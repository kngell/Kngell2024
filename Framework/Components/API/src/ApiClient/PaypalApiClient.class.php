<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class PaypalApiClient implements ApiClientInterface
{
    private Client $client;
    private string $baseUrl;
    private ResponseInterface $response;
    private array $headers;

    public function __construct()
    {
        $this->baseUrl = $_ENV['PAYPAL_SANDBOX_URL'];
        $this->client = new Client(['base_uri' => $this->baseUrl]);
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    public function get(string $url = '', array $params = []): mixed
    {
    }

    public function post(string $url = '', array $data = []): array
    {
        if (empty($data)) {
            return [];
        }
        $this->response = $this->client->post($url, [
            'headers' => $this->headers,
            'json' => $data,
        ]);

        return json_decode($this->response->getBody()->getContents(), true);
    }

    public function put(string $url = '', array $params = []): mixed
    {
    }

    public function delete(string $url = '', array $params = []): mixed
    {
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getResponseHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getAccessToken() : string
    {
        if (empty($_ENV['PAYPAL_CLIENT_ID']) || empty($_ENV['PAYPAL_CLIENT_SECRET'])) {
            throw new InvalidCredentialsException('PayPal client ID and secret must be set in the environment variables.');
        }
        $this->response = $this->client->post($_ENV['PAYPAL_SANDBOX_OAUTH2'], [
            'auth' => [$_ENV['PAYPAL_CLIENT_ID'], $_ENV['PAYPAL_CLIENT_SECRET']],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
            ],
        ]);

        $data = json_decode($this->response->getBody()->getContents(), true);
        return $data['access_token'] ?? '';
    }

    public function createOrder(array $orderData): array
    {
        $accessToken = $this->getAccessToken();

        $this->response = $this->client->post('/v2/checkout/orders', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $orderData,
        ]);

        return json_decode($this->response->getBody()->getContents(), true);
    }
}