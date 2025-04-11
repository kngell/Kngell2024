<?php

declare(strict_types=1);

interface FileUploadInterface
{
    public function proceed(bool $uploadRequired = false) : array;
}