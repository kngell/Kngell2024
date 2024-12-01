<?php

declare(strict_types=1);
class Controller
{
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function page() : string
    {
        return '';
    }

    protected function render(string $templatePath, array $context = []) : string
    {
        $pathParts = explode(DS, $templatePath);
        if (count($pathParts) === 1) {
            $templatePath = strtolower(str_replace('Controller', '', $this::class) . DS . $templatePath);
        }
        return $this->view->render($templatePath, $context);
    }

    protected function pageTitle(string $title) : void
    {
        $this->view->pageTitle($title);
    }

    protected function addProperties(array $props) : void
    {
        $this->view->addProperties($props);
    }

    protected function setLayout(string $layout) : void
    {
        $this->view->setLayout($layout);
    }

    protected function view() : View
    {
        return $this->view;
    }
}
