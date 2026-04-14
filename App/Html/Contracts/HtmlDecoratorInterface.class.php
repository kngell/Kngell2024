<?php

declare(strict_types=1);

interface HtmlDecoratorInterface
{
    public function getTarget(): Controller;
}