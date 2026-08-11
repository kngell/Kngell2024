<?php

declare(strict_types=1);

abstract class AbstractBaseModalController extends Controller
{
    use ResolveDataTrait;

    public function __construct(
        FormCreatorService $frm,
    ) {
        $this->layout(NavbarType::ECOMMERCE);
        $this->frm = $frm;
    }

    public function add(): Response|string
    {
        $this->pageTitle('Add ' . $this->getEntityType());
        $modalData = $this->getModalData();
        $isAjax = $this->request->isAjax();
        $redirectUrl = $this->resolveRedirectUrl();

        if (empty($modalData)) {
            if ($isAjax) {
                return $this->respondError(
                    isAjax: $isAjax,
                    message: 'Modal not Found',
                    redirect: $redirectUrl,
                    flashType: FlashType::INFO,
                );
            }
            $this->flash->add('Modal not Found', FlashType::DANGER);
            return $this->redirect($redirectUrl);
        }

        if ($isAjax) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Modal ready.',
                redirect: $redirectUrl,
                extraData:  $modalData,
            );
        }

        return $this->render($this->getModlIndentifier()->getView(), $modalData);
    }

    protected function getModalData(null|Entity $entity = null): array
    {
        $dto = $this->createDTO($entity);
        if ($dto === null) {
            return [];
        }
        $modalBuilder = $this->getModalBuilder()->setDto($dto);

        $decorated = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $modalBuilder,
                'formValues' => $entity ?? $dto->toFormValues(),
                'action' => $this->getSaveRoute(),
                'factory' => $this->getFormFactory(),
            ],
        );

        return $decorated->page();
    }

    abstract protected function createDTO(Entity $entity): ?ModalDTOInterface;

    abstract protected function getModalBuilder(): ModalFormBuilderInterface;

    abstract protected function getSaveRoute(): string;

    abstract protected function getFormFactory(): AbstractFormConfigFactory;
}