<?php

declare(strict_types=1);

readonly class Assets
{
    private array $assets;
    private string $cssTemplate;
    private string $jsTemplate;

    public function __construct(array $assets = [])
    {
        $assetsFile = file_get_contents(dirname(getcwd()) . '/App/assets.json');
        $this->assets = json_decode($assetsFile, true);
        $this->cssTemplate = file_get_contents(dirname(getcwd()) . '/App/Templates/Script/CssTagTemplate.php');
        $this->jsTemplate = file_get_contents(dirname(getcwd()) . '/App/Templates/Script/JsTagTemplate.php');
    }

    public function getCss(string|null $path = null) : string
    {
        $file = $this->getAssets($path);
        if (! empty($file)) {
            return str_replace('{{cssFile}}', $file, $this->cssTemplate);
        }
        return '';
    }

    public function getJs(string|null $path) : string
    {
        if ($path !== null) {
            $file = $this->getAssets($path);
            if (! empty($file)) {
                return str_replace('{{jsFile}}', $file, $this->jsTemplate);
            }
        }
        return '';
    }

    private function getAssets(string|null $path = null) : string
    {
        if (array_key_exists($path, $this->assets)) {
            return $this->assets[$path];
        }
        return '';
    }
}