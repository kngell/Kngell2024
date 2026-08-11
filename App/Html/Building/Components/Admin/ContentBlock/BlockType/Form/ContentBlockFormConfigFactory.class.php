<?php

declare(strict_types=1);

final class ContentBlockFormConfigFactory extends AbstractFormConfigFactory
{
    private string $page;
    private ContentBlockFormLayoutInterface $formLayout;

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
        private readonly PageSectionOptionsService $optionsService,
        private BlockType $blockType,
        private ContentBlockFormLayoutFactory $layoutFactory,
        private readonly FileMetadataService $metadataService,
    ) {
        $this->page = $this->blockType->getPageTitle();
        $this->formLayout = $layoutFactory->create($blockType);
    }

    #[Override]
    public function headerTitle(): string
    {
        return $this->page . ' Manager';
    }

    #[Override]
    public function breadcrumbs(): array
    {
        return ['Dashboard', 'Pages', $this->page];
    }

    #[Override]
    public function headerButtons(): array
    {
        $e = $this->entityDescriptor();
        return [
            new HeaderButton(
                label: 'Delete',
                action: ContentBlockLinks::getDeConfirmDeletionRoute(),
                formName: "{$e->key}_delete_form",
                ariaLabel: 'Delete',
                style: 'danger',
                icon: 'icon-trash',
                type: 'submit',
                requiresEditMode: true,
                requiresEntityId: true,
                size: 'md-compact',
                blockType: $this->blockType->value,
            ),
            new HeaderButton(
                label: 'Add New',
                action: $e->path('add/' . $this->blockType->value),
                formName: "{$e->key}_add_form",
                ariaLabel: 'Add New',
                style: 'primary',
                icon: 'icon-plus',
                type: 'submit',
                requiresEditMode: false,
                requiresEntityId: false,
                size: 'md-compact',
            ),
        ];
    }

    public function buildSectionsConfig(): array
    {
        return array_merge(
            $this->getRegularConfig(),
            $this->getMediaConfig(),
        );
    }

    public function buildSections(): array
    {
        return [
            new ContentBlockBasics(
                $this->htmlBuilder,
                $this->iconBuilder,
                $this->header,
                $this->optionsService,
                $this->blockType,
            ),
            new ContentBlockMediaSection(
                $this->htmlBuilder,
                $this->iconBuilder,
                $this->header,
                $this->blockType,
            ),
            new ContentBlockProductRelationshipSection(
                $this->htmlBuilder,
                $this->iconBuilder,
                $this->header,
                $this->blockType,
            ),
        ];
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::CONTENT_BLOCK->getKey($this->blockType),
            displayName: $this->page,
            plural: EntityKey::CONTENT_BLOCK->getPlural($this->blockType),
            basePath: EntityKey::CONTENT_BLOCK->getBasePath(),
        );
    }

    protected function getRegularConfig(): array
    {
        return [];
    }

    protected function getMediaConfig(): array
    {
        return [];
    }

    protected function getHiddenFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'block_type',
                type: 'hidden',
            )->setDefaultValue($this->blockType->value),
        ];
    }

    protected function getFields(): array
    {
        return [
        ];
    }

    protected function getAssets(): array
    {
        return[
            'css' => 'css/backend/admin/pages/content-block',
            'js' => 'js/backend/pages/content-block-main',
            'sectionClass' => 'content-block',
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['content-block__body'];
    }

    protected function getFooterClass(): array
    {
        return ['content-block__footer'];
    }

    protected function isShowProgressBar(): bool
    {
        return true;
    }

    protected function formId(): string
    {
        return 'content-block-form__id';
    }

    protected function formName(): string
    {
        return 'content-block-frm';
    }

    /** @return string[] */
    protected function formClass(): array
    {
        return ['content-block-frm'];
    }

    protected function getFormKey(): ?string
    {
        return 'contentBlockForm';
    }

    protected function defaultInputLayoutName(): ?string
    {
        return 'input';
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new FieldLayout(),
            'custom-select' => new CustomSelectLayout(),
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new TextareaFieldHandler(),
            new CustomSelectFieldHandler(),
            new DropzoneFieldHandler(
                $this->metadataService,
                $this->iconBuilder,
            ),
            new NativeSelectFieldHandler(),
            new ToggleSwitchHandler(),
        ];
    }

    protected function getDropzoneRenderer(): ?DropzoneRenderer
    {
        return new DropzoneRenderer(
            $this->metadataService,
            $this->iconBuilder,
        );
    }

    #[Override]
    protected function defaultSectionIcon(): string
    {
        return 'icon-edit';
    }

    #[Override]
    protected function customAttributes(): array
    {
        return [
            'data-validate' => 'true',
            'data-validation-rules' => 'contentBlockRules',
            'data-form-type' => $this->blockType->value,
        ];
    }

    #[Override]
    protected function defaultSectionTitle(): ?string
    {
        return null;
    }

    #[Override]
    protected function submitText(): string
    {
        return 'Save Section';
    }

    #[Override]
    protected function submitIcon(): string
    {
        return 'icon-save';
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        return $this->formLayout->getSectionGroups();
    }

    protected function tabConfig(): ?TabConfig
    {
        return $this->formLayout->getTabConfig();
    }
}