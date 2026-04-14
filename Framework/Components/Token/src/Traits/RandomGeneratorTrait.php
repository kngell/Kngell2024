<?php

// RandomGeneratorTrait.php
declare(strict_types=1);

trait RandomGeneratorTrait
{
    protected function getRandomInteger(int $min, int $max): int
    {
        $range = ($max - $min);
        if ($range < 0) {
            return $min;
        }
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        $filter = (int) (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(random_bytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);

        return $min + $rnd;
    }

    protected function generateRandomString(int $length = 32, string $alphabet = ''): string
    {
        if ('' === $alphabet) {
            $alphabet = implode(range('a', 'z'))
                . implode(range('A', 'Z'))
                . implode(range(0, 9));
        }

        $alphabetLength = strlen($alphabet);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $alphabetLength - 1);
            $result .= $alphabet[$randomKey];
        }

        return $result;
    }
}