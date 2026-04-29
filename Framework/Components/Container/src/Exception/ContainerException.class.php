<?php

declare(strict_types=1);

class ContainerException extends RuntimeException
{
    /** @var array<string> */
    private array $resolutionStack;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $resolutionStack = [],
    ) {
        $this->resolutionStack = $resolutionStack;
        parent::__construct($message, $code, $previous);
    }

    public function getResolutionStack(): array
    {
        return $this->resolutionStack;
    }

    // =============================================
    // YOUR EXISTING FACTORY METHOD
    // =============================================

    public static function circularDependency(
        string $abstract,
        array $stack,
    ): self {
        return new self(
            sprintf(
                'Circular dependency detected while resolving [%s]. '
                . 'Resolution path: %s → [%s]',
                $abstract,
                implode(' → ', $stack),
                $abstract,
            ),
            0,
            null,
            $stack,
        );
    }

    // =============================================
    // NEW FACTORY METHODS
    // =============================================

    /**
     * Class or concrete implementation cannot be found.
     */
    public static function unresolvable(
        string $abstract,
        string $concrete,
        array $stack = [],
    ): self {
        // Build a specific, actionable error message
        if ($abstract === $concrete) {
            $message = sprintf(
                'Unable to resolve [%s]: class does not exist. '
                . 'Check the class name, namespace, and that the file '
                . 'is included in your composer autoloader (composer dump-autoload).',
                $abstract,
            );
        } else {
            $message = sprintf(
                'Unable to resolve [%s] → bound to [%s]: '
                . 'the concrete class does not exist. '
                . 'Verify the binding in your service provider.',
                $abstract,
                $concrete,
            );
        }

        if (!empty($stack)) {
            $message .= self::formatStack($stack);
        }

        return new self($message, 0, null, $stack);
    }

    /**
     * Cannot resolve a parameter dependency.
     */
    public static function cannotResolve(
        ?string $className,
        string $message,
        array $stack = [],
    ): self {
        $fullMessage = $message;

        if ($className) {
            $fullMessage = sprintf(
                'Cannot resolve dependency in %s:: %s',
                $className,
                $message,
            );
        }

        if (!empty($stack)) {
            $fullMessage .= self::formatStack($stack);
        }

        return new self($fullMessage, 0, null, $stack);
    }

    /**
     * Interface has no registered concrete implementation.
     */
    public static function unboundInterface(
        string $interface,
        array $stack = [],
    ): self {
        $message = sprintf(
            'Cannot resolve interface [%s]: no concrete implementation '
            . 'has been registered. Register one with: '
            . '$container->bind(\'%s\', ConcreteClass::class)',
            $interface,
            $interface,
        );

        if (!empty($stack)) {
            $message .= self::formatStack($stack);
        }

        return new self($message, 0, null, $stack);
    }

    /**
     * A factory, closure, or class instantiation threw an exception.
     */
    public static function buildFailed(
        string $abstract,
        string $buildType,
        Throwable $previous,
        array $stack = [],
    ): self {
        $message = sprintf(
            'Failed to build [%s] via %s: %s',
            $abstract,
            $buildType,
            $previous->getMessage(),
        );

        // Add file/line from the original exception for quick debugging
        $message .= sprintf(
            ' (thrown in %s:%d)',
            $previous->getFile(),
            $previous->getLine(),
        );

        if (!empty($stack)) {
            $message .= self::formatStack($stack);
        }

        return new self(
            $message,
            (int) $previous->getCode(),
            $previous,
            $stack,
        );
    }

    /**
     * Format the resolution stack for inclusion in error messages.
     */
    private static function formatStack(array $stack): string
    {
        if (empty($stack)) {
            return '';
        }
        return sprintf(' | Resolution stack: %s', implode(' → ', $stack));
    }
}