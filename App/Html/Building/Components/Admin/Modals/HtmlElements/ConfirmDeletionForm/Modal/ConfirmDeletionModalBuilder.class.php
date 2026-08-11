<?php

declare(strict_types=1);

final class ConfirmDeletionModalBuilder extends AbstractModalFormBuilder
{
    private ConfirmDeletionDTO $dto;

    public function __construct(
        private ButtonBuilder $buttonBuilder,
        ?IconBuilder $iconBuilder = null,
        ?HtmlBuilder $htmlBuilder = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function build(string $action, string $form, FormConfig $config): string
    {
        $html = $this->htmlBuilder;
        $modalClasses = array_merge(self::OVERLAY_CLASS, ['confirm-deletion-modal']);

        $modalContainer = $html->div()->class(...$modalClasses)->custom([
            'data-modal' => 'confirm-deletion',
            'data-cancel-url' => $this->dto->cancelRoute,
        ]);
        if ($this->dto->isVisible) {
            $modalContainer->class('active', 'modal-open');
        }
        $innerClass = array_merge(self::MAIN_DIV_CLASS, ['confirm-deletion']);

        [$iconWarning, $iconCancel, $iconDelete, $iconClose] =
                  $this->buildIcons();

        $modalInner = $html->div()->class(...$innerClass)->add(
            $this->closeButton($this->dto->cancelRoute, $iconClose),
            $this->modalHeader($iconWarning),
            $html->htmlBlock($form),
            $html->htmlBlock($this->getModalFooter($action, $config)),
        );

        return $modalContainer->add($modalInner)->generate();
    }

    public function getIdentier(): string
    {
        return ModalIdentifier::CONFIRM_DELETION->value;
    }

    /**
     * @param ModalDTOInterface $dto
     *
     * @return ModalFormBuilderInterface
     */
    public function setDto(ModalDTOInterface $dto): ModalFormBuilderInterface
    {
        $this->dto = $dto;
        return $this;
    }

    private function getModalFooter(
        string $action,
        FormConfig $config,
    ): string {
        $formValues = $this->dto->toFormValues();
        $label = $formValues['label'] ?? 'Item';
        $cancelRoute = $formValues['cancel_route'] ?? '#';
        $footer = new FooterProvider(
            builder: $this->htmlBuilder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forStandalone(
                action: $action,
                cancelRoute: $cancelRoute,
                formId: $config->getFormId(),
                footerClass: [
                    'modal-footer',
                    'confirm-deletion__footer',
                ],
                // ConfirmDeletionForm::make()
                submitButtonConfig: new ButtonConfig(
                    type: 'submit',
                    label: 'Delete ' . $label,
                    style: 'danger',
                    iconConfig: new IconConfig(
                        icon: 'icon-trash',
                        ariaLabel: 'Delete ' . $label,
                    ),
                    attributes: [
                        'form' => $config->getFormId(),
                        'data-js-type' => 'button',
                    ],
                ),
            ),
        );
        return $footer->renderFooter();
    }

    private function ModalHeader(AbstractHtmlComponent $iconWarning): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $modalHeaderClasses = array_merge(self::HEADER_CLASS, ['confirm-deletion__header']);

        $subtitle = $html->tag('p')
                   ->class(...self::HEADER_CONTENT_TEXT_CLASS)
                   ->content($this->dto->subtitle);
        return $html->div()->class(...$modalHeaderClasses)->add(
            $html->tag('h4')->class(...self::HEADER_TITLE_CLASS)->content('Delete Confirmation'),
            $html->tag('span')->class(...self::HEADER_CONTENT_CLASS)->add(
                $html->div()->class(...self::HEADER_CONTENT_ICON_CLASS)->add(
                    $iconWarning,
                ),
                $subtitle,
            ),
        );
    }
}