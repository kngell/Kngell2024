<?php

declare(strict_types=1);

// src/Http/MaintenanceResponse.php
class MaintenanceResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            'Service temporarily unavailable',
            HttpStatusCode::HTTP_SERVICE_UNAVAILABLE,
            [
                'Retry-After' => '300', // 5 minutes
                'X-Maintenance-Mode' => 'active',
            ]
        );

        $this->headerManager->applyMaintenanceHeaders();
    }
}