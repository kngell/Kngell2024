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
        $data = array_merge(['headers' => $this->headers], $params);
        $this->response = $this->client->get($url, $data);
        return json_decode($this->response->getBody()->getContents(), true);
    }

    public function post(string $url = '', array $params = []): array
    {
        $data = array_merge(['headers' => $this->headers], $params);
        $this->response = $this->client->post($url, $data);
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

    /**
     * Get the value of client.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the value of response.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}