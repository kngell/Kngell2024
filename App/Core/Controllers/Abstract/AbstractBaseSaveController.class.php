<?php

declare(strict_types=1);

abstract class AbstractBaseSaveController extends Controller
{
    use ResolveBlockTypeTrait;

    public function __construct(
        protected SaveServiceInterface $saveService,
        protected SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        $this->layout(NavbarType::ADMIN);
        $this->frm = $frm;
    }

    final public function index(): Response
    {
        $this->resolveBlockType();
        $this->saveService->setBlockType($this->blockType);

        $result = $this->saveWorkflow->handle(
            request: $this->request,
            saveService: $this->saveService,
        );

        return $this->respondFromWorkflowResult($result);
    }

    abstract protected function getEntitySpecificPageTitle(): string;

    private function respondFromWorkflowResult(SaveResult $result): Response
    {
        $isAjax = $this->request->isAjax();

        if ($isAjax && ob_get_level()) {
            ob_clean();
        }

        return match ($result->status) {
            OperationStatus::SUCCESS => $this->respondToSuccessfulSave($result, $isAjax),

            OperationStatus::VALIDATION_ERROR => $this->respondError(
                isAjax: $isAjax,
                message: $result->message,
                redirect: $result->redirectUrl,
                flashType: FlashType::DANGER,
                statusCode: $result->statusCode,
                extraData: [
                    'errors' => $result->errors,
                ],
            ),

            OperationStatus::FILE_STORAGE_ERROR,
            OperationStatus::DATABASE_ERROR => $this->respondError(
                isAjax: $isAjax,
                message: $result->message,
                redirect: $result->redirectUrl,
                statusCode: $result->statusCode,
                extraData: $result->extraData,
            ),

            OperationStatus::NO_DATA => $this->respondError(
                isAjax: $isAjax,
                message: $result->message,
                redirect: $result->redirectUrl,
            ),

            default => $this->respondError(
                isAjax: $isAjax,
                message: 'Unexpected save result.',
                redirect: $this->navigationHistory->getRedirectUrl() ?? '/',
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            ),
        };
    }

    private function respondToSuccessfulSave(
        SaveResult $result,
        bool $isAjax,
    ): Response {
        $flashType = $result->anythingChanged
            ? FlashType::SUCCESS
            : FlashType::INFO;

        $redirectUrl = $result->redirectUrl
            ?: $this->navigationHistory->getRedirectUrl();

        $message = $result->message;

        return $this->respondSuccess(
            isAjax: $isAjax,
            message: $message,
            redirect: $redirectUrl,
            flashType: $flashType,
            extraData: $result->extraData,
            flashOptions: ['duration' => 10000],
        );
    }
}