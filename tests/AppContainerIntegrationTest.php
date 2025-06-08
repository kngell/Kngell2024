<?php

declare(strict_types=1);

/**
 * Integration tests for App container functionality.
 */
class AppContainerIntegrationTest
{
    private App $app;

    public function setUp(): void
    {
        // Create fresh app instance for testing
        $this->app = new App();
    }

    public function testStaticDependencyInjection(): void
    {
        // Test static DI methods
        App::diBind('test.service', TestService::class);

        $service = App::diGet('test.service');
        assert($service instanceof TestService, 'Static DI get failed');

        $hasService = App::diHas('test.service');
        assert($hasService === true, 'Static DI has failed');

        echo "✅ Static dependency injection test passed\n";
    }

    public function testMethodInjection(): void
    {
        App::diBind(TestService::class);

        $result = App::diCall(function (TestService $service) {
            return $service->getName();
        });

        assert($result === 'TestService', 'Method injection failed');
        echo "✅ Method injection test passed\n";
    }

    public function testGlobalParameters(): void
    {
        $this->app->setGlobalParameters([
            'test_param' => 'test_value',
            'test_number' => 42,
        ]);

        $this->app->bind(TestServiceWithParams::class);

        $service = $this->app->resolve(TestServiceWithParams::class);

        assert($service->getTestParam() === 'test_value', 'Global parameter injection failed');
        assert($service->getTestNumber() === 42, 'Global parameter injection failed');

        echo "✅ Global parameters test passed\n";
    }

    public function testAliases(): void
    {
        $this->app->bind(TestService::class);
        $this->app->alias(TestService::class, 'test');

        $service1 = $this->app->resolve(TestService::class);
        $service2 = $this->app->resolve('test');

        assert(get_class($service1) === get_class($service2), 'Aliases failed');
        echo "✅ Aliases test passed\n";
    }

    public function testTaggedServices(): void
    {
        $this->app->bindWithTags(TestHandler1::class, null, ['handler']);
        $this->app->bindWithTags(TestHandler2::class, null, ['handler']);

        $handlers = $this->app->tagged('handler');

        assert(count($handlers) === 2, 'Tagged services count failed');
        assert($handlers[0] instanceof TestHandler1, 'Tagged services type failed');
        assert($handlers[1] instanceof TestHandler2, 'Tagged services type failed');

        echo "✅ Tagged services test passed\n";
    }

    public function testFactoryBinding(): void
    {
        $this->app->factory(TestFactoryService::class, function ($container) {
            return new TestFactoryService('factory_created');
        });

        $service = $this->app->resolve(TestFactoryService::class);

        assert($service->getValue() === 'factory_created', 'Factory binding failed');
        echo "✅ Factory binding test passed\n";
    }

    public function testComplexDependencyResolution(): void
    {
        // Set up complex dependencies
        $this->app->setGlobalParameters([
            'api_key' => 'test_key_123',
            'timeout' => 30,
        ]);

        $this->app->bind(TestRepository::class);
        $this->app->bind(TestComplexService::class);

        $service = $this->app->resolve(TestComplexService::class);

        assert($service instanceof TestComplexService, 'Complex service resolution failed');
        assert($service->getApiKey() === 'test_key_123', 'Complex parameter injection failed');
        assert($service->getTimeout() === 30, 'Complex parameter injection failed');

        echo "✅ Complex dependency resolution test passed\n";
    }

    public function testContainerRegistratorIntegration(): void
    {
        // Test that the registrator works with new container
        ContainerClassRegistrator::register($this->app);

        // Test some core services are registered
        assert($this->app->has(Request::class), 'Core service not registered');
        assert($this->app->has('request'), 'Core alias not registered'); // Should have alias

        // Test tagged services
        $coreServices = $this->app->tagged('core');
        assert(! empty($coreServices), 'Tagged services not registered');

        echo "✅ Container registrator integration test passed\n";
    }

    public function runAllTests(): void
    {
        echo "Running App Container Integration Tests...\n\n";

        $this->setUp();
        $this->testStaticDependencyInjection();

        $this->setUp();
        $this->testMethodInjection();

        $this->setUp();
        $this->testGlobalParameters();

        $this->setUp();
        $this->testAliases();

        $this->setUp();
        $this->testTaggedServices();

        $this->setUp();
        $this->testFactoryBinding();

        $this->setUp();
        $this->testComplexDependencyResolution();

        $this->setUp();
        $this->testContainerRegistratorIntegration();

        echo "\n🎉 All App Container integration tests passed!\n";
    }
}

// Test classes
class TestService
{
    public function getName(): string
    {
        return 'TestService';
    }
}

class TestServiceWithParams
{
    public function __construct(
        private string $test_param,
        private int $test_number
    ) {
    }

    public function getTestParam(): string
    {
        return $this->test_param;
    }

    public function getTestNumber(): int
    {
        return $this->test_number;
    }
}

class TestHandler1
{
}
class TestHandler2
{
}

class TestFactoryService
{
    public function __construct(private string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

class TestRepository
{
    public function findAll(): array
    {
        return ['test_data'];
    }
}

class TestComplexService
{
    public function __construct(
        private TestRepository $repository,
        private string $api_key,
        private int $timeout,
        private ?TestService $optionalService = null
    ) {
    }

    public function getApiKey(): string
    {
        return $this->api_key;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AppContainerIntegrationTest();
    $test->runAllTests();
}