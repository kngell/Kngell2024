<?php

declare(strict_types=1);

abstract class AbstractAdminHtmlDecorator extends AbstractHtmlDecorator
{
    // Backward-compat constants (legacy decorators not yet migrated)
    protected const array HEADER_BTN_CONFIG = [];
    protected const array BREADCRUMBS_LINKS = [];

    protected ?AdminHeaderConfig $headerConfigOverride = null;
    protected array|Entity $formValues = [];
    protected string $deleteAction = '';
    protected bool $withFilters = false;

    public function __construct(
        private readonly AdminMainHeaderFactory $factory,
    ) {
        parent::__construct();
    }

    protected function buildHeaderSection(Controller $target): array
    {
        $headerConfig = $this->resolveHeaderConfig();
        if ($headerConfig === null) {
            return [];
        }

        $headerConfig = $this->applyRuntimeOverrides($headerConfig);

        $headerComponent = $this->factory->create($target->getBuilder())
            ->withConfig($headerConfig)
            ->isEditMode($this->isEditing())
            ->idFieldName($this->getEntityKeyField())
            ->id($this->getEntityKeyValue())
            ->build();

        if ($headerComponent === null) {
            return [];
        }

        $output = ['adminMainHeader' => $headerComponent->generate()];

        if ($this->withFilters) {
            $sub = $this->factory->createSubHeader($target->getBuilder())
                ->withSearchPlaceholder($this->getSearchPlaceholder())
                ->build();

            if ($sub !== null) {
                $output['headerSearchAndFilter'] = $sub->generate();
            }
        }

        return $output;
    }

    /**
     * Override in list decorators to provide entity-specific placeholder.
     */
    protected function getSearchPlaceholder(): string
    {
        return 'Search...';
    }

    /**
     * Override in concrete classes if not using the factory-based config.
     */
    protected function getHeaderConfig(): ?AdminHeaderConfig
    {
        return null;
    }

    protected function headerTitle(): ?string
    {
        return null;
    }

    // ─── Internal ────────────────────────────────────────────

    private function resolveHeaderConfig(): ?AdminHeaderConfig
    {
        if ($this->headerConfigOverride !== null) {
            return $this->headerConfigOverride;
        }

        $fromSubclass = $this->getHeaderConfig();
        if ($fromSubclass !== null) {
            return $fromSubclass;
        }

        // Legacy fallback: build from constants
        if (static::BREADCRUMBS_LINKS === [] && static::HEADER_BTN_CONFIG === []) {
            return null;
        }

        return new AdminHeaderConfig(
            title: $this->headerTitle() ?? '',
            breadcrumbs: static::BREADCRUMBS_LINKS,
            primaryActions: $this->buildLegacyPrimaryActions(),
        );
    }

    /** @return HeaderButton[] */
    private function buildLegacyPrimaryActions(): array
    {
        $actions = [];
        foreach (static::HEADER_BTN_CONFIG as $cfg) {
            $actions[] = new HeaderButton(
                label:            $cfg['label'] ?? '',
                action:           $cfg['action'] ?? '',
                formName:         $cfg['formName'] ?? '',
                ariaLabel:        $cfg['ariaLabel'] ?? ($cfg['label'] ?? ''),
                type:             $cfg['type'] ?? 'submit',
                style:            $cfg['style'] ?? 'primary',
                size:             $cfg['size'] ?? 'md-compact',
                icon:             $cfg['icon'] ?? null,
                iconPosition:     $cfg['iconPosition'] ?? 'left',
                requiresEditMode: $cfg['requiresEditMode'] ?? false,
                requiresEntityId: $cfg['requiresEntityId'] ?? false,
                attributes:       $cfg['attributes'] ?? [],
                class:            $cfg['class'] ?? [],
            );
        }
        return $actions;
    }

    private function applyRuntimeOverrides(AdminHeaderConfig $config): AdminHeaderConfig
    {
        if ($this->deleteAction === '' || $config->primaryActions === []) {
            return $config;
        }

        $actions = $config->primaryActions;
        $actions[0] = $actions[0]->withAction($this->deleteAction);

        return $config->withPrimaryActions($actions);
    }

    private function isEditing(): bool
    {
        return $this->formValues instanceof Entity
            && $this->formValues->entityKeyIsInitialzed();
    }

    private function getEntityKeyField(): ?string
    {
        return $this->isEditing() ? $this->formValues->getEntityKeyField() : null;
    }

    private function getEntityKeyValue(): mixed
    {
        return $this->isEditing() ? $this->formValues->getEntityPrimarykeyValue() : null;
    }
}