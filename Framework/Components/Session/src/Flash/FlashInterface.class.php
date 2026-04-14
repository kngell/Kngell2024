<?php

declare(strict_types=1);

interface FlashInterface
{
    /**
     * Method for adding a flash message to the session.
     *
     * @param string $message
     * @param null|FlashType $type
     *
     * @return void
     */
    public function add(string $message, ?FlashType $type = null): void;

    public function addFormData(string $formAction, array $formValues = [], array $formErrors = [], array $fileData = []): void;

    public function getFormData(string $formAction): array;

    public function flush(?string $key = null): array;

    /**
     * Get all the flash messages from the session.
     *
     * @return mixed
     */
    public function get();

    public function getSession(): SessionInterface;

    public function addData(string $key, array $data = []): void;

    public function getData(string $key): ?array;

    public function peekData(string $key): ?array;

    public function hasData(string $key): bool;
}