<?php

declare(strict_types=1);

readonly class StatusCode
{
    private int $code;
    private string $message;
    private string $description;

    /**
     * @param int $code
     * @param string $message
     * @param string $description
     */
    public function __construct(int $code, string $message, string $description)
    {
        $this->code = $code;
        $this->message = $message;
        $this->description = $description;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
