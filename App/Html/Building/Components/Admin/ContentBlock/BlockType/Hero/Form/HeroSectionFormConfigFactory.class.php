<?php

declare(strict_types=1);

final class HeroSectionFormConfigFactory extends AbstractFormConfigFactory
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private FileMetadataService $metadataService,
        private HeroSectionConfigBuilder $sectionBuilder,
    ) {
    }

    #[Override]
    public function headerTitle(): string
    {
        return 'Hero Section Manager';
    }

    #[Override]
    public function breadcrumbs(): array
    {
        return ['Dashboard', 'Pages', 'Hero Section'];
    }

    #[Override]
    public function headerButtons(): array
    {
        $e = $this->entityDescriptor();
        $type = BlockType::HERO->value;

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
                blockType: $type,
            ),
            new HeaderButton(
                label: 'Add New',
                action: $e->path('add/' . $type),
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

    #[Override]
    protected function getdisplayKey(): ?string
    {
        return 'mainForm';
    }

    protected function getRegularConfig(): array
    {
        return $this->sectionBuilder->buildRegularConfig();
    }

    protected function getMediaConfig(): array
    {
        return $this->sectionBuilder->buildMediaConfig();
    }

    protected function getFields(): array
    {
        return [
            [
                'name' => 'block_type',
                'type' => 'hidden',
                'value' => BlockType::HERO->value,
            ],
        ];
    }

    #[Override]
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
            )->setDefaultValue(BlockType::HERO->value),
        ];
    }

    protected function getAssets(): array
    {
        return[
            'css' => 'css/backend/admin/pages/hero-section',
            'js' => 'js/backend/pages/hero-main',
            'sectionClass' => 'hero',
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['hero__body'];
    }

    protected function getFooterClass(): array
    {
        return ['hero__footer'];
    }

    protected function isShowProgressBar(): bool
    {
        return true;
    }

    protected function formId(): string
    {
        return 'hero-form-id';
    }

    protected function formName(): string
    {
        return 'hero-form';
    }

    /** @return string[] */
    protected function formClass(): array
    {
        return ['hero-form'];
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
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new TextareaFieldHandler(),
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
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::HERO->value,
            displayName: 'Hero Section',
            plural: 'heroes',
            basePath: '/admin/content-block-page',
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
            'data-validation-rules' => 'heroRules',
            'data-form-type' => 'hero_section',
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
        return 'Save Hero';
    }

    #[Override]
    protected function submitIcon(): string
    {
        return 'icon-save';
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        $sectionGroupManager = SectionGroupManager::create();

        return $sectionGroupManager
          ->addGroup(
              SectionGroup::create('left-content')
                  ->setSectionKeys([HeroSectionEnum::BASIC_INFO->value])
                  ->setPosition('left')
                  ->setWrapperClass(['hero-form__left']),
          )
          ->addGroup(
              SectionGroup::create('right-content')
                  ->setSectionKeys([HeroSectionEnum::MEDIA->value])
                  ->setPosition('right')
                  ->setWrapperClass(['hero-form__right']),
          )->addGroup(
              SectionGroup::create('metadata')
                  ->setSectionKeys([HeroSectionEnum::METADATA->value])
                  ->setPosition('left')
                  ->setWrapperClass(['hero-form__left']),
          );
    }

    protected function tabConfig(): ?TabConfig
    {
        return TabConfig::create()
            ->setTabContainerClass(['hero-form__tabs'])
            ->setContentContainerClass(['hero-form__content'])

            // Tab 1: Basic Information
            ->addTab(
                TabItem::create(
                    id: 'tab-basics',
                    title: 'Basic Information',
                )
                    ->setSectionGroups(['left-content', 'right-content'])
                    ->setState('default')
                    ->setContentClass('hero-form__content--basics')
                    ->setPriority(1),
            )

            // Tab 2: Hero Settings
            ->addTab(
                TabItem::create(
                    id:'tab-metadata',
                    title: 'Hero Metadata',
                )
                    ->setSectionGroups(['metadata'])
                    ->setContentClass('hero-form__content--metadata')
                    ->setPriority(2),
            );
    }
}