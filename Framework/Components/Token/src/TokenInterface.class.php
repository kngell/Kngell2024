<?php

declare(strict_types=1);

interface TokenInterface
{
    public function create(int $length = 8, string $frm = '', string $alphabet = '') : string;

    public function validate(string $token = '', string $frm = '') : bool;

    public function isTokenTimeValid() : bool;

    public function getSession() : SessionInterface;

    public function urlSafeDecode(string $str) : string;

    public function urlSafeEncode(string $str) : string;
}
