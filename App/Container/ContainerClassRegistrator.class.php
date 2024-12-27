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
        }
        foreach (self::singletonClasses($app) as $abstract => $concrete) {
            self::registerClass($abstract, $concrete, 'singleton', $app);
        }
    }

    private static function registerClass(string $abstract, mixed $concrete, string $function, App $app) : void
    {
        if (is_array($concrete)) {
            list($class, $args) = self::params($concrete, $abstract);
            $app->$function($abstract, $class, false, $args);
        } else {
            $app->$function($abstract, $concrete);
        }
    }

    private static function params(array $concrete, string $abstract)
    {
        if (count($concrete) === 1) {
            return [$abstract, $concrete[0]];
        }
        $class = $concrete[0];
        if (! is_string($class)) {
            $class = $abstract;
        } else {
            unset($concrete[0]);
            $concrete = array_values($concrete);
        }
        return [$class, $concrete];
    }

    private static function bindClasses() : array
    {
        return [
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
            DatabaseEnvironmentConfig::class => [function () {
                return YamlFile::get('database');
            }, 'mysql',
            ],

        ];
    }

    private static function singletonClasses(App $app) : array
    {
        return [
            DatabaseConnexionInterface::class => PDOConnexion::class,
            UsersModel::class => UsersModel::class,
            FlashInterface::class => Flash::class,
            TokenInterface::class => Token::class,
            ViewInterface::class => View::class,
            CollectionInterface::class => Collection::class,
            EntityManagerInterface::class => EntityManager::class,
            SessionEnvironment::class => SessionEnvironment::class,
            SessionStorageInterface::class => NativeSessionStorage::class,
            SessionInterface::class => Session::class,
            DataMapperInterface::class => DataMapper::class,
            HashInterface::class => [Hash::class, function () use ($app) {
                return $app->getAppConfig()->getConfig()['security'];
            }],
        ];
    }
}
