<?php

declare(strict_types=1);

abstract class AbstractFooterPageController extends Controller
{
    use ResolveDataTrait;
    protected const ROUTE_INDEX = '/admin/footer-page/index';
    protected const ROUTE_CANCEL = '/admin/footer-page/cancel';

    public function __construct(
        FormCreatorService $frm,
    ) {
        $this->layout(NavbarType::ADMIN);
        $this->frm = $frm;
    }

    // ─── Common CRUD Methods ──────────────────────────────────

    public function add(): Response|string
    {
        $this->pageTitle('Add ' . $this->getEntityType());
        $modalData = $this->getModalData();
        $isAjax = $this->request->isAjax();

        if (empty($modalData)) {
            if ($isAjax) {
                return $this->respondError(
                    isAjax: $isAjax,
                    message: 'Please select a Column',
                    redirect: '/admin/footer-page/index',
                    flashType: FlashType::INFO,
                );
            }
            $this->flash->add('Please Select a Column', FlashType::INFO);
            $redirectUrl = $this->getRedirectUrl();
            return $this->redirect($redirectUrl);
        }

        if ($this->request->isAjax()) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Modal ready.',
                redirect: self::ROUTE_INDEX,
                extraData:  $modalData,
            );
        }

        return $this->render('/footer/footer', $modalData);
    }

    public function edit(): Response|string
    {
        $entity = $this->getEntityData();

        if (empty($entity)) {
            $this->flash->add($this->getEntityType() . ' not found', FlashType::DANGER);
            return new RedirectResponse(self::ROUTE_INDEX);
        }

        if ($this->request->isAjax()) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Modal ready.',
                redirect: self::ROUTE_INDEX,
                extraData: array_merge($this->getModalData($entity), ['entityData' => $entity->toFormArray()]),
            );
        }

        return $this->render('/footer/footer', $this->getModalData($entity));
    }

    public function delete(): Response|string
    {
        $data = $this->request->getPost()->getAll();
        $result = $this->getFooterModel()->delete($data);

        if (!$result->isSuccess()) {
            $this->flash->add($this->getEntityType() . ' not found', FlashType::DANGER);
            return new RedirectResponse(self::ROUTE_INDEX);
        }

        if ($this->request->isAjax()) {
            return $this->respondSuccess(
                isAjax: true,
                message: $this->getEntityType() . ' deleted successfully',
                redirect: self::ROUTE_INDEX,
            );
        }

        $this->flash->add($this->getEntityType() . ' deleted successfully');
        return new RedirectResponse(self::ROUTE_INDEX);
    }

    public function cancel(): Response|string
    {
        $action = EntityKey::FOOTER_ABOUT->getBasePath() . DS . 'Add';
        $this->flash->getFormData($action);

        if ($this->request->isAjax()) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Modal closed.',
                redirect: self::ROUTE_INDEX,
            );
        }

        return new RedirectResponse(self::ROUTE_INDEX);
    }

    abstract protected function getEntityKeyfield(): ?string;

    abstract protected function getEntityType(): string;

    abstract protected function getSaveRoute(): string;

    abstract protected function getDeleteRoute(): string;

    abstract protected function getModalBuilder(): AbstractFooterModalBuilder;

    abstract protected function getFormFactory(): AbstractFooterFormConfigFactory;

    abstract protected function getEntityData(): ?Entity;

    protected function getModalData(null|Entity $entity = null): array
    {
        $dto = $this->createDTO($entity);
        if ($dto === null) {
            return [];
        }
        $this->getModalBuilder()->setDto($dto);

        $decorated = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->getModalBuilder(),
                'formValues' => $entity ?? $dto->toFormValues(),
                'action' => $this->getSaveRoute(),
                'factory' => $this->getFormFactory(),
            ],
        );

        return $decorated->page();
    }

    abstract protected function createDTO(Entity $entity): ?BaseFooterDTO;

    abstract protected function getFooterModel(): Model;

    protected function getDeleteModal(array $formValues): array
    {
        // Generic delete modal implementation
        // Could be overridden by child classes if needed
        return [
            'modal' => $this->buildDeleteModal($formValues),
            'entity' => $formValues,
        ];
    }

    protected function buildDeleteModal(array $formValues): string
    {
        // Build a generic delete confirmation modal
        // This would use a DeleteModalBuilder or similar
        return '';
    }
}