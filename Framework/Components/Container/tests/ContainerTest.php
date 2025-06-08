<?php

declare(strict_types=1);

/**
 * Basic tests for the improved Container.
 */
class ContainerTest
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function testBasicBinding(): void
    {
        $this->container->bind('test.value', 'hello world');
        $value = $this->container->get('test.value');

        assert($value === 'hello world', 'Basic binding failed');
        echo "✅ Basic binding test passed\n";
    }

    public function testSingletonBinding(): void
    {
        $this->container->singleton(TestService::class);

        $instance1 = $this->container->get(TestService::class);
        $instance2 = $this->container->get(TestService::class);

        assert($instance1 === $instance2, 'Singleton binding failed');
        echo "✅ Singleton binding test passed\n";
    }

    public function testParameterResolution(): void
    {
        $this->container->bindParams(TestServiceWithParams::class, [
            'name' => 'Test App',
            'version' => '1.0.0',
        ]);

        $service = $this->container->get(TestServiceWithParams::class);

        assert($service->getName() === 'Test App', 'Parameter resolution failed');
        assert($service->getVersion() === '1.0.0', 'Parameter resolution failed');
        echo "✅ Parameter resolution test passed\n";
    }

    public function testTaggedServices(): void
    {
        $this->container->bindWithTags(TestHandler1::class, null, ['handler']);
        $this->container->bindWithTags(TestHandler2::class, null, ['handler']);

        $handlers = $this->container->tagged('handler');

        assert(count($handlers) === 2, 'Tagged services failed');
        assert($handlers[0] instanceof TestHandler1, 'Tagged services failed');
        assert($handlers[1] instanceof TestHandler2, 'Tagged services failed');
        echo "✅ Tagged services test passed\n";
    }

    public function testMethodInjection(): void
    {
        $this->container->bind(TestService::class);

        $result = $this->container->call(function (TestService $service) {
            return $service->getName();
        });

        assert($result === 'TestService', 'Method injection failed');
        echo "✅ Method injection test passed\n";
    }

    public function testAliases(): void
    {
        $this->container->bind(TestService::class);
        $this->container->alias(TestService::class, 'test.service');

        $service1 = $this->container->get(TestService::class);
        $service2 = $this->container->get('test.service');

        assert(get_class($service1) === get_class($service2), 'Aliases failed');
        echo "✅ Aliases test passed\n";
    }

    public function testCircularDependencyDetection(): void
    {
        $this->container->bind(CircularA::class);
        $this->container->bind(CircularB::class);

        try {
            $this->container->get(CircularA::class);
            assert(false, 'Circular dependency detection failed');
        } catch (ContainerException $e) {
            assert(str_contains($e->getMessage(), 'Circular dependency'), 'Wrong exception message');
            echo "✅ Circular dependency detection test passed\n";
        }
    }

    public function runAllTests(): void
    {
        echo "Running Container Tests...\n\n";

        $this->setUp();
        $this->testBasicBinding();

        $this->setUp();
        $this->testSingletonBinding();

        $this->setUp();
        $this->testParameterResolution();

        $this->setUp();
        $this->testTaggedServices();

        $this->setUp();
        $this->testMethodInjection();

        $this->setUp();
        $this->testAliases();

        $this->setUp();
        $this->testCircularDependencyDetection();

        echo "\n🎉 All Container tests passed!\n";
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
        private string $name,
        private string $version
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}

class TestHandler1
{
}
class TestHandler2
{
}

class CircularA
{
    public function __construct(CircularB $b)
    {
    }
}

class CircularB
{
    public function __construct(CircularA $a)
    {
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new ContainerTest();
    $test->runAllTests();
}