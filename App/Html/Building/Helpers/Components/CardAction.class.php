<?php

declare(strict_types=1);

final class CardAction implements StandAloneComponentInterface
{
    private string $targetSelector = '';
    private string $itemSelector = '';
    private bool $showAdd = true;
    private bool $showRemove = true;
    private string $addButtonText = '';
    private string $removeButtonText = '';
    private array $addAttributes = [];
    private array $removeAttributes = [];
    private ?Closure $onAdd = null;
    private ?Closure $onRemove = null;

    public function __construct(
        private readonly HtmlBuilder $builder,
        private readonly IconBuilder $iconBuilder,
    ) {
    }

    public function target(string $selector): self
    {
        $this->targetSelector = $selector;
        return $this;
    }

    public function item(string $selector): self
    {
        $this->itemSelector = $selector;
        return $this;
    }

    public function showAdd(bool $show = true): self
    {
        $this->showAdd = $show;
        return $this;
    }

    public function showRemove(bool $show = true): self
    {
        $this->showRemove = $show;
        return $this;
    }

    public function addButtonText(string $text): self
    {
        $this->addButtonText = $text;
        return $this;
    }

    public function removeButtonText(string $text): self
    {
        $this->removeButtonText = $text;
        return $this;
    }

    public function addAttributes(array $attributes): self
    {
        $this->addAttributes = array_merge($this->addAttributes, $attributes);
        return $this;
    }

    public function removeAttributes(array $attributes): self
    {
        $this->removeAttributes = array_merge($this->removeAttributes, $attributes);
        return $this;
    }

    public function onAdd(Closure $callback): self
    {
        $this->onAdd = $callback;
        return $this;
    }

    public function onRemove(Closure $callback): self
    {
        $this->onRemove = $callback;
        return $this;
    }

    public function build(mixed $params = null): ?AbstractHtmlComponent
    {
        $cardAction = $this->builder->div()->class('card-action');

        if ($this->targetSelector) {
            $cardAction->attribute('data-card-target', $this->targetSelector);
        }

        if ($this->itemSelector) {
            $cardAction->attribute('data-card-item', $this->itemSelector);
        }

        if ($this->showAdd) {
            $addButton = $this->builder->button('button')
                ->class('card-action__add-btn')
                ->attribute('data-card-action', 'add');

            foreach ($this->addAttributes as $key => $value) {
                $addButton->attribute($key, $value);
            }

            if ($this->addButtonText) {
                $addButton->add(
                    $this->builder->tag('span')->class('btn__label')->content($this->addButtonText),
                );
            } else {
                $addButton->add(
                    $this->iconBuilder->createIcon('icon-plus', 'Add New', ['add']),
                );
            }

            $cardAction->add($addButton);
        }

        if ($this->showRemove) {
            $removeButton = $this->builder->button('button')
                ->class('card-action__remove-btn')
                ->attribute('data-card-action', 'remove');

            // Apply custom attributes
            foreach ($this->removeAttributes as $key => $value) {
                $removeButton->attribute($key, $value);
            }

            if ($this->removeButtonText) {
                $removeButton->add(
                    $this->builder->tag('span')->class('btn__label')->content($this->removeButtonText),
                );
            } else {
                $removeButton->add(
                    $this->iconBuilder->createIcon('icon-minus', 'Remove Existing', ['remove']),
                );
            }

            $cardAction->add($removeButton);
        }

        // Store callbacks for JavaScript integration
        if ($this->onAdd || $this->onRemove) {
            $this->registerCallbacks();
        }

        return $cardAction;
    }

    /**
     * Register callbacks for custom behavior.
     */
    private function registerCallbacks(): void
    {
        $callbacks = [];
        if ($this->onAdd) {
            $callbacks['onAdd'] = $this->onAdd;
        }
        if ($this->onRemove) {
            $callbacks['onRemove'] = $this->onRemove;
        }
    }
}