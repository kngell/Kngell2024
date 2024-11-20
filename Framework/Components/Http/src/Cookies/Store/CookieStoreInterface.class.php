<?php

declare(strict_types=1);

interface CookieStoreInterface
{
    /**
     * @return bool
     */
    public function exists(): bool;

    /**
     * @param mixed $value
     * @return void
     */
    public function setCookie(mixed $value, ?string $coolieName = null): void;

    /**
     * @param null|string $cookieName
     * @return void
     */
    public function deleteCookie(string|null $cookieName = null): void;

    public function getCookie(string $name) : mixed;
}
