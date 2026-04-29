<?php

declare(strict_types=1);

abstract class AbstractConfirmDeletionController extends Controller
{
    use AjaxResponseTrait;

    public function __construct(
        protected HtmlTemplatePathInterface $templatePath,
        FormCreatorService $frm,
    ) {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function confirm(): Response
    {
        $isAjax = $this->request->isAjax();
        $id = $this->resolveEntityId();
        $redirectUrl = $this->resolveRedirectUrl();

        if (empty($id)) {
            return $this->respondError(
                isAjax: $isAjax,
                message: 'No ' . strtolower($this->getLabel()) . ' selected.',
                redirect: $redirectUrl,
                flashType: FlashType::WARNING,
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        $existingData = $this->flash->peekData($this->getFlashKey());
        if ($existingData && ($existingData['id'] ?? null) === $id) {
            return $this->renderConfirmResponse($existingData, $isAjax, $id);
        }

        try {
            $validationResult = $this->getValidator()->validate($id);
        } catch (Throwable $e) {
            return $this->respondError(
                isAjax: $isAjax,
                message: 'An unexpected error occurred during validation.',
                redirect: $redirectUrl,
                flashType: FlashType::DANGER,
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }

        if (!$validationResult->isValid()) {
            return $this->respondError(
                isAjax: $isAjax,
                message: $validationResult->getErrorMessage(),
                redirect: $redirectUrl,
                flashType: FlashType::DANGER,
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        $flashData = $this->buildFlashData($id, $validationResult);
        $this->flash->addData($this->getFlashKey(), $flashData);

        return $this->renderConfirmResponse($flashData, $isAjax, $id);
    }

    public function cancel(): Response
    {
        $this->flash->removeData($this->getFlashKey());

        $isAjax = $this->request->isAjax();
        $redirectUrl = $this->resolveRedirectUrl();

        if ($isAjax) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Deletion cancelled.',
                redirect: $redirectUrl,
                flashType: FlashType::INFO,
            );
        }

        return $this->redirect($redirectUrl);
    }
    // --- Abstract contracts ---

    abstract protected function getValidator(): AbstractDeleteValidator;

    abstract protected function getLabel(): string;

    abstract protected function getDeleteRoute(): string;

    abstract protected function getConfirmRedirectUrl(array $id): string;

    abstract protected function createDeletionDecorator(array $data): object;

    abstract protected function getEntityKeyfield(): ?string;

    protected function buildFlashData(
        array $id,
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
    private function resolveEntityId(): array
    {
        $post = $this->request->getPost();
        $keyField = $this->getEntityKeyfield();

        // Try keyField first
        $value = $post->get($keyField, '');
        if (!empty($value)) {
            return ['key' => $keyField, 'value' => $value];
        }

        // Fallback to public_id
        $value = $post->get('public_id', '');
        if (!empty($value)) {
            return ['key' => 'public_id', 'value' => $value];
        }

        return ['key' => '', 'value' => ''];
    }

    private function renderConfirmResponse(
        array $flashData,
        bool $isAjax,
        array $id,
    ): Response {
        if ($isAjax) {
            try {
                $decorator = $this->createDeletionDecorator($flashData);
                $pageData = $decorator->page();
            } catch (Throwable $e) {
                return $this->respondError(
                    isAjax: true,
                    message: 'An error occurred while preparing the deletion confirmation.',
                    redirect: $this->resolveRedirectUrl(),
                    flashType: FlashType::DANGER,
                    statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            if (empty($pageData['confirmDeletionModal'])) {
                return $this->respondError(
                    isAjax: true,
                    message: 'Failed to generate deletion confirmation modal.',
                    redirect: $this->resolveRedirectUrl(),
                    flashType: FlashType::DANGER,
                    statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                );
            }

            return $this->respondSuccess(
                isAjax: true,
                message: 'Confirmation ready.',
                redirect: $this->getConfirmRedirectUrl($id),
                extraData: $pageData,
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