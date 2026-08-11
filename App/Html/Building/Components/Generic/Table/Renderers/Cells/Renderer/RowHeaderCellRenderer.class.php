<?php

declare(strict_types=1);

final class RowHeaderCellRenderer implements TableCellRendererInterface
{
    use EntityDisplayTrait;

    public function render(
        Mixed $entity,
        array $colDef,
        int $rowIndex,
        HtmlBuilder $builder,
    ): AbstractHtmlComponent {
        $key = $colDef['key'];
        $id = "{$key}_row" . ($rowIndex + 1);

        $thumbnailUrl = isset($colDef['thumbnail']) ? ($colDef['thumbnail'])($entity) : null;
        $thumbnailAlt = $colDef['thumbnailAlt'] ?? '';
        $title = isset($colDef['title']) ? ($colDef['title'])($entity) : '';

        // Get checkbox name - dynamic based on entity
        $checkboxName = $colDef['checkboxName'] ?? $this->generateCheckboxName($entity);

        $entityId = $this->getEntityId($entity);

        return $builder->tag('th')
            ->custom(['scope' => 'row'])
            ->class('table__body--row-cell', 'table__cell--start')
            ->add(
                $builder->tag('div')
                    ->class('body-cell-start', 'body-cell-start--checkbox')
                    ->add(
                        $builder->input('checkbox')
                            ->id($id)
                            ->name($checkboxName)
                            ->value($entityId),
                        $builder->label()
                            ->class('body-cell-start__label')
                            ->for($id)
                            ->add(
                                $this->buildThumbnail($thumbnailUrl, $thumbnailAlt, $builder, $entity),
                                $builder->tag('span')->class('text-container')->add(
                                    $builder->tag('span')
                                        ->class('text-container__name')
                                        ->content($title),
                                    ...$this->buildSubtitle($colDef, $entity, $builder),
                                ),
                            ),
                    ),
            );
    }

    private function generateCheckboxName(Entity $entity): string
    {
        if ($entity instanceof Entity) {
            $tableName = $entity->table();
            return $tableName . '[]';
        }

        // Fallback for non-Entity objects
        return 'items[]';
    }

    private function buildSubtitle(array $colDef, Entity $entity, HtmlBuilder $builder): array
    {
        if (!isset($colDef['subtitle'])) {
            return [];
        }

        $subtitle = ($colDef['subtitle'])($entity);

        return [
            $builder->tag('span')
                ->class('text-container__variant')
                ->content($subtitle),
        ];
    }

    private function buildThumbnail(
        ?string $url,
        string|Closure $alt,
        HtmlBuilder $builder,
        mixed $entity,
    ): AbstractHtmlComponent {
        $container = $builder->tag('span')->class('img-container');

        if ($alt instanceof Closure) {
            $alt = $alt($entity);
        }

        return $url
            ? $container->add(
                $builder->tag('img')->src($url)->alt($alt)->class('image'),
            )
            : $container;
    }
}