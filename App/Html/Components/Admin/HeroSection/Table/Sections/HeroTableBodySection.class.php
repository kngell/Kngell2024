<?php

declare(strict_types=1);

class HeroTableBodySection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        private readonly HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($builder, $icon);
    }

    public function getKey(): string
    {
        return TableListSection::TBODY->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::TBODY;
    }

    public function getConfig(array $context = []): array|AbstractHtmlComponent
    {
        $this->context = $context;

        return [
            [
                'key' => 'select',
                'cellType' => TableCellType::START,
                'checkboxName' => 'heroes[]',
                'thumbnailAlt' => fn (Hero $h) => $this->presenter->showField($h, 'image_alt'),
                'thumbnail' => fn (Hero $h) => $this->escape($h->getImageUrl()),
                'title' => fn (Hero $h) => $this->escape($h->getTitle()),
                'subtitle' => fn (Hero $h) => $this->escape($h->getSubtitle()),
            ],

            [
                'key' => 'title',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (Hero $h) => $this->escape($h->getTitle()),
            ],

            [
                'key' => 'specialized-title',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (Hero $h) => $this->escape($h->getSpecializedTitle()),
            ],

            [
                'key' => 'introduction',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (Hero $h) => $this->escape($h->getIntroduction()),
            ],

            [
                'key' => 'cta_text',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (Hero $h) => $this->escape($h->getCtaText()),
            ],

            [
                'key' => 'cta_link',
                'cellType' => TableCellType::NORMAL,
                'value' => fn (Hero $h) => $this->escape($h->getCtaLink()),
            ],
            [
                'key' => 'action',
                'cellType' => TableCellType::ACTION,
                'idField' => 'public_id',
                'id' => fn (Hero $h) => $this->presenter->showField($h, 'public_id'),
                'actions' => fn (Hero $h) => $this->getActions($h),
            ],
        ];
    }

    /**
     * @return ActionDefinition[]
     */
    private function getActions(Hero $hero): array
    {
        $id = $hero->getPublicId();
        return [
            new ActionDefinition(
                action: "/hero-page/{$id}/show",
                method: 'post',
                icon: 'icon-eye',
                iconLabel: 'Eye',
                iconClasses: ['eye'],
                buttonType: 'submit',
                screenReaderText: 'View Product',
                actionClass: 'view-action',
                csrfProtected: true,
            ),
            new ActionDefinition(
                action: "/hero-page/{$id}/product-edit",
                method: 'get',
                icon: 'icon-edit',
                iconLabel: 'Edit',
                iconClasses: ['edit'],
                buttonType: 'submit',
                screenReaderText: 'Edit Hero',
                actionClass: 'edit-action',
                csrfProtected: false,
            ),
            new ActionDefinition(
                action: '/hero-confirm-deletion/confirm',
                method: 'post',
                icon: 'icon-trash',
                iconLabel: 'Delete',
                iconClasses: ['trash'],
                buttonType: 'button',
                screenReaderText: 'Delete Hero',
                actionClass: 'trash-action',
                buttonCustom: ['data-action' => 'confirm-delete'],
                csrfProtected: true,
            ),
        ];
    }

    private function formatVariationCount(array $variations): string
    {
        $count = count($variations);
        return match (true) {
            $count === 0 => 'No variants',
            $count === 1 => '1 Variant',
            default => "{$count} Variants",
        };
    }
}