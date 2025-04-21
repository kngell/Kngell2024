<?php

declare(strict_types=1);

use Twig\Environment;

#[Service]
readonly class TwigView implements ViewInterface
{
    public function __construct(private Environment $twigEnv)
    {
    }

    public function render(string $templatePath, array $templateVars): string
    {
        return $this->twigEnv->render($templatePath, $templateVars);
    }
}