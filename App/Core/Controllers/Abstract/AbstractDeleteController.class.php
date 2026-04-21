<?php

declare(strict_types=1);

abstract class AbstractDeleteController extends Controller
{
    use AjaxResponseTrait;

    public function delete(): Response
    {
        $isAjax = $this->request->isAjax();
        $dto = DeleteDTO::fromRequest($this->request);
        $redirectUrl = $this->resolveRedirectUrl();

        if (!$dto->confirmed) {
            return $this->respondSuccess(
                $isAjax,
                $this->getLabel() . ' deletion cancelled.',
                $redirectUrl,
                FlashType::INFO,
            );
        }

        $flashData = $this->flash->getData($this->getFlashKey());

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
                $flashData['id'],
                $dto->deleteOption,
                $this->eventManager,
            );

            return $this->handleDeleteResult(
                $result,
                $dto->deleteOption,
                $isAjax,
            );
        } catch (Exception $e) {
            error_log(
                $this->getLabel() . ' deletion exception: '
                . $e->getMessage(),
            );

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

    // --- Abstract contracts ---

    abstract protected function getDeleteService(): AbstractDeleteService;

    abstract protected function getLabel(): string;

    // --- Private helpers ---

    private function resolveRedirectUrl(): string
    {
        return $this->getRedirectUrl()
            ?? DeletionFlowConfig::DEFAULT_REDIRECT->value;
    }

    private function getFlashKey(): string
    {
        return DeletionFlowConfig::flashKeyFor($this->getLabel());
    }

    private function handleDeleteResult(
        DeleteResult $result,
        string $deleteOption,
        bool $isAjax,
    ): Response {
        $redirectUrl = $this->resolveRedirectUrl();

        if (!$result->isSuccess()) {
            error_log(
                $this->getLabel() . ' deletion failed: '
                . json_encode($result->getErrorDetails()),
            );

            return $this->respondError(
                $isAjax,
                $result->getErrorMessage()
                    ?? $this->getLabel() . ' deletion failed.',
                $redirectUrl,
                FlashType::DANGER,
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                $isAjax
                    ? ['error_details' => $result->getErrorDetails()]
                    : [],
            );
        }

        $message = $this->buildSuccessMessage($result, $deleteOption);
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