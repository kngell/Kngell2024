<?php

declare(strict_types=1);

class CategoryConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private CategoryDeleteValidator $validator,
        private ConfirmDeletionModalBuilder $modalBuilder,
        ConfirmDeletionFormConfigFactory $factory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm, $factory);
    }

    #[Override]
    protected function entityClass(): string
    {
        return Category::class;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::CATEGORY->value;
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(array $identifier): string
    {
        $id = $identifier['value'];
        return CategoryLinks::EDIT->withId($id);
    }

    protected function createDeletionDecorator(array $data): object
    {
        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $data,
            label: $this->getLabel(),
            deleteRoute: CategoryLinks::DELETE->value,
            cancelRoute: CategoryLinks::CANCEL_DELETION->value,
            isVisible: false,
        );
        $this->modalBuilder->setDto($dto);
        return $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->modalBuilder,
                'formValues' => $dto->toFormValues(),
                'action' => CategoryLinks::DELETE->value,
                'factory' => $this->factory,
            ],
        );
    }
}