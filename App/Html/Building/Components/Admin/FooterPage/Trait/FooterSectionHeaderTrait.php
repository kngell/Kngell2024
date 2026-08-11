<?php

declare(strict_types=1);
/**
 * @property HtmlBuilder $htmlBuilder
 * @property IconBuilder $iconBuilder
 */
trait FooterSectionHeaderTrait
{
    private function sectionHeader(string $title, string $action, string $type, string $url, string $textSubmit, array $class = ['new-column'], ?AbstractHtmlComponent $filterGroup = null): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $button = $html->button()->class('btn', 'btn-secondary')->custom([
            'data-action' => $action,
            'data-modal-url' => $url,
            'data-modal-type' => $type,
        ])->add(
            $this->iconBuilder->createIcon('icon-plus', $textSubmit, $class),
            $html->text($textSubmit),
        );
        return $html->div()->class('section-header')->add(
            $html->tag('h2')->content($title),
            $this->wrapButtonWithform(
                action: $url,
                components: [
                    $button,
                    $filterGroup !== null ? $filterGroup : null,
                ],
            ),
        );
    }
}