<?php

declare(strict_types=1);

class ConfirmDeletionDecorator extends AbstractHtmlDecorator
{
    private const string TEMPLATE_NAME = 'confirmProductDeletionModal';

    public function __construct(
        Controller $controller,
        private string $action,
        private array|Entity $formValues = [],
        private ?HtmlTemplatePathManager $templateManager = null,
    ) {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $target = $this->getTarget();
        $deletionModalHtml = $this->templateManager->getTemplate(self::TEMPLATE_NAME);
        $form = $target->form($this->action, $this->formValues);
        [$iconWarning, $iconCancel, $iconDelete,$iconClose] = $this->icons($target);
        $deletionModalHtml = str_replace('{{icon-warning}}', $iconWarning, $deletionModalHtml);
        $deletionModalHtml = str_replace('{{icon-cancel}}', $iconCancel, $deletionModalHtml);
        $deletionModalHtml = str_replace('{{icon-delete}}', $iconDelete, $deletionModalHtml);
        $deletionModalHtml = str_replace('{{icon-close}}', $iconClose, $deletionModalHtml);
        $deletionModalHtml = str_replace('{{deletion_subtitle}}', $this->deletionSubTitle($target), $deletionModalHtml);
        $deletionModalHtml = str_replace('{{confirmDeletionForm}}', $form, $deletionModalHtml);

        return parent::page() + [
            'confirmDeletionModal' => $deletionModalHtml,
        ];
    }

    private function deletionSubTitle(Controller $target): string
    {
        $html = $target->builder;
        $productName = $this->formValues['product_name'] ?? '';
        $htmlText = 'This action will remove ' . htmlspecialchars($productName) . ' from your storefront.';
        return $html->tag('p')->class('content__text')->add(
            $html->text($htmlText),
        )->generate();
    }

    private function icons(Controller $target): array
    {
        $iconBuilder = new IconBuilder();
        $iconWarning = $iconBuilder->createIcon($target->builder, 'icon-warning', 'Warning', ['warning']);
        $iconCancel = $iconBuilder->createIcon($target->builder, 'icon-cancel', 'Cancel', ['cancel']);
        $iconDelete = $iconBuilder->createIcon($target->builder, 'icon-trash', 'Delete', ['delete']);
        $iconClose = $iconBuilder->createIcon($target->builder, 'icon-close', 'Close Modal', ['close']);
        return [$iconWarning->generate(), $iconCancel->generate(), $iconDelete->generate(), $iconClose->generate()];
    }
}