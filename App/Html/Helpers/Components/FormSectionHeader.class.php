<?php

declare(strict_types=1);

final class FormSectionHeader
{
    public function __construct(private HtmlBuilder $builder, private IconBuilder $iconBuilder)
    {
    }

    public function getComponent(?string $title = null, string $wrapperClass = 'form-section__header', ?string $icon = null, bool $showRequired = true): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('div')->class($wrapperClass)->add(
            $this->headerLeft($title, $wrapperClass, $icon),
            $this->headerRight($wrapperClass, $showRequired),
        );
    }

    private function headerLeft(?string $title, string $wrapperClass, ?string $icon): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('div')->class($wrapperClass . '-left')->add(
            $html->tag('div')->class('icon-container')->add(
                $this->iconBuilder->createIcon($icon ?? 'icon-edit2', 'Edit', ['edit']),
            ),
            $html->tag('h6')->class('title')->add(
                $html->text($title),
            ),
        );
    }

    private function headerRight(string $wrapperClass, bool $showRequired): ?AbstractHtmlComponent
    {
        $html = $this->builder;
        if ($showRequired) {
            return $html->tag('span')->class($wrapperClass . '-right')->content('Required');
        }
        return null;
    }
}