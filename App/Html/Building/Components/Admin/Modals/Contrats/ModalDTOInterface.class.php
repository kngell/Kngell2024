<?php

declare(strict_types=1);

interface ModalDTOInterface
{
    public function toFormValues(): array;
}