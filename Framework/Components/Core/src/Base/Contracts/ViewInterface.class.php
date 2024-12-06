<?php

declare(strict_types=1);

interface ViewInterface
{
    public function render(string $templatePath, array $templateVars) : string;

    public function pageTitle(string $title) : void;

    public function getPageTitle() : string;

    public function setLayout(string $layout) : void;

    public function addProperties(array $props) : void;
}