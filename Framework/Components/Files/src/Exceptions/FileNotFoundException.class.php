<?php

declare(strict_types=1);

class FileNotFoundException extends FilesException
{
    public function __construct(string $path = '')
    {
        parent::__construct(sprintf('The file "%s" does not exissts', $path));
    }
}
