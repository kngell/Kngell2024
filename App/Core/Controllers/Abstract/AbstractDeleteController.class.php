<?php

declare(strict_types=1);

abstract class AbstractDeleteController extends Controller
{
    use ResolveRedirectTrait;
    use ResolveBlockTypeTrait;

    public function delete(): Response
    {
        $isAjax = $this->request->isAjax();
        $this->resolveBlockType();
        $dto = DeleteDTO::fromRequest($this->request);
        $redirectUrl = $this->resolveRedirectUrl();

        if (!$dto->confirmed) {
            return $this->respondSuccess(
                isAjax: $isAjax,
                message: $this->getLabel() . ' deletion cancelled.',
                redirect:$redirectUrl,
                flashType: FlashType::INFO,
            );
        }

        $flashData = $this->flash->getData($this->getFlashKey($dto->blockType));

        if (!$flashData) {
            return $this->respondError(
                $isAjax,
                'Deletion session expired. Please try again.',
                $redirectUrl,
                FlashType::WARNING,
                HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        try {
            $result = $this->getDeleteService()->delete(
                $flashData,
                $dto->deleteOption,
                $dto->blockType,
            );

            return $this->handleDeleteResult($result, $dto->deleteOption, $isAjax, $dto->blockType);
        } catch (Exception $e) {
            error_log($this->getLabel() . ' deletion exception: ' . $e->getMessage());

            return $this->respondError(
                $isAjax,
                'An unexpected error occurred while deleting the '
                    . strtolower($this->getLabel()) . '.',
                $redirectUrl,
                FlashType::DANGER,
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    abstract protected function getDeleteService(): AbstractDeleteService;

    abstract protected function getLabel(): string;

    abstract protected function resolveRedirectUrl(): string;
    // --- Private helpers ---

    // protected function resolveRedirectUrl(?string $blockType = null): string
    // {
    //     return $this->getRedirectUrl()
    //         ?? DeletionFlowConfig::DEFAULT_REDIRECT->value;
    // }

    private function getFlashKey(?string $blockType = null): string
    {
        return DeletionFlowConfig::flashKeyFor($this->getLabel());
    }

    private function handleDeleteResult(
        DeleteResult $result,
        string $deleteOption,
        bool $isAjax,
        ?string $blockType = null,
    ): Response {
        $redirectUrl = $this->resolveRedirectUrl();

        if (!$result->isSuccess()) {
            error_log(
                $this->getLabel() . ' deletion failed: '
                . json_encode($result->getErrorDetails()),
            );
            $code = (int) $result->getErrorDetails()['code'];
            return $this->respondError(
                isAjax: $isAjax,
                message: $result->getErrorMessage()
                    ?? $this->getLabel() . ' deletion failed.',
                redirect: $redirectUrl,
                flashType: FlashType::DANGER,
                statusCode: HttpStatusCode::tryFrom($code) ?? HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                extraData: $isAjax
                    ? ['error_details' => $result->getErrorDetails()]
                    : [],
            );
        }

        $message = $this->buildSuccessMessage($result, $deleteOption, $blockType);
        $flashType = $result->wasSkipped()
            ? FlashType::INFO
            : FlashType::SUCCESS;

        $extraData = $isAjax ? [
            'data' => [
                'name' => $result->getName(),
                'was_skipped' => $result->wasSkipped(),
                'skip_reason' => $result->wasSkipped()
                    ? $result->getSkipReason()
                    : null,
                'deletion_type' => $deleteOption,
                'operation' => 'DELETE',
            ],
        ] : [];

        return $this->respondSuccess(
            $isAjax,
            $message,
            $redirectUrl,
            $flashType,
            HttpStatusCode::HTTP_OK,
            $extraData,
        );
    }

    private function buildSuccessMessage(
        DeleteResult $result,
        string $deleteOption,
        ?string $blockType = null,
    ): string {
        $label = $this->getLabel();

        if ($result->wasSkipped()) {
            if (str_contains(
                $result->getSkipReason(),
                'already archived',
            )) {
                return sprintf(
                    '%s "%s" was already archived.',
                    $label,
                    $result->getName(),
                );
            }

            return sprintf(
                '%s "%s" - %s',
                $label,
                $result->getName(),
                $result->getSkipReason(),
            );
        }

        $action = ($deleteOption === 'permanent')
            ? 'permanently deleted'
            : 'archived';

        return sprintf(
            '%s "%s" %s successfully.',
            $label,
            $result->getName(),
            $action,
        );
    }
}