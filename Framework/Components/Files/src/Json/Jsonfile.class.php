<?php

declare(strict_types=1);
class JsonFile
{
    public function getContent(string $file) : string
    {
        return json_decode(file_get_contents($file), true);
    }
}
