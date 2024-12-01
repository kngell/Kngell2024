<?php

declare(strict_types=1);

class PaginationPath implements TemplatePathsInterface
{
    private string $templatePath = APP . 'Display' . DS . 'Components' . DS . 'Pagination' . DS . 'Templates' . DS;

    public function Paths(): CollectionInterface
    {
        return new Collection(array_merge($this->templatesPath(), $this->viewPath()));
    }

    private function templatesPath() : array
    {
        return [
            'paginPath' => $this->templatePath . 'paginationTemplate.php',
            'linkPath' => $this->templatePath . 'paginationLinkTemplate.php',
            'previousPath' => $this->templatePath . 'previousLinkTemplate.php',
            'nextPath' => $this->templatePath . 'nextLinkTemplate.php',
            'dotsPath' => $this->templatePath . 'dotsLinkTemplate.php',
        ];
    }

    private function viewPath() : array
    {
        return [

        ];
    }
}
