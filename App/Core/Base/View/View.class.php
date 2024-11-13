<?php

declare(strict_types=1);
class View implements ViewInterface
{
    private ViewEnvironment $viewEnv;
    private string $_head;
    private string $_body;
    private string $_footer;
    private string $_outputBuffer;

    public function __construct(ViewEnvironment $viewEnv)
    {
        $this->viewEnv = $viewEnv;
    }

    public function render(string $templatePath, array $templateVars): string
    {
        $templatePath = $this->viewEnv->validate($templatePath);
        if (! $templatePath) {
            throw new ViewException('This view does not exist');
        }
        try {
            return $this->renderViewContent($templatePath, $templateVars);
        } catch (ViewException $ex) {
            throw $ex;
        }
    }

    private function renderViewContent(string $templatePath, $templateVars) : string
    {
        extract($templateVars, EXTR_SKIP);
        require_once $templatePath;
        $this->start('html');
        $layout = $this->viewEnv->getLayout();
        if ($layout) {
            require_once $layout;
        }
        $this->end();
        return $this->content('html');
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
