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

    public function addFormInput(string $formAction, array $formValues, array $formErrors, array $fileData = []): void;

    public function getOldInput(?string $key = null): mixed;

    public function flushForm(string $formAction): array;

    /**
     * Get all the flash messages from the session.
     *
     * @return mixed
     */
    public function get();

    public function getSession(): SessionInterface;
}