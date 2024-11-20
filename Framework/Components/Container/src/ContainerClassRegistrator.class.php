<?php

declare(strict_types=1);

final readonly class ContainerClassRegistrator
{
    private function __construct()
    {
    }

    public static function register(App $app) : void
    {
        foreach (self::bindClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'bind', $app);
            $app->bind($abstract, $concrete);
        }
        foreach (self::singletonClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'singleton', $app);
        }
    }

    private static function registerClass(string $abstract, mixed $concrete, string $function, App $app) : void
    {
        if (is_array($concrete)) {
            $class = key($concrete);
            $args = $concrete[$class];
            $app->$function($abstract, $class, $args);
        } else {
            $app->$function($abstract, $concrete);
        }
    }

    private static function bindClasses() : array
    {
        return [
            RooterInterface::class => Rooter::class,
            CacheStorageInterface::class => NativeCacheStorage::class,
            CacheInterface::class => Cache::class,
            FilesSystemInterface::class => FileSystem::class,
            SessionInterface::class => Session::class,
            SuperGlobalsInterface::class => SuperGlobals::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,
        ];
    }

    private static function singletonClasses() : array
    {
        return [
            DatabaseConnexionInterface::class => [
                PDOConnexion::class => function () {
                    $file = dirname(getcwd()) . '/App/Config/database.yaml';
                    $credentials = YamlFile::get($file);
                    return $credentials['driver']['mysql'];
                },

            ],
        ];
    }
}