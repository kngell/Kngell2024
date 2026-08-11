<?php

declare(strict_types=1);

final class CartOperationResult
{
    private function __construct(
        public readonly bool $success,
        public readonly string $operation,
        public readonly ?int $cartId = null,
        public readonly ?string $message = null,
        public readonly ?string $error = null,
        public readonly array $data = [],
        public readonly ?int $affectedRows = null,
        public readonly bool $wasSkipped = false,
    ) {
    }

    // ─── Helper Methods ───────────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function isSkipped(): bool
    {
        return $this->wasSkipped;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getMessage(): ?string
    {
        return $this->message ?? $this->error;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'operation' => $this->operation,
            'cartId' => $this->cartId,
            'message' => $this->getMessage(),
            'error' => $this->error,
            'data' => $this->data,
            'affectedRows' => $this->affectedRows,
            'wasSkipped' => $this->wasSkipped,
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    // ─── Success Factory Methods ──────────────────────────────────

    public static function saveSuccess(
        int $cartId,
        string $message = 'Cart saved successfully',
        array $data = [],
        int $affectedRows = 0,
    ): self {
        return new self(
            success: true,
            operation: 'save',
            cartId: $cartId,
            message: $message,
            data: $data,
            affectedRows: $affectedRows,
        );
    }

    public static function deleteSuccess(
        int $cartId,
        string $message = 'Cart deleted successfully',
        array $data = [],
        int $affectedRows = 0,
    ): self {
        return new self(
            success: true,
            operation: 'delete',
            cartId: $cartId,
            message: $message,
            data: $data,
            affectedRows: $affectedRows,
        );
    }

    public static function mergeSuccess(
        int $cartId,
        string $message = 'Carts merged successfully',
        array $data = [],
        int $affectedRows = 0,
    ): self {
        return new self(
            success: true,
            operation: 'merge',
            cartId: $cartId,
            message: $message,
            data: $data,
            affectedRows: $affectedRows,
        );
    }

    public static function clearSuccess(
        int $cartId,
        string $message = 'Cart cleared successfully',
        array $data = [],
        int $affectedRows = 0,
    ): self {
        return new self(
            success: true,
            operation: 'clear',
            cartId: $cartId,
            message: $message,
            data: $data,
            affectedRows: $affectedRows,
        );
    }

    // ─── Failure Factory Methods ──────────────────────────────────

    public static function failure(
        string $operation,
        string $error,
        array $data = [],
        bool $wasSkipped = false,
    ): self {
        return new self(
            success: false,
            operation: $operation,
            error: $error,
            data: $data,
            wasSkipped: $wasSkipped,
        );
    }

    public static function notFound(
        string $operation,
        string $message = 'Cart not found',
        array $data = [],
    ): self {
        return new self(
            success: false,
            operation: $operation,
            message: $message,
            error: 'Not found',
            data: $data,
        );
    }

    public static function skipped(
        string $operation,
        string $message = 'Operation skipped',
        array $data = [],
    ): self {
        return new self(
            success: true,
            operation: $operation,
            message: $message,
            data: $data,
            wasSkipped: true,
        );
    }
}