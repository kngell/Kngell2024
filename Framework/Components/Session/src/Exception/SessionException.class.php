<?php

declare(strict_types=1);

class SessionException extends BaseException
{
    public string $message = 'An exception was thrown in retrieving the key from the session storage.';
}