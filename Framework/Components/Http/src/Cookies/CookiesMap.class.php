<?php

declare(strict_types=1);
class CookiesMap implements Countable, IteratorAggregate
{
    /**
     * @var array<string,Cookie>
     */
    private array $cookies = [];

    /**
     * @param Cookie[] $cookies
     */
    public function __construct(array $cookies = [])
    {
        $this->addAll($cookies);
    }

    public static function createFromCookieGlobals(array $cookieGlobals) : self
    {
        $cookies = [];
        foreach ($cookieGlobals as $name => $value) {
            $cookies[] = new Cookie($name, $value);
        }
        return new self($cookies);
    }

    /**
     * @param Cookie[] $cookies
     * @return void
     */
    public function addAll(array $cookies) : void
    {
        foreach ($cookies as $cookie) {
            $this->add($cookie);
        }
    }

    public function add(Cookie $cookie) : void
    {
        $this->cookies[$cookie->getId()] = $cookie;
    }

    public function get(string $name, string|null $domain = null, string|null $path = null) : Cookie|null
    {
        $checkDomain = ! StringUtils::isBlanc($domain);
        $checkPath = ! StringUtils::isBlanc($path);

        foreach ($this->cookies as $cookie) {
            if ($name !== $cookie->getName()) {
                continue;
            }
            if ($checkDomain && $domain !== $cookie->getDomain()) {
                continue;
            }
            if ($checkPath && $path !== $cookie->getPath()) {
                continue;
            }

            return $cookie;
        }
        return null;
    }

    public function count(): int
    {
        return count($this->cookies);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->cookies);
    }
}
