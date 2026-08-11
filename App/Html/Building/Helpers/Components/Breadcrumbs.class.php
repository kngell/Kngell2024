<?php

declare(strict_types=1);

class Breadcrumbs implements StandAloneComponentInterface
{
    private array $links = [];
    private array $containerClass = ['title-left__breadcrumbs'];

    public function __construct(private HtmlBuilder $htmlBuilder)
    {
    }

    public function addLink(
        string $name,
        ?string $link = null,
        bool $active = false,
    ): self {
        $this->links[] = [
            'name' => $name,
            'link' => $link ?? '#',
            'active' => $active,
        ];
        return $this;
    }

    public function setContainerClass(array $containerClass): self
    {
        $this->containerClass = $containerClass;
        return $this;
    }

    public function build(mixed $containerClassOverrides = null): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $items = [];

        foreach ($this->links as $index => $config) {
            $isLast = $index === array_key_last($this->links);
            $linkClass = ['breadcrumbs-list__item--link'];

            if ($isLast) {
                $linkClass[] = 'active';
            }

            $inner = $isLast
                ? $html->tag('span')->class(...$linkClass)->content($config['name'])
                : $html->tag('a')->href($config['link'])->class(...$linkClass)->content($config['name']);

            $items[] = $html->tag('li')->class('breadcrumbs-list__item')->add($inner);
        }

        $class = $this->resolveContainerClass($containerClassOverrides);

        $component = $html->nav()
            ->class(...$class)
            ->aria('label', 'Breadcrumb')
            ->add(
                $html->tag('ul')->class('breadcrumbs-list')->add(...$items),
            );

        $this->reset();

        return $component;
    }

    public function reset(): self
    {
        $this->links = [];
        $this->containerClass = ['title-left__breadcrumbs'];
        return $this;
    }

    private function resolveContainerClass(mixed $overrides): array
    {
        if (is_array($overrides) && ArrayUtils::isStringList($overrides)) {
            return $overrides;
        }

        return $this->containerClass;
    }
}