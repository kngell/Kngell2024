<?php

declare(strict_types=1);

class ConfirmDeletionDecorator extends AbstractHtmlDecorator implements RuntimeConfigurableInterface
{
    private const string TEMPLATE_NAME = 'confirmDeletionModal';

    protected ConfirmDeletionDTO $dto;

    public function __construct(
        private ?HtmlTemplatePathManager $templateManager,
        private readonly IconBuilder $iconBuilder,
    ) {
    }

    public function page(): array
    {
        $target = $this->getTarget();
        $html = $target->builder;

        $modalHtml = $this->templateManager->getTemplate(
            self::TEMPLATE_NAME,
        );

        $formValues = $this->dto->toFormValues();
        $form = $target->form(
            $this->dto->deleteRoute,
            $formValues,
        );

        [$iconWarning, $iconCancel, $iconDelete, $iconClose] =
            $this->buildIcons();

        $subtitle = $html->tag('p')
            ->class('content__text')
            ->content($this->dto->subtitle);

        $modalHtml = strtr($modalHtml, [
            '{{visible}}' => $this->dto->isVisible ? 'active' : '',
            '{{cancel-route}}' => $this->dto->cancelRoute,
            '{{icon-warning}}' => $iconWarning,
            '{{icon-cancel}}' => $iconCancel,
            '{{icon-delete}}' => $iconDelete,
            '{{icon-close}}' => $iconClose,
            '{{deletion_subtitle}}' => $subtitle->generate(),
            '{{confirmDeletionForm}}' => $form,
        ]);

        return parent::page() + [
            'confirmDeletionModal' => $modalHtml,
        ];
    }

    private function buildIcons(): array
    {
        $iconBuilder = $this->iconBuilder;

        $icons = [
            ['icon-warning', 'Warning', ['warning']],
            ['icon-cancel', 'Cancel', ['cancel']],
            ['icon-trash', 'Delete', ['delete']],
            ['icon-close', 'Close Modal', ['close']],
        ];

        return array_map(
            fn (array $config) => $iconBuilder
                ->createIcon(
                    $config[0],
                    $config[1],
                    $config[2],
                )
                ->generate(),
            $icons,
        );
    }
}