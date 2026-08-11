<?php

declare(strict_types=1);

abstract class AbstractConfirmDeletionController extends Controller
{
    use ResolveDataTrait;

    public function __construct(
        FormCreatorService $frm,
        protected ConfirmDeletionFormConfigFactory $factory,
    ) {
        $this->layout(NavbarType::ADMIN);
        $this->frm = $frm;
    }

    public function confirm(): Response
    {
        $isAjax = $this->request->isAjax();
        $id = $this->resolveEntityId();
        $this->resolveBlockType();
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

    abstract protected function getValidator(): AbstractDeleteValidator;

    abstract protected function entityClass(): string;

    abstract protected function getLabel(): string;

    abstract protected function getConfirmRedirectUrl(array $id): string;

    abstract protected function createDeletionDecorator(array $data): object;

    abstract protected function getEntityKeyfield(): ?string;

    protected function buildFlashData(
        array $id,
        DeletionValidatorResult $validationResult,
        ?string $blockType = null,
    ): array {
        return [
            'id' => $id,
            'name' => $validationResult->getDisplayName(),
            'warnings' => $validationResult->getWarnings(),
            'image' => $validationResult->getDisplayImage(),
            'metadata' => $validationResult->getAllMetadata(),
            'timestamp' => time(),
            'block_type' => $blockType,
        ];
    }

    // --- Private helpers ---
    // private function resolveEntityId(): array
    // {
    //     $post = $this->request->getPost();
    //     $keyField = $this->getEntityKeyfield();

    //     $value = $post->get($keyField, '');
    //     if (!empty($value)) {
    //         return ['key' => $keyField, 'value' => $value];
    //     }

    //     $value = $post->get('public_id', '');
    //     if (!empty($value)) {
    //         if (!is_string($value)) {
    //             throw new InvalidArgumentException('Invalid public_id payload type received.');
    //         }

    //         if (!StringUtils::isUuid($value) && !preg_match('/^[a-zA-Z0-9_\-]+$/', $value)) {
    //             throw new InvalidArgumentException(sprintf(
    //                 'Security Violation: Malformed public_id string provided for entity class %s.',
    //                 $this->entityClass(),
    //             ));
    //         }

    //         return ['key' => 'public_id', 'value' => $value];
    //     }
    //     return [];
    // }

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

    private function getFlashKey(): string
    {
        return DeletionFlowConfig::flashKeyFor($this->getLabel());
    }
}