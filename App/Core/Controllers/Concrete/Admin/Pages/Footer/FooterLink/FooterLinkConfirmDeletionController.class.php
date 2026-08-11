<?php

declare(strict_types=1);

class FooterLinkConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private FooterLinkDeleteValidator $validator,
        private ConfirmDeletionModalBuilder $modalBuilder,
        ConfirmDeletionFormConfigFactory $factory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm, $factory);
    }

    #[Override]
    protected function entityClass(): string
    {
        return FooterMenuLink::class;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_MENU_LINK->value;
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(array $identifier): string
    {
        $id = $identifier['value'];
        return '/admin/footer-page/index';
    }

    protected function createDeletionDecorator(array $data): object
    {
        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $data,
            label: $this->getLabel(),
            deleteRoute: '/admin/footer-link-confirm-deletion/confirm',
            cancelRoute: '/admin/footer-link-confirm-deletion/cancel',
            isVisible: false,
        );
        $this->modalBuilder->setDto($dto);
        return $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->modalBuilder,
                'formValues' => $dto->toFormValues(),
                'action' => '/admin/footer-link-delete/delete',
                'factory' => $this->factory,
            ],
        );
    }

    protected function buildFlashData(
        array $id,
        DeletionValidatorResult $validationResult,
        ?string $blockType = null,
    ): array {
        $columnId = $this->request->getPost()->get('column_id', null);
        return [
            'id' => $id,
            'column_id' => $columnId,
            'name' => $validationResult->getDisplayName(),
            'warnings' => $validationResult->getWarnings(),
            'image' => $validationResult->getDisplayImage(),
            'metadata' => $validationResult->getAllMetadata(),
            'timestamp' => time(),
            'block_type' => $blockType,
        ];
    }
}