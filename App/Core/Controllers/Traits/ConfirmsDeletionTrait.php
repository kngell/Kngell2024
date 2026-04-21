<?php

declare(strict_types=1);

trait ConfirmsDeletionTrait
{
    use AjaxResponseTrait;

    private const string FLASH_KEY_PREFIX = 'delete_data_';

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

        // Reuse existing flash if same entity
        $existingData = $this->flash->getData($this->getFlashKey());
        if ($existingData && ($existingData['id'] ?? null) === $id) {
            return $this->renderConfirmResponse(
                $existingData,
                $isAjax,
                $id,
            );
        }

        // Validate entity
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

        // Store and respond
        $flashData = $this->buildFlashData($id, $validationResult);
        $this->flash->addData($this->getFlashKey(), $flashData);

        return $this->renderConfirmResponse($flashData, $isAjax, $id);
    }

    abstract protected function getValidator(): AbstractDeleteValidator;

    abstract protected function getLabel(): string;

    abstract protected function getDeleteRoute(): string;

    abstract protected function createDeletionDecorator(array $data): object;

    abstract protected function getConfirmRedirectUrl(string $id): string;

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

    protected function resolveEntityId(): string
    {
        $keyField = $this->getEntityKeyfield();
        return $keyField
            ? $this->request->getPost()->get($keyField, '')
            : $this->request->getPost()->get('public_id', '');
    }

    protected function getEntityKeyfield(): ?string
    {
        return null;
    }

    protected function getFlashKey(): string
    {
        return self::FLASH_KEY_PREFIX
            . strtolower(str_replace(' ', '_', $this->getLabel()));
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

        // Non-AJAX: redirect to page that renders the modal
        return $this->redirect($this->getConfirmRedirectUrl($id));
    }
}