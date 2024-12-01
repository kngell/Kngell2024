<?php

declare(strict_types=1);

class NavigationPath implements TemplatePathsInterface
{
    private string $navPath = VIEW . 'client' . DS . 'components' . DS . 'navigation' . DS;
    private string $templatePath = APP . 'Display' . DS . 'Components' . DS . 'Navigation' . DS . 'Templates' . DS;

    public function Paths() : CollectionInterface
    {
        return new Collection(array_merge($this->templates(), $this->files()));
    }

    private function templates() : array
    {
        return [
            'menuConnexionPath' => $this->templatePath . 'menuConnexionTemplate.php',
        ];
    }

    private function files() : array
    {
        return [
            'navPath' => $this->navPath . '_nav.php',
            'conectPath' => $this->navPath . '_connexion.php',
            'navBrandPath' => $this->navPath . '_navbar_brand.php',
            'settingsPath' => $this->navPath . '_settings.php',
        ];
    }
}
