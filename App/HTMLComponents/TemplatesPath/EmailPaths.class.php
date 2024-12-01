<?php

declare(strict_types=1);

class EmailPaths implements TemplatePathsInterface
{
    private string $viewPath = VIEW . 'client' . DS . 'components' . DS . 'Email' . DS;
    private string $templatePath = APP . 'Display' . DS . 'Components' . DS . 'Email' . DS . 'Templates' . DS;
    private string $cssPath = ASSET . 'css' . DS . 'client' . DS . 'components' . DS . 'email' . DS;
    private string $layoutPath;
    private string $txtPath;

    public function __construct()
    {
        $this->layoutPath = $this->templatePath . 'Layout' . DS;
        $this->txtPath = $this->templatePath . 'Text' . DS;
    }

    public function Paths() : CollectionInterface
    {
        return new Collection(array_merge($this->templates(), $this->viewFiles(), $this->cssFiles(), $this->txtFiles()));
    }

    private function templates() : array
    {
        return [
            'standardHeaderPath' => $this->layoutPath . 'Standard' . DS . 'header.php',
            'standardFooterPath' => $this->layoutPath . 'Standard' . DS . 'footer.php',
        ];
    }

    private function viewFiles() : array
    {
        return [
            'welcomePath' => $this->viewPath . 'welcomeTemplate.php',
        ];
    }

    private function cssFiles() : array
    {
        return [
            'mainCssPath' => $this->cssPath . 'main.css',
        ];
    }

    private function txtFiles() : array
    {
        return [
            'welcomeContent' => $this->txtPath . 'welcomeContent.txt',
            'verifyAccount' => $this->txtPath . 'verifyAccount.txt',
            'completeProfile' => $this->txtPath . 'completeProfile.txt',
            'startPurchase' => $this->txtPath . 'startPurchase.txt',
        ];
    }
}