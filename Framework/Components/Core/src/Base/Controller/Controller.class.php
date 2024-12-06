<?php

declare(strict_types=1);
abstract class Controller
{
    use ControllerGettersAndSetters;

    protected FormBuilder $formBuilder;
    protected Request $request;
    protected Response $response;
    private ViewInterface $view;

    public function page() : string
    {
        return '';
    }

    protected function makeForm(string $action = '', array $formValues = [], array $formErrors = []) : string
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

    protected function view(string $template, array $data = []) : Response
    {
        return new Response($this->render($template, $data), HttpStatusCode::HTTP_OK);
    }
}