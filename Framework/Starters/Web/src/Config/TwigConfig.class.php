<?php

declare(strict_types=1);

use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\FilesystemLoader;

#[Configuration]
class TwigConfig
{
    public function __construct(
        #[Property(name: 'kngell-ecom.rootDirectory')]
        private string $rootDirectory,
        #[Property(name: 'kngell-ecom.cacheDirectory')]
        private string $cacheDirectory,
        #[Property(name: 'twig.extension')]
        private array $twiExtClassNames = [],
    ) {
    }

    #[Bean]
    public function twigEnvironment() : Environment
    {
        $loader = new FilesystemLoader('Templates', $this->rootDirectory);
        $twig = new Environment($loader, [
            'cache' => $this->cacheDirectory . DIRECTORY_SEPARATOR . 'Twig',
        ]);

        foreach ($this->twiExtClassNames as $className) {
            if (! is_subclass_of($className, ExtensionInterface::class)) {
                continue;
            }

            $twig->addExtension(new $className);
        }
        return $twig;
    }
}