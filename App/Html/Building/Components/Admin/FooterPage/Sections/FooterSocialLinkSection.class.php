<?php

declare(strict_types=1);

class FooterSocialLinkSection extends AbstractBaseHtmlSection
{
    use EntityDisplayTrait;
    use FooterSectionHeaderTrait;
    private const string MODAL_TYPE = 'social';

    private string $entityClass = FooterSocial::class;

    public function __construct(HtmlBuilder $htmlBuilder, IconBuilder $iconBuilder, private ButtonBuilder $button)
    {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getConfig(array $entities = []): array|AbstractHtmlComponent
    {
        $entities = $entities[$this->entityClass] ?? [];
        $pagination = $this->pagination[$this->entityClass] ?? [];
        $components = [
            $this->sectionHeader(
                title: 'Social Media Links',
                action: 'add-column',
                type:self::MODAL_TYPE,
                url: '/admin/footer-social/add',
                textSubmit: 'Add Social Link',
            ),
            $this->socialGrid($entities),
        ];
        if (!empty($pagination)) {
            $components[] = $this->htmlBuilder->htmlBlock($pagination);
        }
        return $components;
    }

    #[Override]
    public function getKey(): string
    {
        return FooterSectionKeys::SOCIALS->value;
    }

    /**
     * @param FooterSocial[] $entities
     *
     * @return null|AbstractHtmlComponent
     */
    private function socialGrid(array $entities): ?AbstractHtmlComponent
    {
        if (empty($entities)) {
            return null;
        }
        $html = $this->htmlBuilder;
        $socialCards = [];
        foreach ($entities as $entity) {
            $socialCards = array_merge($socialCards, [$this->socialItem($entity)]);
        }
        return $html->div()->class('social-grid')->id('social-grid')->add(
            ...$socialCards,
        );
    }

    private function socialItem(FooterSocial $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $title = $this->show($entity, 'name');
        $active = $html->tag('span')->class('status-badge');
        if ($entity->getIsActive()) {
            $active->class('active')->content('Active');
        } else {
            $active->class('inactive')->content('Inactive');
        }
        $icon = $this->show($entity, 'icon');
        // dd($icon);
        return $html->div()->class('social-card')->attribute('data-id', $this->show($entity, 'id'))
         ->add(
             $html->div()->class('social-icon')->add(
                 $this->iconBuilder->createIcon(
                     $icon,
                     $title,
                     [$this->show($entity, 'icon_class') ?? ''],
                 ),
             ),
             $html->div()->class('social-info')->add(
                 $html->tag('h3')->content($title),
                 $html->tag('code')->content($this->show($entity, 'url') ?? ''),
                 $html->tag('span')->class('platform-badge')->content($this->show($entity, 'platform')),
             ),
             $html->div()->class('social-status')->add(
                 $active,
             ),
             $this->socialActions($entity),
         );
    }

    private function socialActions(FooterSocial $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $entityId = $this->show($entity, 'id');

        $editBtn = $this->button->iconOnly(
            new IconConfig(
                icon: 'icon-edit',
                ariaLabel: 'Edit Social Link',
                iconClass: ['icon-btn'],
            ),
        )->build(
            new ButtonConfig(
                type: 'submit',
                attributes: [
                    'data-action' => 'edit-social',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        $deleteBtn = $this->button->iconOnly(
            new IconConfig(
                icon: 'icon-trash',
                ariaLabel: 'Delete Social Link',
                iconClass: ['icon-btn'],
            ),
        )->build(
            new ButtonConfig(
                type: 'submit',
                attributes: [
                    'data-action' => 'confirm-delete',
                    'data-id' => $entityId,
                    'data-modal-type' => self::MODAL_TYPE,
                ],
            ),
        );
        return $html->div()->class('social-actions')->add(
            $this->wrapButtonWithform(
                action: '/admin/footer-social/edit',
                entityId: $entityId,
                components:[$editBtn],
            ),
            $this->wrapButtonWithform(
                action: '/admin/footer-socials-confirm-deletion/confirm',
                entityId: $entityId,
                components:[$deleteBtn],
            ),
        );

        //    $html->button('button')->class('icon-btn')
        //     ->custom([
        //         'data-action' => 'edit-social',
        //         'data-id' => $entityId,
        //         'data-modal-type' => self::MODAL_TYPE,
        //     ])->add(
        //         $this->iconBuilder->createIcon('icon-edit', 'Edit', ['edit']),
        //     ),
        //     $html->button('button')->class('icon-btn', 'delete')
        //     ->custom([
        //         'data-action' => 'delete-social',
        //         'data-id' => $entityId,
        //     ])->add(
        //         $this->iconBuilder->createIcon('icon-trash', 'Delete', ['trash']),
        //     ),
    }
}