<?php

declare(strict_types=1);

final class CheckoutAddressModalBuilder extends AbstractModalFormBuilder
{
    private CheckoutAddressDTO $dto;

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

        $modalContainer = $this->getRegularContainer('address-modal');
        $isEdit = $this->dto->getId() !== null;
        $modalId = $isEdit ? 'edit-address-modal' : 'add-address-modal';
        $modalContainer = $modalContainer->custom([
            'data-modal' => $modalId,
            'data-cancel-url' => $this->dto->getCancelRoute(),
            'data-address-type' => $this->dto->getAddressType(),
            'data-address-id' => $this->dto->addressId ?? '',
        ]);

        if ($this->dto->isVisible()) {
            $modalContainer->class('active', 'modal-open');
        }

        $innerClass = $this->modalClass('address-modal__content');

        [$iconEdit, $iconPlus, $iconCancel, $iconDelete,$iconClose] = $this->buildIcons();

        $modalInner = $html->div()->class(...$innerClass)->add(
            $this->closeButton($this->dto->getCancelRoute(), $iconClose),
            $this->modalHeader($isEdit ? $iconEdit : $iconPlus),
            $html->htmlBlock($form),
            $html->htmlBlock($this->getModalFooter($action, $config, $isEdit)),
        );

        return $modalContainer->add($modalInner)->generate();
    }

    public function getIdentier(): string
    {
        return ModalIdentifier::CHECKOUT_ADDRESS->value;
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
        bool $isEdit,
    ): string {
        $formValues = $this->dto->toFormValues();
        $cancelRoute = $formValues['cancel_route'] ?? '#';
        $label = $formValues['label'] ?? 'Address';

        $submitLabel = $isEdit ? 'Update ' . $label : 'Add ' . $label;
        $icon = $isEdit ? 'icon-edit' : 'icon-plus';
        $style = $isEdit ? 'primary' : 'success';

        $footer = new FooterProvider(
            builder: $this->htmlBuilder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forStandalone(
                action: $action,
                cancelRoute: $cancelRoute,
                formId: $config->getFormId(),
                footerClass: [
                    'modal-footer',
                    'address-modal__footer',
                ],
                submitButtonConfig: new ButtonConfig(
                    type: 'submit',
                    label: $submitLabel,
                    style: $style,
                    iconConfig: new IconConfig(
                        icon: $icon,
                        ariaLabel: $submitLabel,
                    ),
                    attributes: [
                        'form' => $config->getFormId(),
                        'data-js-type' => 'button',
                        'data-action' => $isEdit ? 'update-address' : 'save-address',
                    ],
                ),
            ),
        );
        return $footer->renderFooter();
    }

    private function modalHeader(AbstractHtmlComponent $icon): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $modalHeaderClasses = $this->headerClass('address-modal__header');

        $titleClasses = $this->headertitleClass('address-modal__header-title');
        $headerContentClass = $this->headerContentClass('address-modal__header-content');

        $headerContentTextClass = $this->headerContentTextClass('address-modal__header-content--text');

        $headerContentIconClaass = $this->headerContentIconClass('address-modal__header-content--icon');

        $isEdit = $this->dto->getId() !== null;
        $title = $isEdit ? 'Edit Address' : 'Add New Address';
        $subtitle = $isEdit
            ? 'Update your address details below.'
            : 'Enter the address details below.';

        $subtitleElement = $html->tag('p')
            ->class(...$headerContentTextClass)
            ->content($subtitle);

        // Address type badge
        $addressType = $this->dto->getAddressType() ?? 'shipping';
        $typeLabel = ucfirst($addressType);
        $typeBadge = $html->tag('span')
            ->class('address-modal__type-badge', 'address-modal__type-badge--' . $addressType)
            ->content($typeLabel . ' Address');

        return $html->div()->class(...$modalHeaderClasses)->add(
            $html->div()->class('address-modal__header-top')->add(
                $html->tag('h4')->class(...$titleClasses)->content($title),
                $typeBadge,
            ),
            $html->tag('span')->class(...$headerContentClass)->add(
                $html->div()->class(...$headerContentIconClaass)->add(
                    $icon,
                ),
                $subtitleElement,
            ),
        );
    }
}