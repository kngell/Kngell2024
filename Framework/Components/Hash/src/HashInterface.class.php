<?php

declare(strict_types=1);

interface HashInterface
{
    public function password(string $password) : string;

    public function passwordCheck(string $password, string $hash) : bool;

    public function hash(string $input) : string;

    public function hashCheck(string $knownHash, string $userHash) : bool;
}