<?php

declare(strict_types=1);

// src/Http/ApiResponse.php
class ApiResponse extends Response
{
    public function __construct(
        mixed $data,
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $headers = [],
        array $cookies = []
    ) {
        parent::__construct(
            json_encode($data),
            $statusCode,
            array_merge($headers, [
                'Content-Type' => 'application/json',
            ]),
            $cookies
        );

        // Force CORS for all API responses
        $this->headerManager->applyCorsHeaders();

        // Special API security headers
        $this->headers->add('X-Content-Type-Options', 'nosniff');
        $this->headers->add('X-API-Version', '1.0');
    }
}