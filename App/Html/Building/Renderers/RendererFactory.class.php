<?php

declare(strict_types=1);

final class RendererFactory
{
    private array $fieldRenderers = [];
    private ?FieldGroupRenderer $fieldGroupRenderer = null;
    private ?DropzoneRenderer $dropzoneRenderer = null;
    private ?SectionRenderer $sectionRenderer = null;
    private ?TableRenderer $tableRenderer = null;
    private ?VariationGroupRenderer $variationGroupRenderer = null;
    private ?string $sectionRendererKey = null;

    /** @var array<string, callable(RendererFactory): array> */
    private array $handlerFactories = [];

    /** @var array<string, array> */
    private array $resolvedHandlers = [];

    public function __construct(
        private readonly FileMetadataService $fileMetadata,
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlBuilder $htmlBuilder,
        private readonly ButtonBuilder $buttonBuilder,
    ) {
    }

    /**
     * Register a handler factory (closure) for a specific context
     * The closure must return an array of handler objects.
     */
    public function registerHandlers(string $context, callable $handlerFactory): self
    {
        $this->handlerFactories[$context] = $handlerFactory;
        return $this;
    }

    /**
     * Get or create FieldRenderer for specific context.
     */
    public function getFieldRenderer(string $context = 'default'): FieldRenderer
    {
        if (!isset($this->fieldRenderers[$context])) {
            $handlers = $this->resolveHandlers($context);
            $this->fieldRenderers[$context] = new FieldRenderer($handlers);
        }

        return $this->fieldRenderers[$context];
    }

    public function getFieldGroupRenderer(): FieldGroupRenderer
    {
        return $this->fieldGroupRenderer ??= new FieldGroupRenderer(
            $this->getFieldRenderer(), // Uses default context
        );
    }

    public function getDropzoneRenderer(): DropzoneRenderer
    {
        return $this->dropzoneRenderer ??= new DropzoneRenderer(
            $this->fileMetadata,
            $this->iconBuilder,
        );
    }

    public function getTableRenderer(): TableRenderer
    {
        return $this->tableRenderer ??= new TableRenderer(
            $this->htmlBuilder,
            $this->iconBuilder,
        );
    }

    public function getVariationGroupRenderer(): VariationGroupRenderer
    {
        return $this->variationGroupRenderer ??= new VariationGroupRenderer(
            $this->getFieldGroupRenderer(),
        );
    }

    public function getSectionRenderer(string $context = 'default'): SectionRenderer
    {
        $key = $context . '_section';

        if ($this->sectionRenderer === null || $this->sectionRendererKey !== $key) {
            $this->sectionRenderer = new SectionRenderer(
                $this->getFieldRenderer($context),
                $this->getFieldGroupRenderer(),
                $this->getDropzoneRenderer(),
                $this->getTableRenderer(),
                $this->getVariationGroupRenderer(),
            );
            $this->sectionRendererKey = $key;
        }

        return $this->sectionRenderer;
    }

    /**
     * Helper methods for handlers to access dependencies.
     */
    public function getFileMetadata(): FileMetadataService
    {
        return $this->fileMetadata;
    }

    public function getIconBuilder(): IconBuilder
    {
        return $this->iconBuilder;
    }

    public function getButtonBuilder(): ButtonBuilder
    {
        return $this->buttonBuilder;
    }

    public function getHtmlBuilder(): HtmlBuilder
    {
        return $this->htmlBuilder;
    }

    /**
     * Reset all cached renderers (useful for testing).
     */
    public function reset(): void
    {
        $this->fieldRenderers = [];
        $this->fieldGroupRenderer = null;
        $this->dropzoneRenderer = null;
        $this->sectionRenderer = null;
        $this->tableRenderer = null;
        $this->variationGroupRenderer = null;
        $this->sectionRendererKey = null;
        $this->resolvedHandlers = [];
    }

    /**
     * Resolve handlers for a context, using closures if available.
     *
     * @return array<mixed>
     */
    private function resolveHandlers(string $context): array
    {
        // Return cached resolved handlers
        if (isset($this->resolvedHandlers[$context])) {
            return $this->resolvedHandlers[$context];
        }

        $handlers = [];

        // If we have a factory closure for this context, execute it
        if (isset($this->handlerFactories[$context])) {
            $result = ($this->handlerFactories[$context])($this);

            // Ensure the result is an array
            if (!is_array($result)) {
                throw new RuntimeException(
                    sprintf(
                        'Handler factory for context "%s" must return an array, got %s',
                        $context,
                        gettype($result),
                    ),
                );
            }

            $handlers = $result;
        } else {
            // Default handlers
            $handlers = $this->getDefaultHandlers();
        }

        // Cache the resolved handlers
        $this->resolvedHandlers[$context] = $handlers;

        return $handlers;
    }

    /**
     * Default handlers - these can also use $this to get renderers.
     *
     * @return array<mixed>
     */
    private function getDefaultHandlers(): array
    {
        return [
            new InputBoxHandler(),
            new TextareaBoxHandler(),
            new NativeSelectBoxHandler(),
            new DropzoneFieldHandler($this->fileMetadata, $this->iconBuilder),
            new CurrencyFieldHandler(),
            new FieldGroupFieldHandler($this->getFieldGroupRenderer()),
            new ButtonFieldHandler($this->buttonBuilder),
        ];
    }
}