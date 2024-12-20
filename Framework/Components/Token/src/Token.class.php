<?php

declare(strict_types=1);
class Token extends RandomStringGenerator implements TokenInterface
{
    private const string CSRF_KEY = 'UYUZYZ3121331azeazesfKngell';

    public function __construct(private SessionInterface $session)
    {
    }

    public function create(int $length = CSRF_TOKEN_LENGHT, string $frm = '', string $alphabet = '') : string
    {
        $identifiant = '';
        $this->setAlphabet($alphabet);
        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $this->alphabetLength);
            $identifiant .= $this->alphabet[$randomKey];
        }
        $time = $this->tokenTime(self::CSRF_KEY);
        $separator = ! empty($frm) ? $frm : '|';
        $hash = hash_hmac('sha256', session_id() . $identifiant . $time . $frm, CSRF_TOKEN_SECRET, true);
        return $this->urlSafeEncode($hash . $separator . $identifiant . $separator . $time);
    }

    public function validate(string $token = '', string $frm = '') : bool
    {
        $separator = ! empty($frm) ? $frm : '|';
        $part = explode($separator, $this->urlSafeDecode($token));
        if (count($part) === 3) {
            $hash = hash_hmac('sha256', session_id() . $part[1] . $part[2] . $frm, CSRF_TOKEN_SECRET, true);
            if (hash_equals($hash, $part[0])) {
                return true;
            }
        }
        return false;
    }

    public function isTokenTimeValid() : bool
    {
        if ($this->session->exists(self::CSRF_KEY)) {
            $timeOk = time() - $this->session->get(self::CSRF_KEY) > CSRF_TOKEN_LIFETIME;
            $this->session->delete(self::CSRF_KEY);
            return $timeOk;
        }
        return false;
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

    public function check($formName, $token)
    {
        if ($this->session->exists(TOKEN_NAME) && $token === $this->session->get(TOKEN_NAME)) {
            $this->session->delete(TOKEN_NAME);
            return true;
        }
        $serverToken = hash_hmac('sha256', $formName, $this->session->exists(TOKEN_NAME) ? $this->session->get(TOKEN_NAME) : '');

        return hash_equals($serverToken, $token);
    }

    public function getHash() : string
    {
        return hash_hmac('sha256', '$this->token', YamlFile::get('app')['settings']['secret_key']);
    }

    public function urlSafeEncode(string $str) : string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function urlSafeDecode(string $str) : string
    {
        // $st = base64_decode(strtr($str, '-_', '+/'));
        return base64_decode(strtr($str, '-_', '+/'));
    }

    private function tokenTime(string $key) : int
    {
        $time = time();
        if (! $this->session->exists($key)) {
            $this->session->set($key, $time);
        }
        return $time;
    }
}