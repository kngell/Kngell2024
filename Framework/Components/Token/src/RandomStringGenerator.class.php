<?php

declare(strict_types=1);
/**
 * Class RandomStringGenerator.
 */
abstract class RandomStringGenerator
{
    /** @var string */
    protected $alphabet;

    /** @var int */
    protected $alphabetLength;

    /**
     * @param string $alphabet
     */
    public function __construct()
    {
    }

    /**
     * @param string $alphabet
     */
    public function setAlphabet($alphabet)
    {
        if ('' !== $alphabet) {
            $this->alphabet = $alphabet;
        } else {
            $this->alphabet =
                implode(range('a', 'z'))
                . implode(range('A', 'Z'))
                . implode(range(0, 9));
        }
        $this->alphabetLength = strlen($this->alphabet);
    }

    public function getAlphabet()
    {
        return $this->alphabet;
    }

    protected function getRandomInteger($min, $max)
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
}
