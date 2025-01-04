<?php

declare(strict_types=1);

interface TokenInterface
{
    public function getCsrfHash(int $length = 8, string $frm = '', string $alphabet = '') : string;

    public function getRememberHash(): string;

    public function generate(int $length = CSRF_TOKEN_LENGHT, string $alphabet = '') : string;

    public function validate(array $data) : bool;

    public function urlSafeDecode(string $str) : string;

    public function urlSafeEncode(string $str) : string;

    public function getValue(): string;
}