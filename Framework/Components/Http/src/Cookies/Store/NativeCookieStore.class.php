<?php

declare(strict_types=1);

class NativeCookieStore extends AbstractCookieStore
{
    public function __construct(CookieEnvironment $cookieEnvironment, SuperGlobalsInterface $gv)
    {
        parent::__construct($cookieEnvironment, $gv);
    }

    public function getCookie(string $name) : mixed
    {
        if ($this->exists($name)) {
            return $this->gv->cookies($name);
        }
    }

    /**
     * @inheritdoc
     *
     * @param mixed $value
     * @param null|array $attributes
     * @return self
     */
    public function exists(string $name = ''): bool
    {
        $CookieName = $name == '' ? $this->cookieEnvironment->getCookieName() : $name;
        return array_key_exists($CookieName, $this->gv->cookies());
    }

    /**
     * @inheritdoc
     * @param mixed $value
     * @return self
     */
    public function setCookie(mixed $value, ?string $cookieName = null): void
    {
        setcookie($cookieName === null ? $this->cookieEnvironment->getCookieName() : $cookieName, $value, $this->cookieEnvironment->getExpiration(), $this->cookieEnvironment->getPath(), $this->cookieEnvironment->getDomain(), $this->cookieEnvironment->isSecure(), $this->cookieEnvironment->isHttpOnly());
    }

    /**
     * @inheritdoc
     * @return self
     */
    public function deleteCookie(string|null $cookieName = null): void
    {
        setcookie(($cookieName != null) ? $cookieName : $this->cookieEnvironment->getCookieName(), '', (time() - 3600), $this->cookieEnvironment->getPath(), $this->cookieEnvironment->getDomain(), $this->cookieEnvironment->isSecure(), $this->cookieEnvironment->isHttpOnly());
    }
}