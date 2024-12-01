<?php

declare(strict_types=1);
class BannerAddsHTMLElement extends AbstractHTMLElement
{
    private string $banner = 'bannerAdds';

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        return [$this->banner => $this->bannerAddsSection()];
    }

    private function bannerAddsSection() : string
    {
        $bannerAddTemplate = $this->getTemplate('bannerAddPath');
        $bannerAddTemplate = str_replace('{{banner1}}', ImageManager::asset_img('banner1-cr-500x150.jpg'), $bannerAddTemplate);
        return str_replace('{{banner2}}', ImageManager::asset_img('banner2-cr-500x150.jpg'), $bannerAddTemplate);
    }
}