# Container Migration Guide

## Overview

This guide helps you migrate from the old Container implementation to the new improved version with advanced dependency injection capabilities.

## Key Improvements

### ✅ **What's New:**

1. **Advanced Type Resolution** - Handles any parameter type (scalar, object, array, union types, etc.)
2. **Circular Dependency Detection** - Prevents infinite loops
3. **Tagged Services** - Group and resolve related services
4. **Method Injection** - Call methods with automatic dependency injection
5. **Contextual Binding** - Different implementations based on context
6. **Performance Optimizations** - Better caching and resolution strategies
7. **Better Error Handling** - Clear, actionable error messages

### 🔧 **Backward Compatibility:**

- All existing code continues to work
- Same method signatures for core functionality
- Gradual migration path available

## Migration Steps

### 1. **Update Basic Usage (No Changes Required)**

Your existing code continues to work:

```php
// ✅ Still works exactly the same
$container->bind(UserService::class, UserService::class);
$container->singleton(Logger::class, FileLogger::class);
$container->get(UserService::class);
```

### 2. **Enhanced Parameter Binding**

**Before:**

```php
$container->bindParams(DatabaseConnection::class, [
    'host' => 'localhost',
    'port' => 3306
]);
```

**After (Enhanced):**

```php
// More flexible parameter binding
$container->bindParams(DatabaseConnection::class, [
    'host' => 'localhost',
    'port' => 3306,
    'options' => ['charset' => 'utf8mb4']
]);

// Or use global parameters
$container->setGlobalParameters([
    'app_name' => 'My App',
    'debug' => true
]);
```

### 3. **Improved Error Handling**

**Before:**

```php
try {
    $service = $container->get(NonExistentService::class);
} catch (BindingResolutionException $e) {
    // Generic error handling
}
```

**After:**

```php
try {
    $service = $container->get(NonExistentService::class);
} catch (ContainerException $e) {
    // More specific error messages
    echo $e->getMessage(); // "Cannot resolve [NonExistentService]: Class does not exist"
}
```

### 4. **New Advanced Features**

#### **Tagged Services:**

```php
// Group related services
$container->bindWithTags(EmailHandler::class, null, ['notification']);
$container->bindWithTags(SmsHandler::class, null, ['notification']);

// Resolve all at once
$handlers = $container->tagged('notification');
```

#### **Method Injection:**

```php
// Call methods with automatic dependency injection
$result = $container->call([UserController::class, 'index']);

// Call closures with DI
$result = $container->call(function(UserService $service) {
    return $service->getAllUsers();
});
```

#### **Factory Bindings:**

```php
$container->factory(CacheManager::class, function($container) {
    $driver = $container->get('cache.driver');
    return match($driver) {
        'redis' => new RedisCache(),
        'file' => new FileCache(),
        default => new MemoryCache()
    };
});
```

#### **Aliases:**

```php
$container->alias(UserRepository::class, 'users');
$userRepo = $container->get('users'); // Same as UserRepository::class
```

## Advanced Type Resolution

### **Built-in Types:**

```php
class MyService
{
    public function __construct(
        private string $apiKey,        // ✅ Automatically resolved
        private int $timeout,          // ✅ Automatically resolved
        private array $config,         // ✅ Automatically resolved
        private bool $debug = false    // ✅ Uses default value
    ) {}
}

$container->bindParams(MyService::class, [
    'apiKey' => 'secret-key',
    'timeout' => 30,
    'config' => ['retries' => 3]
]);
```

### **Union Types (PHP 8+):**

```php
class ModernService
{
    public function __construct(
        private Logger|NullLogger $logger,     // ✅ Tries Logger first, then NullLogger
        private string|int $identifier         // ✅ Resolves based on bound value
    ) {}
}
```

### **Nullable Types:**

```php
class FlexibleService
{
    public function __construct(
        private ?Logger $logger = null,        // ✅ Uses null if Logger not bound
        private ?CacheInterface $cache = null  // ✅ Optional dependency
    ) {}
}
```

## Performance Optimizations

### **Auto-wiring Control:**

```php
// Disable auto-wiring for performance in production
$container->setAutoWiring(false);

// Explicit bindings for better performance
$container->bind(UserService::class, function($container) {
    return new UserService(
        $container->get(UserRepository::class),
        $container->get(Logger::class)
    );
});
```

### **Singleton Management:**

```php
// Efficient singleton resolution
$container->singleton(ExpensiveService::class);

// Instance management
$container->instance('current_user', $user);
```

## Error Prevention

### **Circular Dependency Detection:**

```php
// Old: Would cause infinite loop
// New: Throws clear error message
class ServiceA {
    public function __construct(ServiceB $b) {}
}

class ServiceB {
    public function __construct(ServiceA $a) {}
}

try {
    $container->get(ServiceA::class);
} catch (ContainerException $e) {
    echo $e->getMessage(); // "Circular dependency detected: ServiceA -> ServiceB -> ServiceA"
}
```

## Testing Improvements

### **Container Isolation:**

```php
public function testSomething(): void
{
    $container = new Container(); // Fresh container for test
    $container->bind(UserRepository::class, MockUserRepository::class);

    // Test with mocked dependencies
    $service = $container->get(UserService::class);

    // Clean up
    $container->flush();
}
```

### **Rebound Callbacks for Testing:**

```php
$container->rebinding(Logger::class, function($container, $logger) {
    if ($container->get('app.testing')) {
        $logger->setLevel('debug');
    }
});
```

## Common Migration Patterns

### **1. Service Provider Pattern:**

```php
class DatabaseServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(Database::class, function($container) {
            return new Database(
                $container->get('config.database.host'),
                $container->get('config.database.port')
            );
        });

        $container->bind(UserRepository::class, DatabaseUserRepository::class);
        $container->alias(UserRepository::class, 'users');
    }
}
```

### **2. Configuration-based Binding:**

```php
$config = [
    'bindings' => [
        UserRepository::class => DatabaseUserRepository::class,
        Logger::class => FileLogger::class,
    ],
    'singletons' => [
        Database::class,
        Cache::class,
    ],
    'parameters' => [
        'app.name' => 'My Application',
        'app.debug' => true,
    ]
];

foreach ($config['bindings'] as $abstract => $concrete) {
    $container->bind($abstract, $concrete);
}

foreach ($config['singletons'] as $singleton) {
    $container->singleton($singleton);
}

$container->setGlobalParameters($config['parameters']);
```

## Troubleshooting

### **Common Issues:**

1. **"Cannot resolve parameter [name]"**

   - Solution: Use `bindParams()` or `setGlobalParameters()`

2. **"Circular dependency detected"**

   - Solution: Refactor dependencies or use factory pattern

3. **"Class is not instantiable"**

   - Solution: Bind interface to concrete implementation

4. **Performance issues**
   - Solution: Disable auto-wiring and use explicit bindings

### **Debugging:**

```php
// Check if service is bound
if ($container->has(UserService::class)) {
    $service = $container->get(UserService::class);
}

// Check binding details
$binding = $container->getBinding(UserService::class); // Internal method
```

## Best Practices

1. **Use interfaces for dependencies**
2. **Prefer constructor injection over property injection**
3. **Use tagged services for plugin architectures**
4. **Leverage factory bindings for complex object creation**
5. **Use aliases for commonly accessed services**
6. **Set global parameters for application-wide configuration**
7. **Use method injection for controllers and commands**
8. **Disable auto-wiring in production for better performance**

## Next Steps

1. **Test existing functionality** - Ensure everything still works
2. **Gradually adopt new features** - Start with tagged services and aliases
3. **Optimize performance** - Use explicit bindings for frequently resolved services
4. **Improve error handling** - Catch specific ContainerException types
5. **Leverage advanced types** - Use union types and nullable dependencies where appropriate
