<?php

declare(strict_types=1);

interface FlashInterface
{
    /**
     * Method for adding a flash message to the session.
     *
     * @param string $message
     * @param string|null $type
     * @return void
     */
    public function add(string $message, ?string $type = null) : void;

    /**
     * Get all the flash messages from the session.
     *
     * @return mixed
     */
    public function get();
}