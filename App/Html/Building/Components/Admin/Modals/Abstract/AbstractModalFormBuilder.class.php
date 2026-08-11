<?php

declare(strict_types=1);

abstract class AbstractModalFormBuilder implements ModalFormBuilderInterface
{
    protected const array OVERLAY_CLASS = ['modal-overlay'];
    protected const array MAIN_DIV_CLASS = ['modal'];
    protected const array HEADER_CLASS = ['modal-header'];
    protected const array HEADER_TITLE_CLASS = ['modal-header__title'];
    protected const array HEADER_CONTENT_CLASS = ['modal-header__content'];
    protected const array HEADER_CONTENT_ICON_CLASS = ['modal-header__content--icon-container'];
    protected const array HEADER_CONTENT_TEXT_CLASS = ['modal-header__content--text'];
    protected const array MODAL_BODY_CLASS = ['modal-body'];
    protected const array MODAL_FOOTER_CLASS = ['modal-footer'];

    public function __construct(protected HtmlBuilder $htmlBuilder, protected IconBuilder $iconBuilder)
    {
    }

    protected function getDialogContainer(): AbstractHtmlComponent
    {
        return  $this->htmlBuilder->tag('dialog')->class(...self::OVERLAY_CLASS);
    }

    protected function getRegularContainer(string|null ...$class): AbstractHtmlComponent
    {
        $classes = array_merge(self::OVERLAY_CLASS, $class);
        return  $this->htmlBuilder->div()->class(...$classes);
    }

    protected function overlayClass(string|null ...$class): array
    {
        return array_merge(self::OVERLAY_CLASS, $class);
    }

    protected function headerClass(string|null ...$class): array
    {
        return array_merge(self::HEADER_CLASS, $class);
    }

    protected function headertitleClass(string|null ...$class): array
    {
        return array_merge(self::HEADER_TITLE_CLASS, $class);
    }

    protected function headerContentClass(string|null ...$class): array
    {
        return array_merge(self::HEADER_CONTENT_CLASS, $class);
    }

    protected function headerContentIconClass(string|null ...$class): array
    {
        return array_merge(self::HEADER_CONTENT_ICON_CLASS, $class);
    }

    protected function headerContentTextClass(string|null ...$class): array
    {
        return array_merge(self::HEADER_CONTENT_TEXT_CLASS, $class);
    }

    protected function bodyClass(string|null ...$class): array
    {
        return array_merge(self::MODAL_BODY_CLASS, $class);
    }

    protected function footerClass(string|null ...$class): array
    {
        return array_merge(self::MODAL_FOOTER_CLASS, $class);
    }

    protected function modalClass(string|null ...$class): array
    {
        return array_merge(self::MAIN_DIV_CLASS, $class);
    }

    protected function closeButton(string $cancelRoute, AbstractHtmlComponent $iconClose): AbstractHtmlComponent
    {
        return $this->htmlBuilder->link()
        ->href($cancelRoute)
        ->class('modal-close-btn')
        ->attribute('aria-label', 'Close modal')
        ->attribute('data-modal-close', true)->add(
            $iconClose,
        );
    }

    protected function buildIcons(?string $icon = null): array|AbstractHtmlComponent
    {
        $iconBuilder = $this->iconBuilder ?? throw new RuntimeException('IconBuilder not initialized');
        $configs = $this->getIconConfigs();

        if ($icon !== null) {
            return $this->buildSingleIcon($iconBuilder, $configs, $icon);
        }

        return array_values($this->buildAllIcons($iconBuilder, $configs));
    }

    private function getIconConfigs(): array
    {
        return [
            'edit' => ['icon-edit', 'Edit', ['edit']],
            'add' => ['icon-plus', 'Add New', ['add']],
            'cancel' => ['icon-cancel', 'Cancel', ['cancel']],
            'delete' => ['icon-trash', 'Delete', ['delete']],
            'close' => ['icon-close', 'Close Modal', ['close']],
        ];
    }

    private function buildSingleIcon(IconBuilder $iconBuilder, array $configs, string $key): AbstractHtmlComponent
    {
        if (!isset($configs[$key])) {
            throw new InvalidArgumentException(
                "Icon '{$key}' not found. Available: " . implode(', ', array_keys($configs)),
            );
        }

        [$class, $label, $attributes] = $configs[$key];
        return $iconBuilder->createIcon($class, $label, $attributes);
    }

    private function buildAllIcons(IconBuilder $iconBuilder, array $configs): array
    {
        $result = [];
        foreach ($configs as $key => [$class, $label, $attributes]) {
            $result[$key] = $iconBuilder->createIcon($class, $label, $attributes);
        }
        return $result;
    }
}