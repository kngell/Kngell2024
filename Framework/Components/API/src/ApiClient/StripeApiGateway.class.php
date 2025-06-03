<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

class StripeApiGateway extends AbstractApiGateway
{
    private const array DEFAULT_HEADERS = [
    ];
    private const string STRIPE_VERSION = '2025-03-31.preview';

    private const string URL = 'https://api.stripe.com/v1/customers';

    public function __construct(array $defaultHeaders = self::DEFAULT_HEADERS)
    {
        if (isset($_ENV['GITHUB_ACCESS_TOKEN'])) {
            $defaultHeaders['Authorization'] = 'Bearer ' . $_ENV['GITHUB_ACCESS_TOKEN'];
        }
        parent::__construct(self::URL, $defaultHeaders);
    }

    public function get(string $url = '', array $params = []): ResponseInterface
    {
        return $this->processApiWithCurl([], $params);
    }

    public function post(string $url = '', array $params = []): mixed
    {
        $customer = [
            'name' => 'Akono',
            'email' => 'akono@example.com',
        ];
        return $this->processApiWithCurl([
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($customer),
        ], $params);
    }

    public function put(string $url = '', array $params = []): mixed
    {
        return $this->processApiWithCurl([CURLOPT_CUSTOMREQUEST => 'PUT'], $params);
    }

    public function delete(string $url = '', array $params = []): mixed
    {
        return $this->processApiWithCurl([CURLOPT_CUSTOMREQUEST => 'DELETE'], $params);
    }
}