<?php

declare(strict_types=1);

class IconBuilder
{
    private const string ICON_SPRITE = 'icons-sprite.svg';

    public function __construct(private readonly HtmlBuilder $htmlBuilder)
    {
    }

    public function createIcon(string $icon, string $ariaLabel, array $additionalClasses = [], ?string $desc = null): AbstractHtmlComponent
    {
        $classes = array_merge(['icon'], $additionalClasses);
        $form = $this->htmlBuilder;
        return $form->tag('svg')
            ->class(...$classes)
            ->ariaLabel($ariaLabel)
            ->role('img')
            ->add(
                $desc !== null ? $form->tag('desc')->content($desc) : null,
                $form->tag('use')->href($this->getMediaIconUrl($icon)),
            );
    }

    public function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel): AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        return $form->tag('span')
            ->class($wrapperClass)
            ->add($this->createIcon($icon, $ariaLabel));
    }

    private function getMediaIconUrl(string $icon): string
    {
        return '/public/assets/img/' . self::ICON_SPRITE . '#' . $icon;
    }
}