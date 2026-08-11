<?php

declare(strict_types=1);

interface ModalFormBuilderInterface
{
    public function build(string $action, string $form, FormConfig $config): string;

    public function getIdentier(): string;

    public function setDto(ModalDTOInterface $dto): self;
}