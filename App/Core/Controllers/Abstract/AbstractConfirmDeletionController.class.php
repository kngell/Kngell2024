<?php

declare(strict_types=1);

abstract class AbstractConfirmDeletionController extends Controller
{
    use AjaxResponseTrait;

    public function __construct(
        protected HtmlTemplatePathInterface $templatePath,
    ) {
        $this->layout('admin');
    }

    public function confirm(): Response|string
    {
        $isAjax = $this->request->isAjax();
        $id = $this->resolveEntityId();
        $redirectUrl = $this->resolveRedirectUrl();

        if (empty($id)) {
            return $this->respondError(
                $isAjax,
                'No ' . strtolower($this->getLabel()) . ' selected.',
                $redirectUrl,
                FlashType::WARNING,
                HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        $existingData = $this->flash->getData($this->getFlashKey());
        if ($existingData && ($existingData['id'] ?? null) === $id) {
            return $this->renderConfirmResponse(
                $existingData,
                $isAjax,
                $id,
            );
        }

        $validationResult = $this->getValidator()->validate($id);

        if (!$validationResult->isValid()) {
            return $this->respondError(
                $isAjax,
                $validationResult->getErrorMessage(),
                $redirectUrl,
                FlashType::DANGER,
                HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        $flashData = $this->buildFlashData($id, $validationResult);
        $this->flash->addData($this->getFlashKey(), $flashData);

        return $this->renderConfirmResponse($flashData, $isAjax, $id);
    }

    // --- Abstract contracts ---

    abstract protected function getValidator(): AbstractDeleteValidator;

    abstract protected function getLabel(): string;

    abstract protected function getDeleteRoute(): string;

    abstract protected function getConfirmRedirectUrl(string $id): string;

    abstract protected function createDeletionDecorator(array $data): object;

    // --- Overridable ---

    protected function getEntityKeyfield(): ?string
    {
        return null;
    }

    protected function buildFlashData(
        string $id,
        DeletionValidatorResult $validationResult,
    ): array {
        return [
            'id' => $id,
            'name' => $validationResult->getDisplayName(),
            'warnings' => $validationResult->getWarnings(),
            'image' => $validationResult->getDisplayImage(),
            'metadata' => $validationResult->getAllMetadata(),
            'timestamp' => time(),
        ];
    }

    // --- Private helpers ---

    private function resolveEntityId(): string
    {
        $keyField = $this->getEntityKeyfield();

        return $keyField
            ? $this->request->getPost()->get($keyField, '')
            : $this->request->getPost()->get('public_id', '');
    }

    private function renderConfirmResponse(
        array $flashData,
        bool $isAjax,
        string $id,
    ): Response|string {
        if ($isAjax) {
            $decorator = $this->createDeletionDecorator($flashData);
            $pageData = $decorator->page();

            if (empty($pageData['confirmDeletionModal'])) {
                return $this->respondError(
                    true,
                    'Failed to generate deletion confirmation modal.',
                    $this->resolveRedirectUrl(),
                    FlashType::DANGER,
                    HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            return new JsonResponse(
                array_merge(['success' => true], $pageData),
            );
        }

        return $this->redirect($this->getConfirmRedirectUrl($id));
    }

    private function resolveRedirectUrl(): string
    {
        return $this->getRedirectUrl()
            ?? DeletionFlowConfig::DEFAULT_REDIRECT->value;
    }

    private function getFlashKey(): string
    {
        return DeletionFlowConfig::flashKeyFor($this->getLabel());
    }
}