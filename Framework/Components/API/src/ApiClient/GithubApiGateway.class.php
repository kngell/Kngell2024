<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

class GithubApiGateway extends AbstractApiGateway
{
    private const array DEFAULT_HEADERS = [
        // 'Accept: application/vnd.github+json',
        // 'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent' => 'kngell',
    ];
    // 'https://api.github.com/user/starred/kngell/kngell2024'
    // 'https://api.github.com/user/repos'
    private const string URL = 'https://api.github.com/user/repos';

    public function __construct(array $defaultHeaders = self::DEFAULT_HEADERS)
    {
        if (isset($_ENV['GITHUB_ACCESS_TOKEN'])) {
            $defaultHeaders['Authorization'] = 'Bearer ' . $_ENV['GITHUB_ACCESS_TOKEN'];
        }
        parent::__construct(self::URL, $defaultHeaders);
    }

    public function get(string $url = '', array $params = []): ResponseInterface
    {
        return $this->processApiRequest([], $params);
    }

    public function post(string $url = '', array $params = []): ResponseInterface
    {
        $payload = json_encode([
            'name' => 'kngell2025',
            'description' => 'This is a test repository created via API',
        ]);
        return $this->processApiRequest([
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $payload,
        ], $params);
    }

    public function put(string $url = '', array $params = []): mixed
    {
        return $this->processApiRequest([CURLOPT_CUSTOMREQUEST => 'PUT'], $params);
    }

    public function delete(string $url = '', array $params = []): mixed
    {
        return $this->processApiRequest([CURLOPT_CUSTOMREQUEST => 'DELETE'], $params);
    }
}