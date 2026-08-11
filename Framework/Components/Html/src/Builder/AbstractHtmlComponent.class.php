<?php

declare(strict_types=1);

abstract class AbstractHtmlComponent
{
    use HtmlAttributesTrait;
    use AriaAttributesTrait;
    use EventAttributesTrait;
    use FormFieldTrait;
    use TagRendererTrait;
    use ConditionMethodsTrait;

    protected int $level = 0;
    protected ?self $parent = null;
    protected string $tag = '';
    protected null|string|int $content = null;
    protected bool $contentUp = true;
    protected CollectionInterface $children;

    public function __construct(protected ?TranslatorServiceInterface $translator = null)
    {
        $this->children = new Collection();
    }

    public function setParent(?self $parent): void
    {
        $this->parent = $parent;
        $this->level = $parent ? $parent->getLevel() + 1 : 0;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function add(self|null ...$htmlelements): static
    {
        return $this;
    }

    public function remove(self $component): static
    {
        return $this;
    }

    public function content(null|string|int $content, bool $contentUp = true): static
    {
        $this->content = $content;
        $this->contentUp = $contentUp;
        return $this;
    }

    public function getContent(): null|string|int
    {
        return $this->content;
    }

    public function htmlBlock(?string $htmlBlock = null): HtmlBlockElement
    {
        return new HtmlBlockElement($htmlBlock ?? '');
    }

    public function text(?string $text = null): HtmlTextElement
    {
        return new HtmlTextElement($text ?? '');
    }

    abstract public function generate(): string;

    protected function inputClassStyle(string $type = '', bool $isError = false): void
    {
        if ($isError) {
            if (!in_array('is-invalid', $this->class, true)) {
                $this->class[] = 'is-invalid';
            }
        } else {
            $this->class = array_values(
                array_filter($this->class, fn ($c) => $c !== 'is-invalid'),
            );
            $validClass = $this->isValidClass($type);
            if ($validClass !== '') {
                $this->class[] = $validClass;
            }
        }
    }

    protected function trans(string $key, array $parameters = []): string
    {
        return $this->translator ? $this->translator->translate($key, $parameters) : $key;
    }

    protected function populateField(): void
    {
        $strErrors = '';
        if (isset($this->name) && $this->name !== '') {
            $strErrors = $this->inputErrors($this->name);
            $this->value = $this->inputValue($this->name, $this->value ?? '');

            if ($this instanceof SelectElement && $this->value && isset($this->children)) {
                foreach ($this->children as $child) {
                    $child->defaultValue($this->value);
                }
            }
        }
        $this->errorMessage = $strErrors;
    }

    protected function inputErrors(string $name, string $type = ''): string
    {
        $errorStr = '';
        $isError = false;

        if (!empty($this->formErrors) && array_key_exists($name, $this->formErrors)) {
            foreach ($this->formErrors[$name] as $error) {
                $errorStr .= is_array($error) ? implode(', ', $error) : $error;
            }
            $isError = true;
        }

        $this->inputClassStyle($type, $isError);
        return $errorStr;
    }
}