<?php

declare(strict_types=1);

abstract class AbstractBaseHtmlSection implements HtmlSectionInterface
{
    protected array $context = [];
    protected bool $hasform = false;
    protected ?string $action = null;
    protected array $pagination = [];

    public function __construct(
        protected readonly HtmlBuilder $htmlBuilder,
        protected readonly IconBuilder $iconBuilder,
    ) {
    }

    abstract public function getConfig(array $formValues = []): array|AbstractHtmlComponent;

    abstract public function getKey(): string;

    public function shouldRender(array|Entity $formValues = []): bool
    {
        return true;
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return null;
    }

    public function getSectionsCustomLayout(array $sections): ?AbstractHtmlComponent
    {
        return null;
    }

    public function buildForm(): ?AbstractHtmlComponent
    {
        return null;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function hasform(): bool
    {
        return $this->hasform;
    }

    public function withform(bool $hasform = true): self
    {
        $this->hasform = $hasform;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * @param array $pagination
     *
     * @return self
     */
    public function setPagination(array $pagination = []): self
    {
        $this->pagination = $pagination;

        return $this;
    }

    protected function withAction(?string $action): self
    {
        $this->action = $action;
        return $this;
    }

    protected function thumbnail(null|array|string $media = null, string $alt = ''): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $container = $html->tag('div')->class('details__image-container');

        if ($media !== null) {
            return $container->add(
                $html->tag('img')
                    ->src($media)
                    ->class('image')
                    ->alt('Entity Image'),
            );
        }

        return $container->add(
            $this->iconBuilder->createIcon(
                'icon-media-image',
                'Thumbnail',
                ['image'],
            ),
        );
    }

    /**
     * @param string $action
     * @param null|string $entityId
     * @param AbstractHtmlComponent[] $components
     *
     * @return AbstractHtmlComponent
     */
    protected function wrapButtonWithform(string $action, ?string $entityId = null, array $classes = [], ?string $id = null, array $components = []): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $method = 'get';
        $csrfToken = false;
        if ($this->isPostMethod($action)) {
            $method = 'post';
            $csrfToken = true;
        }
        $form = $html->form($csrfToken)
            ->action($action)
            ->method($method)
            ->style(['display' => 'inline']);
        if (!empty($classes)) {
            $form->class(...$classes);
        }
        if ($id !== null) {
            $form->id($id);
        }
        return $form->add(
            $entityId !== null ? $html->input('hidden')->name('id')->value($entityId) : null,
            ...$components,
        );
    }

    protected function getContentOverview(string $content, int $length = 200): string
    {
        return substr(strip_tags($this->htmlDecode($content)), 0, $length) . '...';
    }

    protected function htmlDecode(string|null $str): string
    {
        return !empty($str) ? htmlspecialchars_decode(html_entity_decode($str), ENT_QUOTES) : '';
    }

    private function isPostMethod(string $url): bool
    {
        if (str_ends_with($url, 'confirm')) {
            return true;
        }
        return false;
    }
}