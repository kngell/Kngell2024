<?php

declare(strict_types=1);

final class FooterPageConfigFactory extends AbstractPageConfigFactory
{
    #[Override]
    public function headerTitle(): string
    {
        return 'Footer Page Manager';
    }

    #[Override]
    public function breadcrumbs(): array
    {
        return ['Dashboard', 'Pages', 'Footer Page'];
    }

    public function buildSections(): array
    {
        return [
            FooterColumnSection::class,
            FooterLinkSection::class,
            FooterAboutSection::class,
            FooterSocialLinkSection::class,
            FooterSettings::class,
        ];
    }

    public function getExpectedControllerClass(): ?string
    {
        return FooterPageController::class;
    }

    protected function getDefaultInputLayoutName(): ?string
    {
        return 'input';
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new FieldLayout($this->iconBuilder),
        ];
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: EntityKey::FOOTER_COLUMN->value,
            displayName: 'Column',
            plural: EntityKey::FOOTER_COLUMN->getPlural(),
            basePath: EntityKey::FOOTER_COLUMN->getBasePath(),
        );
    }

    protected function getEntityKey(): ?string
    {
        return $this->entityDescriptor()->key;
    }

    protected function getLayoutBuilder(): ?PageLayoutInterface
    {
        return new TabbedPageLayout($this->tabConfig(), $this->sectionGroupManager()->getAllGroups());
    }

    protected function getEnumClass(): ?string
    {
        return FooterSectionKeys::class;
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new TextareaFieldHandler(),
            new NativeSelectFieldHandler(),
        ];
    }

    protected function hasContainer(): bool
    {
        return true;
    }

    protected function getContainerClass(): array
    {
        return ['footer-page__content-bis'];
    }

    protected function getFormId(): ?string
    {
        return 'footer-page__content-id';
    }

    protected function getAssets(): array
    {
        return[
            'css' => 'css/backend/admin/pages/footer-page',
            'js' => 'js/backend/pages/footer-main',
            'sectionClass' => 'xxxxx',
        ];
    }

    protected function getdisplayKey(): ?string
    {
        return FooterSectionKeys::COLUMN->value;
    }

    #[Override]
    protected function defaultSectionIcon(): string
    {
        return 'icon-edit';
    }

    protected function sectionGroupManager(): ?SectionGroupManager
    {
        $sectionGroupManager = SectionGroupManager::create();

        return $sectionGroupManager
          ->addGroup(
              SectionGroup::create('footer-column')
                  ->setSectionKeys([
                      FooterSectionKeys::COLUMN->value,
                  ])
                  ->setPosition('full')
                  ->setWrapperClass(['footer-content__columns']),
          )->addGroup(
              SectionGroup::create('footer-link')
                  ->setSectionKeys([
                      FooterSectionKeys::LINK->value,
                  ])
                  ->setPosition('full')
                  ->setWrapperClass(['footer-content__links']),
          )->addGroup(
              SectionGroup::create('footer-about')
                  ->setSectionKeys([
                      FooterSectionKeys::ABOUT->value,
                  ])
                  ->setPosition('full')
                ->setWrapperClass(['footer-content__about']),
          )->addGroup(
              SectionGroup::create('footer-social')
                  ->setSectionKeys([
                      FooterSectionKeys::SOCIALS->value,
                  ])
                  ->setPosition('full')
                  ->setWrapperClass(['footer-content__socials']),
          )->addGroup(
              SectionGroup::create('footer-settings')
                  ->setSectionKeys([
                      FooterSectionKeys::SETTINGS->value,
                  ])
                  ->setPosition('full')
                  ->setWrapperClass(['footer-content__settings']),
          );
    }

    protected function tabConfig(): ?TabConfig
    {
        return TabConfig::create()
              ->setTabContainerClass([$this->getContainerClass()[0] . '__tabs'])
              ->setContentContainerClass([$this->getContainerClass()[0] . '__content', 'footer-content'])

              // Tab 1: Basic Information
              ->addTab(
                  TabItem::create(
                      id: 'tab-columns',
                      title: 'Menu columns',
                  )
                      ->setSectionGroups(['footer-column'])
                      ->setState('default')
                      ->setContentClass('footer-page__content--columns')
                      ->setPriority(1),
              )
        // Tab2 : RelationShips and settings
          ->addTab(
              TabItem::create(
                  id: 'tab-links',
                  title: 'Menu Links',
              )->setSectionGroups(['footer-link'])
            ->setContentClass('footer-page__content--links')
            ->setPriority(2),
          )->addTab(
              TabItem::create(
                  id:'tab-about',
                  title: 'About Section',
              )
                  ->setSectionGroups(['footer-about'])
                  ->setContentClass('footer-page__content--about')
                  ->setPriority(3),
          )->addTab(
              TabItem::create(
                  id:'tab-socials',
                  title: 'Social Links',
              )
                  ->setSectionGroups(['footer-social'])
                  ->setContentClass('footer-page__content--socials')
                  ->setPriority(4),
          )->addTab(
              TabItem::create(
                  id:'tab-settings',
                  title: 'Settings',
              )
                  ->setSectionGroups(['footer-settings'])
                  ->setContentClass('footer-page__content--settings')
                  ->setPriority(5),
          );
    }
}