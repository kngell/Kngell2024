<?php

// ObfuscatorInterface.php
declare(strict_types=1);

interface ObfuscatorInterface
{
    public function obfuscate(int $value): string;

    public function deobfuscate(string $value): ?int;

    public function generate(): string;
}