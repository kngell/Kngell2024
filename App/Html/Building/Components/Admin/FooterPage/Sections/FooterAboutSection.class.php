<?php

declare(strict_types=1);

class FooterAboutSection extends AbstractBaseHtmlSection
{
    use EntityDisplayTrait;
    use FooterSectionHeaderTrait;
    private const string MODAL_TYPE = 'about';

    private string $entityClass = FooterAbout::class;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private ?ButtonBuilder $buttonBuilder = null,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getConfig(array $entities = [], array $pagination = []): array|AbstractHtmlComponent
    {
        $entities = $entities[$this->entityClass] ?? [];
        $pagination = $this->pagination[$this->entityClass] ?? [];
        $components = [
            $this->sectionHeader(
                title: 'Footer About',
                action: 'add-about',
                type: self::MODAL_TYPE,
                url: '/admin/footer-about/add',
                textSubmit: 'Add New',
            ),
            $this->aboutItems($entities),
        ];
        if (!empty($pagination)) {
            $components[] = $this->htmlBuilder->htmlBlock($pagination);
        }
        return $components;
    }

    #[Override]
    public function getKey(): string
    {
        return FooterSectionKeys::ABOUT->value;
    }

    private function aboutItems(array $entities): ?AbstractHtmlComponent
    {
        $aboutItems = [];
        $html = $this->htmlBuilder;
        foreach ($entities as $entity) {
            $aboutItems = array_merge($aboutItems, [$this->aboutItem($entity)]);
        }
        return $html->div()->class('about-items')->add(
            ...$aboutItems,
        );
    }

    private function aboutItem(FooterAbout $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->div()->class('about-item')->add(
            $this->itemContent($entity),
            $this->itemActions($entity),
        );
    }

    private function itemContent(FooterAbout $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $content = $this->getContentOverview($this->show($entity, 'content'), 100);
        $active = $html->tag('span')->class('status-badge ');
        if ($entity->getIsActive()) {
            $active->class('active')->content('Active');
        } else {
            $active->content('Inactive');
        }
        return $html->div()->class('about-item__content')
        ->add(
            $html->tag('p')->class('about-item__content-text')->content($content),
            $html->div()->class('about-item__content-meta')->add(
                $active,
                $html->tag('span')->class('valid_from')->content('Valid from: ' . $this->show($entity, 'valid_from')),
            ),
        );
    }

    private function itemActions(FooterAbout $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $entityId = $this->show($entity, 'id');
        $editBtn = $this->buttonBuilder->iconOnly(
            new IconConfig(
                icon: 'icon-edit',
                ariaLabel: 'Edit',
                iconClass: ['edit-existing'],
            ),
        )->build(
            new ButtonConfig(
                type: 'button',
                label: 'Edit',
                attributes:[
                    'data-action' => 'edit-about',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        $deleteBtn = $this->buttonBuilder->iconOnly(
            new IconConfig(
                icon: 'icon-trash',
                ariaLabel: 'Delete',
                iconClass: ['delete'],
            ),
        )->build(
            new ButtonConfig(
                type: 'button',
                label: 'Delete',
                attributes:[
                    'data-action' => 'confirm-delete',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        return $html->div()->class('about-item__actions')->add(
            $this->wrapButtonWithform(
                action: '/admin/footer-about/edit',
                entityId: $entityId,
                classes: ['edit-btn'],
                components: [$editBtn],
            ),
            $this->wrapButtonWithform(
                action: '/admin/footer-about-confirm-deletion/confirm',
                entityId: $entityId,
                classes: ['delete-btn'],
                components: [$deleteBtn],
            ),
        );
    }
}