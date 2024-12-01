<?php

declare(strict_types=1);

class SocialMediaPaths implements TemplatePathsInterface
{
    private string $templatePath = APP . 'Display New' . DS . 'Components' . DS . 'SocialMedia' . DS . 'Templates' . DS;

    public function Paths() : CollectionInterface
    {
        return new Collection(array_merge($this->templates(), $this->files()));
    }

    private function templates() : array
    {
        return [
            'socialMediaPath' => $this->templatePath . 'socialMedia.php',
            'socialMediaLinkPath' => $this->templatePath . 'socialMediaLink.php',
        ];
    }

    private function files() : array
    {
        return [

        ];
    }
}