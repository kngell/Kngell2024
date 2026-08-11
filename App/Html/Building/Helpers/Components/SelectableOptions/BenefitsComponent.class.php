<?php

declare(strict_types=1);

class BenefitsComponent implements StandAloneComponentInterface
{
    private array $benefits = [];
    private ?string $title = null;
    private string $optionValue = '';
    private array $wrapperClass = ['options__benefits'];
    private string $itemClass = 'benefit-item';
    private string $iconClass = 'benefit-item__icon';
    private string $textClass = 'benefit-item__text';
    private bool $useSvgIcons = false;
    private ?IconBuilder $iconBuilder = null;

    public function __construct(
        private HtmlBuilder $htmlBuilder,
    ) {
    }

    public function setBenefits(array $benefits): self
    {
        $this->benefits = $benefits;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setOptionValue(string $value): self
    {
        $this->optionValue = $value;
        return $this;
    }

    public function setWrapperClass(array $class): self
    {
        $this->wrapperClass = $class;
        return $this;
    }

    public function setItemClass(string $class): self
    {
        $this->itemClass = $class;
        return $this;
    }

    public function setIconClass(string $class): self
    {
        $this->iconClass = $class;
        return $this;
    }

    public function setTextClass(string $class): self
    {
        $this->textClass = $class;
        return $this;
    }

    public function setUseSvgIcons(bool $use, ?IconBuilder $iconBuilder = null): self
    {
        $this->useSvgIcons = $use;
        $this->iconBuilder = $iconBuilder;
        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        if (empty($this->benefits)) {
            return null;
        }

        $html = $this->htmlBuilder;

        $container = $html->tag('div')
            ->class(...$this->wrapperClass)
            ->attr('data-option', $this->optionValue);

        if ($this->title) {
            $container->add(
                $html->tag('h4')
                    ->class('options__benefits-title')
                    ->content($this->title),
            );
        }

        $list = $html->tag('ul')->class('options__benefits-list');

        foreach ($this->benefits as $benefit) {
            $item = $html->tag('li')->class($this->itemClass);

            $icon = $html->tag('span')->class($this->iconClass);

            if ($this->useSvgIcons && $this->iconBuilder) {
                $icon->add(
                    $this->iconBuilder->createIcon(
                        icon: 'check',
                        ariaLabel: 'Check',
                        iconClass: ['benefit-item__icon-svg'],
                    ),
                );
            } else {
                $icon->content('✓');
            }

            $text = $html->tag('span')
                ->class($this->textClass)
                ->content(is_string($benefit) ? $benefit : $benefit['text']);

            $item->add($icon, $text);
            $list->add($item);
        }

        $container->add($list);

        return $container;
    }

    public static function create(HtmlBuilder $builder): self
    {
        return new self($builder);
    }
}