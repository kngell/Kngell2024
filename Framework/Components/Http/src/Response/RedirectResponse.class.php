<?php

declare(strict_types=1);

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        parent::__construct('', HttpStatusCode::from($status), array_merge($headers, ['Location' => $url]));
    }
}
