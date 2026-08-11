<?php

declare(strict_types=1);

class IconBuilder
{
    private const string ICON_SPRITE = 'icons-sprite.svg';

    public function __construct(private readonly HtmlBuilder $htmlBuilder)
    {
    }

    public function createFromConfig(IconConfig $config): AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        if ($config->wrapperClass !== null) {
            return $this->createIconWrapperFromConfig($config);
        }
        $classes = array_merge(['icon'], $config->iconClass);

        $svg = $form->tag('svg')
            ->class(...$classes)
            ->ariaLabel($config->ariaLabel)
            ->role($config->role)
            ->attr('viewBox', $config->viewBox);
        if (!empty($config->aria)) {
            $svg->aria(...$config->aria);
        }

        if ($config->width !== null) {
            $svg->attr('width', $config->width);
        }
        if ($config->height !== null) {
            $svg->attr('height', $config->height);
        }
        if ($config->fill !== null) {
            $svg->attr('fill', $config->fill);
        }
        if ($config->stroke !== null) {
            $svg->attr('stroke', $config->stroke);
        }
        if ($config->strokeWidth !== null) {
            $svg->attr('stroke-width', $config->strokeWidth);
        }

        // Add title if provided
        if ($config->title !== null) {
            $svg->add($form->tag('title')->content($config->title));
        }

        // Add description if provided
        if ($config->desc !== null) {
            $svg->add($form->tag('desc')->content($config->desc));
        }

        // Add use element
        $svg->add($form->tag('use')->href($this->getMediaIconUrl($config->icon)));

        return $svg;
    }

    public function createIcon(string $icon, string $ariaLabel, array $iconClass = [], ?string $desc = null): AbstractHtmlComponent
    {
        $config = new IconConfig(
            icon: $icon,
            ariaLabel: $ariaLabel,
            iconClass: $iconClass,
            desc: $desc,
        );
        return $this->createFromConfig($config);
    }

    public function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel): AbstractHtmlComponent
    {
        $config = new IconConfig(
            icon: $icon,
            ariaLabel: $ariaLabel,
            wrapperClass: $wrapperClass,
        );
        return $this->createFromConfig($config);
    }

    public function createButtonIcon(
        string $icon,
        string $ariaLabel,
        array $iconClass = [],
        ?string $desc = null,
        bool $decorative = false,
    ): AbstractHtmlComponent {
        $config = new IconConfig(
            icon: $icon,
            ariaLabel: $decorative ? '' : $ariaLabel,
            iconClass: $iconClass,
            desc: $desc,
            role: $decorative ? 'presentation' : 'img',
        );
        return $this->createFromConfig($config);
    }

    private function createIconWrapperFromConfig(IconConfig $config): AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        return $form->tag('span')
            ->class($config->wrapperClass)
            ->add($this->createFromConfig($config));
    }

    private function getMediaIconUrl(string $icon): string
    {
        return '/public/assets/img/' . self::ICON_SPRITE . '#' . $icon;
    }
}