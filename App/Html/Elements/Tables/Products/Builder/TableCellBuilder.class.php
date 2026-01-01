<?php

declare(strict_types=1);

class TableCellBuilder
{
    public function __construct(
        private HtmlBuilder $builder,
        private TypePresenterFactory $presenter,
    ) {
    }

    /**
     * Build a table cell with formatted content.
     */
    public function buildCell(Entity $entity, string $fieldName, string $cssClass = ''): AbstractHtmlComponent
    {
        $value = $this->getEntityValue($entity, $fieldName);
        $formatted = $this->formatValue($entity, $value, $fieldName);

        return $this->builder->tag('td')
            ->class('table__body--row-cell')
            ->add(
                $this->builder->tag('div')
                    ->class('body-cell', $cssClass)
                    ->add(
                        $this->builder->tag('span')->content($formatted),
                    ),
            );
    }

    /**
     * Build a badge cell.
     */
    public function buildBadgeCell(Entity $entity, string $fieldName, string $cssClass = '', array $badgeClasses = []): AbstractHtmlComponent
    {
        $value = $this->getEntityValue($entity, $fieldName);
        $formatted = $this->formatValue($entity, $value, $fieldName);

        return $this->builder->tag('td')
            ->class('table__body--row-cell')
            ->add(
                $this->builder->tag('div')
                    ->class('body-cell', $cssClass)
                    ->add(
                        $this->builder->tag('span')
                            ->add(
                                $this->builder->tag('span')
                                    ->class(...$badgeClasses)
                                    ->content($formatted),
                            ),
                    ),
            );
    }

    private function getEntityValue(Entity $entity, string $fieldName): mixed
    {
        // Try getter first
        $getter = 'get' . ucfirst($fieldName);
        if (method_exists($entity, $getter)) {
            return $entity->$getter();
        }

        // Try direct property
        $propertyName = lcfirst(str_replace('_', '', ucwords($fieldName, '_')));
        try {
            $property = $entity->getProperty($propertyName);
            return $property->getValue($entity);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    private function formatValue(Entity $entity, mixed $value, string $fieldName): string
    {
        try {
            $property = $entity->getProperty($fieldName);
            return (string) $this->presenter->displayValue($value, $property);
        } catch (ReflectionException $e) {
            return (string) $this->presenter->displayValue($value);
        }
    }
}