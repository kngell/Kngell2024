<?php

declare(strict_types=1);

/**
 * @method array getFieldLayouts()
 * @method array getFieldHandlers()
 * @method array getSectionRenderers()
 *
 * @property FileMetadataService $metadataService
 * @property IconBuilder $iconBuilder
 */
trait BuildSectionRendererTrait
{
    private ?SectionRenderer $sectionRenderer = null;
    private ?FieldRenderer $fieldRenderer = null;
    private ?FieldGroupRenderer $fieldGroupRenderer = null;

    /**
     * @return null|FieldRenderer
     */
    public function getFieldRenderer(): ?FieldRenderer
    {
        if ($this->fieldRenderer === null) {
            $this->fieldRenderer = new FieldRenderer($this->getFieldHandlers());
        }
        return $this->fieldRenderer;
    }

    protected function getSectionRenderer(): SectionRenderer
    {
        // Return cached instance if exists
        if ($this->sectionRenderer !== null) {
            return $this->sectionRenderer;
        }

        // Build field renderer

        foreach ($this->getFieldLayouts() as $name => $layout) {
            $this->getFieldRenderer()->registerNamedLayout($name, $layout);
        }

        // Build section renderer
        $sectionRenderer = new SectionRenderer();
        $sectionRenderer->fieldRenderer($this->getFieldRenderer());

        $sectionRenderer->fieldGroupRenderer($this->getFieldGroupRenderer());

        $renderers = $this->getRenderers();
        if (!empty($renderers)) {
            $sectionRenderer->registerRenderer(...$renderers);
        } else {
            $dropzoneRenderer = method_exists($this, 'getDropzoneRenderer')
                       ? $this->getDropzoneRenderer()
                       : null;

            if ($dropzoneRenderer !== null) {
                $sectionRenderer->dropzoneRenderer($dropzoneRenderer);
            }

            $variationRenderer = null;

            if (method_exists($this, 'getVariationGroupRenderer')) {
                $variationRenderer = $this->getVariationGroupRenderer();
                $sectionRenderer->variationGroupRenderer($variationRenderer);
            }
        }
        // Cache and return
        $this->sectionRenderer = $sectionRenderer;
        return $sectionRenderer;
    }

    protected function getFieldGroupRenderer(): FieldGroupRenderer
    {
        if ($this->fieldGroupRenderer !== null) {
            return $this->fieldGroupRenderer;
        }
        return new FieldGroupRenderer($this->getFieldRenderer());
    }
}