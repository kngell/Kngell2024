<?php

declare(strict_types=1);

class ContentBlockConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private ContentBlockDeleteValidator $validator,
        private ConfirmDeletionModalBuilder $modalBuilder,
        ConfirmDeletionFormConfigFactory $factory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm, $factory);
    }

    #[Override]
    protected function entityClass(): string
    {
        return ContentBlock::class;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('BlockType is required');
        }
        return $this->blockType->getPageTitle();
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(array $identifier): string
    {
        if (empty($this->blockType)) {
            throw new InvalidArgumentException('Block type is required.');
        }
        $id = (string) $identifier['value'];
        return ContentBlockLinks::getConfirmRedirectRoute($this->blockType, $id);
    }

    protected function createDeletionDecorator(array $data): object
    {
        if (empty($this->blockType)) {
            throw new InvalidArgumentException('Block type is required.');
        }
        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $data,
            label: $this->getLabel(),
            deleteRoute: ContentBlockLinks::getDeleteRoute(),
            cancelRoute: ContentBlockLinks::getCancelRoute($this->blockType, (string) $data['id']['value']),
            isVisible: false,
            blockType: $this->blockType->value,
        );
        $this->modalBuilder->setDto($dto);
        return $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->modalBuilder,
                'formValues' => $dto->toFormValues(),
                'action' => '/admin/content-block-page/add',
                'factory' => $this->factory,
            ],
        );
    }
}