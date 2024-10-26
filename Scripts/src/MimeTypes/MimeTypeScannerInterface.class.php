<?php

declare(strict_types=1);

interface MimeTypeScannerInterface
{
    public function scan(MimeTypeInfoMap $map) : void;
}
