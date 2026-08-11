<?php

declare(strict_types=1);

final class ProductFormConfigFactory extends AbstractFormConfigFactory
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private FileMetadataService $metadataService,
    ) {
    }

    #[Override]
    public function headerTitle(): string
    {
        return 'Product Manager';
    }

    #[Override]
    public function breadcrumbs(): array
    {
        return ['Dashboard', 'Pages', 'Product'];
    }

    #[Override]
    public function headerButtons(): array
    {
        $e = $this->entityDescriptor();

        return [
            new HeaderButton(
                label: 'Delete',
                action: ProductLinks::CONFIRM_DELETION->value,
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
                action: $e->path('product-add'),
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

    protected function buildSections(): array
    {
        return [
            GeneralInformationSection::class,
            MediaSection::class,
            BrandSection::class,
            CategorySection::class,
            PricingSection::class,
            InventorySection::class,
            VariationSection::class,
            ShippingSection::class,
            ProductStatusSection::class,
            ProductTagsSection::class,
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputBoxHandler(),
            new TextareaHandler(),
            new NativeSelectBoxHandler(),
            new CurrencyFieldHandler(),
            // new CustomSelectFieldHandler(),
            new DropzoneFieldHandler(
                $this->metadataService,
                $this->iconBuilder,
            ),
            // new NativeSelectFieldHandler(),
            // new ToggleSwitchHandler(),
        ];
    }

    protected function getDisplayKey(): ?string
    {
        return 'productForm';
    }

    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new TabbedFormLayout($this->tabConfig(), $this->sectionGroupManager()->getAllGroups());
    }

    protected function getEnumClass(): ?string
    {
        return ProductSection::class;
    }

    protected function getRenderers(): array
    {
        return [
            new VariationGroupRenderer($this->getFieldGroupRenderer()),
            new DropzoneRenderer(
                $this->metadataService,
                $this->iconBuilder,
            ),
        ];
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        $sectionGroupManager = SectionGroupManager::create();

        return $sectionGroupManager
          ->addGroup(
              SectionGroup::create('general-infos')
                  ->setSectionKeys([
                      ProductSection::GENERAL_INFOS->value,
                      ProductSection::PRODUCT_TAGS->value,
                  ])
                  ->setPosition('left')
                  ->setWrapperClass(['form-left']),
          )->addGroup(
              SectionGroup::create(
                  key: 'media',
              )->setSectionKeys([
                  ProductSection::MEDIA->value,
              ])
              ->setPosition('full')
              ->setwrapperClass(['form-left']),
          )->addGroup(
              SectionGroup::create(
                  key: 'price',
              )->setSectionKeys([
                  ProductSection::PRICING->value,
                  ProductSection::SHIPPING->value,
              ])->setPosition('left')
               ->setwrapperClass(['form-left']),
          )->addGroup(
              SectionGroup::create(
                  key: 'inventory',
              )->setSectionKeys([
                  ProductSection::INVENTORY->value,
              ])->setPosition('right')
               ->setwrapperClass(['form-right']),
          )->addGroup(
              SectionGroup::create('right-content')
                  ->setSectionKeys([
                      ProductSection::BRAND->value,
                      ProductSection::CATEGORY->value,
                      ProductSection::PRODUCT_STATUS->value,
                  ])
                  ->setPosition('right')
                  ->setWrapperClass(['form-right']),
          )->addGroup(
              SectionGroup::create(
                  key: 'variation',
              )->setSectionKeys([
                  ProductSection::VARIATION->value,
              ])->setPosition('left')
               ->setwrapperClass(['form-left']),
          );
    }

    protected function tabConfig(): ?TabConfig
    {
        return TabConfig::create()
            ->setTabContainerClass(['product__body-frm__tabs'])
            ->setContentContainerClass(['product__body-frm__content'])

            // Tab 1: Basic Information
            ->addTab(
                TabItem::create(
                    id: 'tab-basics',
                    title: 'Basic Information',
                )
                    ->setSectionGroups(['general-infos', 'right-content'])
                    ->setState('default')
                    ->setContentClass('product__body-frm__content--basics')
                    ->setPriority(1),
            )
            // Tab2 : RelationShips and settings
            ->addTab(
                TabItem::create(
                    id: 'tab-media',
                    title: 'Media Information',
                )
                    ->setSectionGroups([
                        'media',
                    ])
                    ->setContentClass('product__body-frm__content--media')
                    ->setPriority(2),
            )
            ->addTab(
                TabItem::create(
                    id: 'tab-price-and-inventory',
                    title: 'Comercial Information',
                )->setSectionGroups([
                    'price',
                    'inventory',
                ])->setContentClass('product__body-frm__content--price-inventory')
                    ->setPriority(3),
            )

            // Tab 2: Small Banner Settings
            ->addTab(
                TabItem::create(
                    id:'tab-variation',
                    title: 'Variations',
                )
                    ->setSectionGroups(['variation'])
                    ->setContentClass('product__body-frm__content--variation')
                    ->setPriority(4),
            );
    }

    protected function defaultInputLayoutName(): ?string
    {
        return 'input';
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new InputBoxLayout(),
        ];
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::PRODUCT->getKey(),
            displayName: 'Products',
            plural: 'products',
            basePath: '/admin/admin',
        );
    }

    protected function getHiddenFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'public_id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'slug',
                type: 'hidden',
            ),
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['product__body'];
    }

    protected function getFooterClass(): array
    {
        return ['product__footer'];
    }

    protected function isShowProgressBar(): bool
    {
        return true;
    }

    protected function formId(): string
    {
        return 'product-frm';
    }

    protected function formName(): string
    {
        return 'product-frm';
    }

    /** @return string[] */
    protected function formClass(): array
    {
        return ['product__body-frm'];
    }

    protected function getFormKey(): ?string
    {
        return 'productForm';
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
            'data-validation-rules' => 'productRules',
            'data-form-type' => 'product',
        ];
    }

    #[Override]
    protected function submitText(): string
    {
        return 'Save Product';
    }

    #[Override]
    protected function submitIcon(): string
    {
        return 'icon-save';
    }
}