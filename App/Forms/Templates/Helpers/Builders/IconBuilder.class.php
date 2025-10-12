<?php

declare(strict_types=1);

class IconBuilder
{
    public function createIcon(FormBuilder $form, string $icon, string $ariaLabel, array $additionalClasses = []): AbstractHtmlComponent
    {
        $classes = array_merge(['icon'], $additionalClasses);

        return $form->tag('svg')
            ->class(...$classes)
            ->ariaLabel($ariaLabel)
            ->role('img')
            ->add(
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
        return '/public/assets/img/' . AbstractForm::ICON_SPRITE . '#' . $icon;
    }
}
