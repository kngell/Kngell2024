<?php

declare(strict_types=1);
class SectionProviderFactory
{
    private array $providers = [];
    private array $factories = [];

    public function __construct(
        private ContainerInterface $container,
    ) {
        $this->registerFactories();
    }

    public function getProvider(string $type): ?SectionProviderInterface
    {
        if (!isset($this->providers[$type])) {
            $factoryClass = $this->factories[$type] ?? null;

            if (!$factoryClass) {
                return null;
            }

            $factory = $this->container->get($factoryClass);

            if (!$factory instanceof SectionProviderFactoryInterface) {
                throw new InvalidArgumentException('Factory must implement SectionProviderFactoryInterface');
            }

            $this->providers[$type] = $factory->create();
        }

        return $this->providers[$type];
    }

    private function registerFactories(): void
    {
        $this->factories = [
            'product_form' => ProductFormSectionProviderFactory::class,
            'product_confirm_deletion' => ConfirmDeletionSectionProviderFactory::class,
            'header' => HeaderSectionProviderFactory::class,
            'index_page' => IndexSectionProviderFactory::class,
            'admin_navbar' => AdminNavSectionProviderFactory::class,
            'hero-section-form' => HeroFormSectionProviderFactory::class,
            'small-banner-form' => SmallBannerSectionProviderFactory::class,
            'category-form' => CategoryFormProviderFactory::class,
        ];
    }
}