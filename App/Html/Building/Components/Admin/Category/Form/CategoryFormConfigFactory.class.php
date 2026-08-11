<?php

declare(strict_types=1);

final class CategoryFormConfigFactory extends AbstractFormConfigFactory
{
    public function __construct(private FileMetadataService $metadataService, private IconBuilder $iconBuilder)
    {
    }

    #[Override]
    public function headerTitle(): string
    {
        return 'Category Manager';
    }

    #[Override]
    public function breadcrumbs(): array
    {
        return ['Dashboard', 'Pages', 'Category'];
    }

    #[Override]
    public function headerButtons(): array
    {
        $e = $this->entityDescriptor();
        return [
            new HeaderButton(
                label: 'Delete',
                action: '/admin/category-confirm-deletion/confirm',
                formName: "{$e->key}_delete_form",
                ariaLabel: 'Delete',
                style: 'danger',
                icon: 'icon-trash',
                type: 'submit',
                requiresEditMode: true,
                requiresEntityId: true,
                size: 'md-compact',
            ),
            new HeaderButton(
                label: 'Add New',
                action: $e->path('add/'),
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
            CategoryBasicInformation::class,
            CategoryCanonicalInfoSection::class,
            CategoryContentSection::class,
            CategoryContentStyleSection::class,
            CategoryMediaSection::class,
            CategoryNavigationSection::class,
            CategoryOGMediaSection::class,
            CategoryOpenGraphSection::class,
            CategoryPriceRangeSection::class,
            CategorySocialMediaSection::class,
        ];
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::CATEGORY->value,
            displayName: 'Category Manager',
            plural: EntityKey::CATEGORY->getPlural(),
            basePath: EntityKey::CATEGORY->getBasePath(),
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
                name: 'cat_id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'public_id',
                type: 'hidden',
            ),
        ];
    }

    protected function getAssets(): array
    {
        return[
            'css' => 'css/backend/admin/pages/category',
            'js' => 'js/backend/pages/category-main',
            'sectionClass' => 'category',
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['category__body'];
    }

    protected function getFooterClass(): array
    {
        return ['category__footer'];
    }

    protected function isShowProgressBar(): bool
    {
        return true;
    }

    protected function formId(): string
    {
        return 'category-form__id';
    }

    protected function formName(): string
    {
        return 'category-frm';
    }

    /** @return string[] */
    protected function formClass(): array
    {
        return ['category-frm'];
    }

    protected function getdisplayKey(): ?string
    {
        return 'mainForm';
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
            'data-validation-rules' => 'categoryRules',
            'data-form-type' => EntityKey::CATEGORY->value,
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
        return 'Save Category';
    }

    #[Override]
    protected function submitIcon(): string
    {
        return 'icon-save';
    }

    protected function getTabComponentConfig(): ?TabComponentConfig
    {
        return TabComponentConfig::adminForm()
            ->setContainerClass(['category-frm__tabs']);
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        $sectionGroupManager = SectionGroupManager::create();

        return $sectionGroupManager
          ->addGroup(
              SectionGroup::create('infos-left')
                  ->setSectionKeys([
                      CategorySection::BASIC_INFOS->value,
                      CategorySection::SOCIAL_MEDIA->value,
                      CategorySection::OPEN_GRAPH->value,
                  ])
                  ->setPosition('left')
                  ->setWrapperClass(['category-frm__left']),
          )
          ->addGroup(
              SectionGroup::create('infos-right')
                  ->setSectionKeys([
                      CategorySection::MEDIA->value,
                      CategorySection::OG_MEDIA->value,
                      CategorySection::CANONICAL_INFOS->value,
                  ])
                  ->setPosition('right')
                  ->setWrapperClass(['category-frm__right']),
          )
          ->addGroup(
              SectionGroup::create('content-left')
                  ->setSectionKeys([
                      CategorySection::CONTENT_AREA->value,
                  ])
                  ->setPosition('left')
                  ->setWrapperClass(['category-frm__left']),
          )->addGroup(
              SectionGroup::create('content-right')
                  ->setSectionKeys([
                      CategorySection::CONTENT_STYLE->value,
                  ])
                  ->setPosition('right')
                  ->setWrapperClass(['category-frm__right']),
          )->addGroup(
              SectionGroup::create('settings-left')
                  ->setSectionKeys([
                      CategorySection::PRICE_RANGE->value,
                  ])
                  ->setPosition('left')
                  ->setWrapperClass(['category-frm__left']),
          )->addGroup(
              SectionGroup::create('settings-right')
                  ->setSectionKeys([
                      CategorySection::NAVIGATION_INFOS->value,
                  ])
                  ->setPosition('right')
                  ->setWrapperClass(['category-frm__right']),
          );
    }

    protected function tabConfig(): ?TabConfig
    {
        return TabConfig::create()
              ->setContentContainerClass(['category-frm__content'])
              ->setTabLabelContainerClass(['category-frm__label'])

              // Tab 1: Basic Information
              ->addTab(
                  TabItem::create(
                      id: 'tab-basics',
                      title: 'Basic Information',
                  )
                      ->setSectionGroups(['infos-left',   'infos-right'])
                      ->setState('default')
                      ->setContentClass(['category-frm__content--basics'])
                      ->setPriority(1),
              )
              // Tab2 : RelationShips and settings
              ->addTab(
                  TabItem::create(
                      id: 'tab-content',
                      title: 'Content Area',
                  )
                      ->setSectionGroups(['content-left', 'content-right'])
                      ->setContentClass(['category-frm__content--content'])
                      ->setPriority(2),
              )

              // Tab 2: Small Banner Settings
              ->addTab(
                  TabItem::create(
                      id:'tab-settings',
                      title: 'Settings',
                  )
                      ->setSectionGroups(['settings-left', 'settings-right'])
                      ->setContentClass(['category-frm__content--settings'])
                      ->setPriority(3),
              );
    }
}