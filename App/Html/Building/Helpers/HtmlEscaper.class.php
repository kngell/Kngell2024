<?php

declare(strict_types=1);

final class HtmlEscaper
{
    public function escape(?string $value, array $options = []): string
    {
        if ($value === null) {
            return '';
        }

        $options = array_merge([
            'trim' => true,
            'preserveNbsp' => true,
            'preserveEntities' => [],
            'encoding' => 'UTF-8',
        ], $options);

        $result = $value;
        if ($options['trim']) {
            $result = trim($result);
        }

        if ($options['preserveNbsp'] || !empty($options['preserveEntities'])) {
            $entitiesToPreserve = $options['preserveNbsp'] ? ['&nbsp;'] : [];
            $entitiesToPreserve = array_merge($entitiesToPreserve, $options['preserveEntities']);

            $placeholders = [];
            foreach ($entitiesToPreserve as $i => $entity) {
                $placeholder = "___ENT_{$i}___";
                $placeholders[$placeholder] = $entity;
                $result = str_replace($entity, $placeholder, $result);
            }

            $result = html_entity_decode($result, ENT_QUOTES | ENT_HTML5, $options['encoding']);
            $result = htmlspecialchars($result, ENT_QUOTES, $options['encoding']);

            foreach ($placeholders as $placeholder => $entity) {
                $result = str_replace($placeholder, $entity, $result);
            }

            return $result;
        }

        return htmlspecialchars($result, ENT_QUOTES, $options['encoding']);
    }
}