<?php

declare(strict_types=1);

class FormDecorator extends AbstractFormDecorator
{
    protected function getDisplayKey(): ?string
    {
        if ($this->formConfig === null) {
            return null;
        }
        return $this->formConfig->getDisplayKey();
    }

    protected function validateTarget(Controller $target): void
    {
        $expected = $this->tableConfig()->expectedControllerClass;
        if (!$target instanceof $expected) {
            throw new HtmlDecoratorException(sprintf(
                '%s requires %s, got %s',
                static::class,
                $expected,
                get_class($target),
            ));
        }
    }

    #[Override]
    protected function getHeaderConfig(): ?AdminHeaderConfig
    {
        if (!isset($this->factory)) {
            return null;
        }

        // Check if factory has the method to create AdminHeaderConfig
        if (method_exists($this->factory, 'createAdminHeaderConfig')) {
            return $this->factory->createAdminHeaderConfig();
        }

        // Fallback to building from legacy methods
        return new AdminHeaderConfig(
            title: $this->factory->headerTitle(),
            breadcrumbs: $this->factory->breadcrumbs(),
            primaryActions: $this->factory->headerButtons(),
        );
    }

    #[Override]
    protected function getGormConfig(): ?FormConfig
    {
        $this->ensureConfigured();
        return $this->formConfig ??= $this->factory->createFormConfig();
    }

    // ─── Internal ────────────────────────────────────────────

    private function ensureConfigured(): void
    {
        if (!isset($this->factory)) {
            throw new LogicException(sprintf(
                '%s used without configure(["factory" => ..., "adapter" => ...]).',
                static::class,
            ));
        }
    }
}