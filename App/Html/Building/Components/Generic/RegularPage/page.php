<?php 
 public function page(): array
    {
        $target = $this->getTarget();
        $config = $this->factory->createPageConfig();
        $expectedController = $config->getExpectedControllerClass();
        if ($expectedController !== null && !$target instanceof $expectedController) {
            throw new HtmlDecoratorException(
                sprintf(
                    '%s requires %s, got %s',
                    self::class,
                    $expectedController,
                    get_class($target),
                ),
            );
        }

        if (!$target instanceof Controller) {
            throw new HtmlDecoratorException(
                sprintf(
                    '%s requires Controller, got %s',
                    self::class,
                    get_class($target),
                ),
            );
        }

        $provider = new RegularPageProvider($config, $this->iconBuilder);

        $page = new RegularPage(
            $provider,
            $target->getSectionManager(),
            $target->getBuilder(),
            $config->getEnumClass(),
            $this->items,
        );
        $assets = $config->getAssets();
        if (!empty($assets)) {
            $assets = [self::ASSET_KEY => $assets];
        }

        return parent::page() + $assets + $page->getHtmlElements();
    }
?>