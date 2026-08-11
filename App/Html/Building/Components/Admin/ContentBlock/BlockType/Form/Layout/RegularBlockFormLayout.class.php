<?php

declare(strict_types=1);

class RegularBlockFormLayout implements ContentBlockFormLayoutInterface
{
    public function __construct(private ?string $page = null)
    {
    }

    #[Override]
    public function getSectionGroups(): ?SectionGroupManager
    {
        $sectionGroupManager = SectionGroupManager::create();

        return $sectionGroupManager
          ->addGroup(
              SectionGroup::create('left-content')
                  ->setSectionKeys([BlockTypeSection::BASICS->value])
                  ->setPosition('left')
                  ->setWrapperClass(['content-block-frm__left']),
          )
          ->addGroup(
              SectionGroup::create('relationships')
                  ->setSectionKeys([BlockTypeSection::PRODUCT->value])
                  ->setPosition('left')
                  ->setWrapperClass(['content-block-frm__left']),
          )
          ->addGroup(
              SectionGroup::create('right-content')
                  ->setSectionKeys([BlockTypeSection::MEDIA->value])
                  ->setPosition('right')
                  ->setWrapperClass(['content-block-frm__right']),
          )->addGroup(
              SectionGroup::create('metadata')
                  ->setSectionKeys([BlockTypeSection::METADATA->value])
                  ->setPosition('left')
                  ->setWrapperClass(['content-block-frm__left']),
          );
    }

    #[Override]
    public function getTabConfig(): ?TabConfig
    {
        return TabConfig::create()
           ->setTabContainerClass(['content-block-frm__tabs'])
           ->setContentContainerClass(['content-block-frm__content'])

           // Tab 1: Basic Information
           ->addTab(
               TabItem::create(
                   id: 'tab-basics',
                   title: 'Basic Information',
               )
                   ->setSectionGroups(['left-content', 'right-content'])
                   ->setState('default')
                   ->setContentClass('content-block-frm__content--basics')
                   ->setPriority(1),
           )
           // Tab2 : RelationShips and settings
           ->addTab(
               TabItem::create(
                   id: 'tab-relationships',
                   title: 'Product Relationships',
               )
                   ->setSectionGroups(['relationships'])
                   ->setState('default')
                   ->setContentClass('content-block-frm__content--relationships')
                   ->setPriority(2),
           )

           // Tab 2: Small Banner Settings
           ->addTab(
               TabItem::create(
                   id:'tab-metadata',
                   title: $this->page . ' Metadata',
               )
                   ->setSectionGroups(['metadata'])
                   ->setContentClass('content-block-frm__content--metadata')
                   ->setPriority(3),
           );
    }
}