<?php

declare(strict_types=1);

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        parent::__construct('', HttpStatusCode::from($status), array_merge($headers, ['Location' => $url]));
    }

    public function getHeaders(): HeaderMap
    {
        $headers = parent::getHeaders();

        // Ensure Location header is present (case-insensitive check)
        if (!$headers->has(HeaderMap::LOCATION_HEADER)) {
            // Get the location from the parent headers
            $location = parent::getHeaders()->get(HeaderMap::LOCATION_HEADER);
            if ($location) {
                $headers->add(HeaderMap::LOCATION_HEADER, $location);
            }
        }

        return $headers;
    }
}