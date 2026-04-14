<?php

// UrlSafeEncodingTrait.php
declare(strict_types=1);

trait UrlSafeEncodingTrait
{
    public function urlSafeEncode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function urlSafeDecode(string $str): string
    {
        return base64_decode(strtr($str, '-_', '+/'));
    }
}