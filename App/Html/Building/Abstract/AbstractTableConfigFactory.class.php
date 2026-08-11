<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

abstract class AbstractTableConfigFactory
{
    public function __construct(
        protected readonly HtmlSectionPresentationService $presenter,
    ) {
    }

    // ─── Public API: three independent products ──────────────

    public function createTableConfig(): TableConfig
    {
        return new TableConfig(
            entityKey:               $this->entityDescriptor()->key,
            jsAttributes:            $this->jsAttributes(),
            captionText:             $this->captionText(),
            expectedControllerClass: $this->expectedController(),
            columns:                 $this->columns(),
        );
    }

    public function createHeaderConfig(): AdminHeaderConfig
    {
        $e = $this->entityDescriptor();
        return new AdminHeaderConfig(
            title:          "{$e->displayName} List Manager",
            breadcrumbs: ['Dashboard', $e->displayName],
            primaryActions: $this->primaryActions(),
        );
    }

    public function searchPlaceholder(): string
    {
        return "Search {$this->entityDescriptor()->plural}...";
    }

    // ─── Required by subclasses ──────────────────────────────

    abstract protected function entityDescriptor(): EntityDescriptor;

    /** @return TableColumn[] */
    abstract protected function columns(): array;

    abstract protected function expectedController(): string;

    abstract protected function captionText(): string;

    // ─── Overridable defaults ────────────────────────────────

    /** @return array<string, string> */
    protected function jsAttributes(): array
    {
        $e = $this->entityDescriptor();
        return [
            'data-table-type' => $e->key,
            'data-entity-name' => $e->key,
            'data-entity-display-name' => $e->displayName,
            'data-entity-name-plural' => $e->plural,
            'data-empty-title' => "No {$e->displayName} found",
            'data-empty-message' => "Get started by adding your first {$e->key}",
            'data-empty-action-url' => $e->path('add'),
            'data-empty-action-text' => "Add {$e->displayName}",
        ];
    }

    /** @return HeaderButton[] */
    protected function primaryActions(): array
    {
        $e = $this->entityDescriptor();
        $type = $e->blockType !== '' ? "/{$e->blockType}" : '';
        return [
            new HeaderButton(
                label: 'Export',
                action: $e->path('export') . $type,
                formName: "{$e->key}_export_form",
                ariaLabel: "Export {$e->key} data",
                style: 'secondary',
                icon: 'icon-export',
                requiresEntityId: true,
                class: ['export'],
            ),
            new HeaderButton(
                label: 'Add New',
                action: $e->path('add') . $type,
                formName: "{$e->key}_add_form",
                method: 'get',
                ariaLabel: "Add new {$e->key}",
                style: 'primary',
                icon: 'icon-plus',
            ),
        ];
    }

    // ─── Helpers for column closures ─────────────────────────

    // protected function esc(?string $value): string
    // {
    //     return $this->presenter->show($value ?? '');
    // }

    protected function emptyPlaceholder(): string
    {
        return '—';
    }

    protected function rowActions(Entity $e, UuidInterface|string $id, string $confirmUrl, array $actionMethods = [], ?string $blockType = null): array
    {
        $e = $this->entityDescriptor();
        $show = $actionMethods['show'] ?? 'show';
        $edit = $actionMethods['edit'] ?? 'edit';

        $type = $e->blockType !== '' ? "/{$e->blockType}" : '';

        return [
            new ActionDefinition(
                action: $e->path("{$id}/" . $show . $type),
                method: 'get',
                icon: 'icon-eye',
                iconLabel: 'Eye',
                iconClasses: ['eye'],
                buttonType: 'submit',
                screenReaderText: "View {$e->displayName}",
                actionClass: 'view-action',
                csrfProtected: true,
            ),
            new ActionDefinition(
                action: $e->path("{$id}/" . $edit . $type),
                method: 'get',
                icon: 'icon-edit',
                iconLabel: 'Edit',
                iconClasses: ['edit'],
                buttonType: 'submit',
                screenReaderText: "Edit {$e->displayName}",
                actionClass: 'edit-action',
                csrfProtected: true,
            ),
            new ActionDefinition(
                action: $confirmUrl,
                method: 'post',
                icon: 'icon-trash',
                iconLabel: 'Delete',
                iconClasses: ['trash'],
                buttonType: 'button',
                screenReaderText: "Delete {$e->displayName}",
                buttonCustom: ['data-action' => 'confirm-delete'],
                csrfProtected: true,
                actionClass: 'delete',
                blockType: $blockType,
            ),
        ];
    }
}