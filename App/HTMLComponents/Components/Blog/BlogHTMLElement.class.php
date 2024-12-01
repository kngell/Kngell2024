<?php

declare(strict_types=1);
class BlogHTMLElement extends AbstractHTMLElement
{
    private string $blog = 'blogArea';

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        return [$this->blog => $this->displayBlogArea()];
    }

    private function displayBlogArea() : string
    {
        $blogTemplate = str_replace('{{blog1}}', ImageManager::asset_img('blog' . DS . 'blog1.jpg'), $this->getTemplate('blogAreaPath'));
        $blogTemplate = str_replace('{{blog2}}', ImageManager::asset_img('blog' . DS . 'blog2.jpg'), $blogTemplate);
        return str_replace('{{blog3}}', ImageManager::asset_img('blog' . DS . 'blog3.jpg'), $blogTemplate);
    }
}