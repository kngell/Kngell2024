<?php

declare(strict_types=1);

class FileStorage
{
    /**
     * @return Flatbase
     */
    public function flatDatabase()
    {
        $storage = new Filesystem(STORAGE_PATH . DS . 'Files');
        $flatbase = new Flatbase($storage);
        if ($flatbase) {
            return $flatbase;
        }
    }
}
