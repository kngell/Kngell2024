<?php

declare(strict_types=1);
readonly class Cookie
{
    private const string RESERVED_CHARS_LIST = '=-, \t\r\n\v\f';

    private string $name;
    private string $value;
    private int|null $expires;
    private string|null $path;
    private string|null $domain;
    private bool $secure;
    private bool $httpOnly;
    private SameSite|null $sameSite;

    /**
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param string|null $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param SameSite|null $sameSite
     */
    public function __construct(
        string $name,
        string $value,
        ?int $expires = null,
        ?string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = false,
        ?SameSite $sameSite = null
    ) {
        $this->validateName($name);
        $this->name = $name;
        $this->value = $value;
        $this->expires = $expires;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
    }

    public function getId() : string
    {
        return "{$this->name};{$this->domain};{$this->path}";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getExpires(): ?int
    {
        return $this->expires;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function getSameSite(): ?SameSite
    {
        return $this->sameSite;
    }

    private function validateName(string $name) : void
    {
        if (StringUtils::isBlanc($name)) {
            throw new InvalidArgumentException('The cookie name cannot be empty.');
        }
        if (strpbrk($name, self::RESERVED_CHARS_LIST)) {
            throw new InvalidArgumentException("The cookie name '{$name}' contains invalid characters.");
        }
    }
}
