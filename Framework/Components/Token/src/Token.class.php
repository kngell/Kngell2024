<?php

declare(strict_types=1);
class Token extends RandomStringGenerator implements TokenInterface
{
    private string $token;
    private string $tokenHash;

    public function __construct(string|null $token = null)
    {
        if ($token === null) {
            $this->token = $this->generate();
        } else {
            $this->token = $token;
        }
    }

    public function getCsrfHash(int $length = CSRF_TOKEN_LENGHT, string $frm = '', string $alphabet = '') : string
    {
        $time = time();
        $separator = ! empty($frm) ? $frm : '|';
        $hash = hash_hmac('sha256', session_id() . $this->token . $time . $frm, CSRF_TOKEN_SECRET, true);
        return $this->urlSafeEncode($hash . $separator . $this->token . $separator . $time);
    }

    public function generate(int $length = CSRF_TOKEN_LENGHT, string $alphabet = '') : string
    {
        $token = '';
        $this->setAlphabet($alphabet);
        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
            $token .= $this->alphabet[$randomKey];
        }
        return $token;
    }

    public function validate(array $data) : bool
    {
        $separator = ! empty($frm) ? $frm : '|';
        $part = explode($separator, $this->urlSafeDecode($data['csrfToken'] ?? ''));
        if (count($part) === 3) {
            $hash = hash_hmac('sha256', session_id() . $part[1] . $part[2] . ($data['frm_name'] ?? ''), CSRF_TOKEN_SECRET, true);
            if (hash_equals($hash, $part[0])) {
                $this->tokenHash = $hash;
                return true;
            }
        }
        return false;
    }

    public function urlSafeEncode(string $str) : string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function urlSafeDecode(string $str) : string
    {
        return base64_decode(strtr($str, '-_', '+/'));
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getRememberHash(): string
    {
        return hash_hmac('sha256', $this->token, CSRF_TOKEN_SECRET);
    }
}