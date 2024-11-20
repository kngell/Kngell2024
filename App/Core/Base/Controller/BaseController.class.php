<?php

declare(strict_types=1);
class BaseController
{
    private BaseView $view;

    public function __construct(BaseView $view)
    {
        $this->view = $view;
    }

    protected function render(string $templatePath, array $context = []) : string
    {
        $pathParts = explode(DS, $templatePath);
        if (count($pathParts) === 1) {
            $templatePath = strtolower($this::class) . DS . $templatePath;
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

    protected function view() : BaseView
    {
        return $this->view;
    }
}