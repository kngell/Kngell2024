<?php

declare(strict_types=1);
// Simple sticky error
$this->flash->add(
    'Database connection failed.',
    FlashType::DANGER,
    FlashOptions::sticky()->withTitle('Critical Error')->toArray(),
);

// Quick success toast
$this->flash->add(
    'Profile saved.',
    FlashType::SUCCESS,
    FlashOptions::quick(2500)->toArray(),
);

// Warning that needs the user to read it
$this->flash->add(
    'Your session expires in 5 minutes.',
    FlashType::WARNING,
    FlashOptions::persistent(15000)
        ->withTitle('Session Notice')
        ->withExtra(['session_id' => $sessionId])
        ->toArray(),
);

// Inside controller
return $this->respondError(
    $isAjax,
    'Deletion session expired. Please try again.',
    $redirectUrl,
    FlashType::WARNING,
    HttpStatusCode::HTTP_BAD_REQUEST,
    flashOptions: FlashOptions::default()
        ->withTitle('Session Expired')
        ->withDuration(8000)
        ->toArray(),
);