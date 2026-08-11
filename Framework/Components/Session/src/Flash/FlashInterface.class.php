<?php

declare(strict_types=1);

interface FlashInterface
{
    /**
     * Add a flash message to the session.
     *
     * @param string         $message
     * @param FlashType|null $type
     * @param array          $options  Optional: title, duration, dismissible, showProgress, extra
     */
    public function add(string $message, ?FlashType $type = null, array $options = []): void;

    /**
     * Get and clear all flash messages.
     * Returns array of message structs (not HTML).
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array;

    /**
     * Peek at messages without consuming them.
     *
     * @return array<int, array<string, mixed>>
     */
    public function peek(): array;

    /**
     * Whether any flash messages are pending.
     */
    public function has(): bool;

    public function addFormData(string $formAction, array $formValues = [], array $formErrors = [], array $fileData = []): void;

    public function getFormData(string $formAction): array;

    public function flush(?string $key = null): array;

    public function getSession(): SessionInterface;

    public function addData(string $key, array $data = []): void;

    public function getData(string $key): ?array;

    public function removeData(string $key): void;

    public function peekData(string $key): ?array;

    public function hasData(string $key): bool;

    public function addFlag(FlashFlagKey $flag): void;

    public function hasFlag(FlashFlagKey $flag): bool;

    public function consumeFlag(FlashFlagKey $flag): bool;

    public function getAllFlags(): array;
}