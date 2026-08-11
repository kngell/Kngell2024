<?php

declare(strict_types=1);

abstract class AbstractFooterModalBuilder extends AbstractModalFormBuilder
{
    protected BaseFooterDTO $dto;

    public function __construct(
        protected ButtonBuilder $buttonBuilder,
        ?IconBuilder $iconBuilder = null,
        ?HtmlBuilder $htmlBuilder = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function build(string $action, string $form, FormConfig $config): string
    {
        $html = $this->htmlBuilder;
        $modalClasses = array_merge(self::OVERLAY_CLASS, [$this->getModalClass()]);

        $modalContainer = $html->div()->class(...$modalClasses)->custom([
            'data-modal' => $this->getModalClass(),
            'data-cancel-url' => $this->dto->getCancelRoute(),
        ]);

        if ($this->dto->isVisible()) {
            $modalContainer->class('active');
        }

        $innerClass = array_merge(self::MAIN_DIV_CLASS, [$this->getModalClass()]);

        [$iconClose, $iconSave, $iconCancel] = $this->buildIcons();

        $modalInner = $html->div()->class(...$innerClass)->add(
            $this->closeButton($this->dto->getCancelRoute(), $iconClose),
            $this->modalHeader($this->getModalTitle(), $iconClose),
            $html->htmlBlock($form),
            $html->htmlBlock($this->getModalFooter($action, $config, $iconSave, $iconCancel)),
        );

        return $modalContainer->add($modalInner)->generate();
    }

    public function getIdentier(): string
    {
        return $this->getModalIdentifier();
    }

    public function setDto(ModalDTOInterface $dto): self
    {
        if (!$dto instanceof BaseFooterDTO) {
            throw new InvalidArgumentException('Expected BaseFooterDTO');
        }
        $this->dto = $dto;
        return $this;
    }

    abstract protected function getModalTitle(): string;

    abstract protected function getModalIdentifier(): string;

    abstract protected function getModalClass(): string;

    abstract protected function getSubmitButtonLabel(): string;

    abstract protected function getSubmitButtonAriaLabel(): string;

    abstract protected function getSaveIcon(): string;

    protected function buildIcons(): array
    {
        $iconBuilder = $this->iconBuilder;

        return [
            $iconBuilder->createIcon('icon-close', 'Close Modal', ['close']),
            $iconBuilder->createIcon($this->getSaveIcon(), $this->getSubmitButtonAriaLabel(), ['save']),
            $iconBuilder->createIcon('icon-cancel', 'Cancel', ['cancel']),
        ];
    }

    private function modalHeader(string $title, AbstractHtmlComponent $iconClose): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $modalHeaderClasses = array_merge(self::HEADER_CLASS, [$this->getModalClass() . '__header']);

        return $html->div()->class(...$modalHeaderClasses)->add(
            $html->tag('h4')->class(...self::HEADER_TITLE_CLASS)->content($title),
        );
    }

    private function getModalFooter(
        string $action,
        FormConfig $config,
        AbstractHtmlComponent $iconSave,
        AbstractHtmlComponent $iconCancel,
    ): string {
        $footer = new FooterProvider(
            builder: $this->htmlBuilder,
            buttonBuilder: $this->buttonBuilder,
            dto: FooterDTO::forStandalone(
                action: $action,
                cancelRoute: $this->dto->getCancelRoute(),
                formId: $config->getFormId(),
                footerClass: ['modal-footer', $this->getModalClass() . '__footer'],
                submitButtonConfig: new ButtonConfig(
                    type: 'submit',
                    label: $this->getSubmitButtonLabel(),
                    ariaLabel: $this->getSubmitButtonAriaLabel(),
                    style: 'primary',
                    icon: $this->getSaveIcon(),
                    attributes: ['form' => $config->getFormId()],
                ),
            ),
        );
        return $footer->renderFooter();
    }
}