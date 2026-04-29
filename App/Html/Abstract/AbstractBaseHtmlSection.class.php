<?php

declare(strict_types=1);

abstract class AbstractBaseHtmlSection implements HtmlSectionInterface
{
    protected array $context = [];

    public function __construct(
        protected readonly HtmlBuilder $htmlBuilder,
        protected readonly IconBuilder $iconBuilder,
    ) {
    }

    abstract public function getConfig(array $formValues = []): array|AbstractHtmlComponent;

    public function shouldRender(array|Entity $formValues = []): bool
    {
        return true;
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return null;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    protected function escape(?string $value, array $options = []): ?string
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

    protected function thumbnail(?string $media = null, string $alt = ''): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $container = $html->tag('div')->class('details__image-container');

        if ($media !== null) {
            return $container->add(
                $html->tag('img')
                    ->src($media)
                    ->class('image')
                    ->alt('Entity Image'),
            );
        }

        return $container->add(
            $this->iconBuilder->createIcon(
                'icon-media-image',
                'Thumbnail',
                ['image'],
            ),
        );
    }
}