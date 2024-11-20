<?php

declare(strict_types=1);

class BaseView implements ViewInterface
{
    private ViewEnvironment $viewEnv;
    private string $_head;
    private string $_body;
    private string $_footer;
    private string $_outputBuffer;
    private string $_html;
    private string $_pageTitle = '';
    private string $_layout = 'default';
    private array $properties = [];

    public function __construct(ViewEnvironment $viewEnv)
    {
        $this->viewEnv = $viewEnv;
    }

    public function render(string $templatePath, array $context = []): string
    {
        $templatePath = $this->viewEnv->getFile($templatePath);
        if (! $templatePath) {
            throw new ViewException('This view does not exist');
        }
        try {
            return $this->renderViewContent($templatePath, $context);
        } catch (ViewException $ex) {
            throw $ex;
        }
    }

    public function pageTitle(string $title) : string
    {
        return $this->_pageTitle = $title;
    }

    public function setLayout(string $layout) : void
    {
        $this->_layout = $layout;
    }

    public function getPageTitle() : string
    {
        return $this->_pageTitle;
    }

    public function addProperties(array $props) : void
    {
        foreach ($props as $name => $prop) {
            $this->properties[$name] = $prop;
        }
    }

    private function renderViewContent(string $templatePath, $context) : string
    {
        extract($context, EXTR_SKIP);
        require_once $templatePath;
        $layout = $this->viewEnv->getLayout($this->_layout);
        if ($layout) {
            $this->start('html');
            require_once $layout;
            $this->end();
        }
        return $this->content('html');
    }

    private function css(string $path) : string
    {
        return $this->viewEnv->getCss($path);
    }

    private function js(string $path) : string
    {
        return $this->viewEnv->getJs($path);
    }

    private function start(string $type) : void
    {
        $this->_outputBuffer = $type;
        ob_start();
    }

    private function end() : void
    {
        isset($this->_outputBuffer) ? $this->{'_' . $this->_outputBuffer} = ob_get_clean() : '';
    }

    private function content(string $type) : bool|string
    {
        return match ($type) {
            'head' => $this->_head ?? '',
            'body' => $this->_body ?? '',
            'footer' => $this->_footer ?? '',
            'html' => $this->_html ?? '',
            default => throw new ViewException('no content to display')
        };
    }
}