<?php

declare(strict_types=1);

class Hash implements HashInterface
{
    private array $config;
    private string $cipherMethod = '';

    public function __construct(array|Closure $config)
    {
        if ($config instanceof Closure) {
            $this->config = call_user_func($config);
        } else {
            $this->config = $config;
        }
    }

    public function password(string $password): string
    {
        return password_hash(
            $password,
            constant($this->config['password_algo']['default']),
            $this->config['hash_cost_factor']
        );
    }

    public function passwordCheck(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function hash(string $input) : string
    {
        return hash('sha256', $input);
    }

    public function hashCheck(string $knownHash, string $userHash) : bool
    {
        $userHash = $this->hash($userHash);
        return hash_equals($knownHash, $userHash);
    }

    public function encrypt(string $string) : string
    {
        return '';
    }

    public function decrypt(string $string) : string
    {
        return '';
    }
}