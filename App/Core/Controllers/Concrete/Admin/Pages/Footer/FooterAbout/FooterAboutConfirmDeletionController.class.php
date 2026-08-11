<?php

declare(strict_types=1);

class FooterAboutConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private FooterAboutDeleteValidator $validator,
        private ConfirmDeletionModalBuilder $modalBuilder,
        ConfirmDeletionFormConfigFactory $factory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm, $factory);
    }

    #[Override]
    protected function entityClass(): string
    {
        return FooterAbout::class;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_ABOUT->value;
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
            deleteRoute: '/admin/footer-about-confirm-deletion/confirm',
            cancelRoute: '/admin/footer-about-confirm-deletion/cancel',
            isVisible: false,
        );
        $this->modalBuilder->setDto($dto);
        return $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->modalBuilder,
                'formValues' => $dto->toFormValues(),
                'action' => '/admin/footer-about-delete/delete',
                'factory' => $this->factory,
            ],
        );
    }
}