<?php

declare(strict_types=1);

final class ModelResult
{
    private bool $success;
    private mixed $data;
    private ?string $message;
    private ?int $code;

    private function __construct(bool $success, mixed $data = null, ?string $message = null, ?int $code = null)
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->code = $code;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isError(): bool
    {
        return !$this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getDataAsArray(): array
    {
        return is_array($this->data) ? $this->data : [];
    }

    public function getDataAsString(): string
    {
        return (string) $this->data;
    }

    public function getDataAsInt(): int
    {
        return (int) $this->data;
    }

    public function getDataAsBool(): bool
    {
        return (bool) $this->data;
    }

    public function getInsertedId(): ?int
    {
        if (is_array($this->data) && isset($this->data['inserted_id'])) {
            return (int) $this->data['inserted_id'];
        }

        if (is_numeric($this->data)) {
            return (int) $this->data;
        }

        return null;
    }

    public function getAffectedRows(): int
    {
        if (is_array($this->data) && isset($this->data['affected_rows'])) {
            return (int) $this->data['affected_rows'];
        }

        return 0;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'code' => $this->code,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public static function success(mixed $data = null, ?string $message = null, ?int $code = null): self
    {
        return new self(true, $data, $message, $code);
    }

    public static function error(?string $message = null, ?int $code = null, mixed $data = null): self
    {
        return new self(false, $data, $message, $code);
    }

    public static function fromException(Throwable $e, ?string $customMessage = null): self
    {
        return new self(
            false,
            null,
            $customMessage ?? $e->getMessage(),
            $e->getCode(),
        );
    }
}