<?php

declare(strict_types=1);

class IconBuilder
{
    public function createIcon(FormBuilder|HtmlBuilder $form, string $icon, string $ariaLabel, array $additionalClasses = [], ?string $desc = null): AbstractHtmlComponent
    {
        $classes = array_merge(['icon'], $additionalClasses);

        return $form->tag('svg')
            ->class(...$classes)
            ->ariaLabel($ariaLabel)
            ->role('img')
            ->add(
                $desc !== null ? $form->tag('desc')->content($desc) : null,
                $form->tag('use')->href($this->getMediaIconUrl($icon)),
            );
    }

    public function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('span')
            ->class($wrapperClass)
            ->add($this->createIcon($form, $icon, $ariaLabel));
    }

    private function getMediaIconUrl(string $icon): string
    {
        return '/public/assets/img/' . AbstractHtml::ICON_SPRITE . '#' . $icon;
    }
}