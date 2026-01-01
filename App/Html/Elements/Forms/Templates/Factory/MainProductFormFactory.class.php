<?php

declare(strict_types=1);

class MainProductFormFactory implements FormFactoryInterface
{
    private const array SUPPORTED_ACTIONS = ['edit', 'new', 'update', 'create', 'add', 'view', ''];
    private const array SUPPORTED_ROUTES = [
        // Controller-based routes
        'product/create',
        'product/edit',
        'product/update',
        'product/new',
        'product/add',
        'product/store',
        'product-operations/save',

        // Dedicated routes
        'create',
        'edit',
        'update',
        'new',
        'add',
        'store',

        // Root routes
        '/create',
        '/edit',
        '/update',
        '/new',
        '/add',
        '/store',

        // Admin routes
        'admin/product/create',
        'admin/product/edit',
        'admin/product/update',
    ];

    public function __construct(
        private HtmlBuilder $builder,
        private FieldRenderer $fieldRenderer,
        private FieldGroupRenderer $fieldGroupRenderer,
        private SectionRenderer $sectionRenderer,
        private ButtonBuilder $buttonBuilder,
        private IconBuilder $iconBuilder,
        private FieldIdGenerator $idGenerator,
        private FormSectionManager $sectionManager,
        private FormProgressCalculator $progressCalculator,
        private readonly ProductSectionServiceProvider $provider,
        private readonly FlashInterface $flash,
        private array $formValues = [],
        private array $formErrors = [],
    ) {
    }

    public function supports(string $action): bool
    {
        if (in_array($action, self::SUPPORTED_ACTIONS)) {
            return true;
        }

        if (!empty($action)) {
            foreach (self::SUPPORTED_ROUTES as $supportedRoute) {
                if (str_contains($action, $supportedRoute)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function createForm(): FormTemplateInterface
    {
        return new ProductForm(
            $this->builder,
            $this->fieldRenderer,
            $this->fieldGroupRenderer,
            $this->sectionRenderer,
            $this->buttonBuilder,
            $this->iconBuilder,
            $this->idGenerator,
            $this->sectionManager,
            $this->progressCalculator,
            $this->provider,
            $this->flash,
        );
    }
}