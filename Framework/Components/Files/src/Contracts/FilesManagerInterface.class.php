<?php

declare(strict_types=1);

interface FilesManagerInterface
{
    public function validate();

    public function getFileName() : string;

    public function getDestinationPath() : string;

    public function getSourcePath() : string;

    public function getTargetDir() : string;

    public static function asset_img($img = '') : string;
}
