<?php

declare(strict_types=1);

final readonly class ContainerClassRegistrator
{
    private function __construct()
    {
    }

    public static function register(App &$app) : void
    {
        foreach (self::bindClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'bind', $app);
            //$app->bind($abstract, $concrete);
        }
        foreach (self::singletonClasses() as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'singleton', $app);
        }
    }

    private static function registerClass(string $abstract, mixed $concrete, string $function, App $app) : void
    {
        if (is_array($concrete)) {
            $class = key($concrete);
            $class = is_numeric($class) ? $abstract : $class;
            $args = $concrete[$class] ?? $concrete;
            $app->$function($abstract, $class, false, $args);
        } else {
            $app->$function($abstract, $concrete);
        }
    }

    private static function bindClasses() : array
    {
        return [
            FlashInterface::class => Flash::class,
            AbstractFactory::class => ConcreteFactory1::class,
            RooterInterface::class => Rooter::class,
            CacheStorageInterface::class => NativeCacheStorage::class,
            CacheInterface::class => Cache::class,
            FilesSystemInterface::class => FileSystem::class,
            SuperGlobalsInterface::class => SuperGlobals::class,
            CookieStoreInterface::class => NativeCookieStore::class,
            CookieInterface::class => Cookie::class,
            RouteDispatcher::class => [false,
                function () {
                    return YamlFile::get('middlewares');
                }],
            DataMapperEnvironmentConfig::class => [function () {
                return YamlFile::get('database');
            }, 'mysql',
            ],

        ];
    }

    private static function singletonClasses() : array
    {
        return [
            TokenInterface::class => Token::class,
            ViewInterface::class => View::class,
            CollectionInterface::class => Collection::class,
            EntityManagerInterface::class => EntityManager::class,
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            SessionInterface::class => Session::class,
            DataMapperInterface::class => DataMapper::class,
        ];
    }
}