<?php

declare(strict_types=1);

interface ViewInterface
{
    public function render(string $templatePath, array $templateVars): string;

    public function pageTitle(string $title): void;

    public function getPageTitle(): string;

    public function setLayout(NavbarType $layout): void;

    public function addProperties(array $props): void;

    public function getLayout(): NavbarType;

    public function setToken(TokenInterface $token): void;

    public function setRequest(Request $request): void;

    public function getPath(): string;

    public function formatBytes(int $bytes): string;
}