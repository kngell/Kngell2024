<?php

declare(strict_types=1);
/**
 * @property FlashInterface $flash
 * @property Request $request
 * @property Response $response
 *
 * @method Response redirect(string $url)
 */
trait AjaxResponseTrait
{
    private function respondSuccess(
        bool $isAjax,
        string $message,
        string $redirect,
        FlashType $flashType = FlashType::SUCCESS,
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $extraData = [],
    ): Response {
        if ($isAjax) {
            return new JsonResponse(array_merge([
                'success' => true,
                'message' => $message,
                'type' => $flashType->value,
                'redirect' => $redirect,
            ], $extraData), $statusCode);
        }

        $this->flash->add($message, $flashType);
        return $this->redirect($redirect);
    }

    private function respondError(
        bool $isAjax,
        string $message,
        string $redirect,
        FlashType $flashType = FlashType::DANGER,
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_BAD_REQUEST,
        array $extraData = [],
    ): Response {
        if ($isAjax) {
            return new JsonResponse(array_merge([
                'success' => false,
                'error' => $message,
                'type' => $flashType->value,
                'redirect' => $redirect,
            ], $extraData), $statusCode);
        }

        $this->flash->add($message, $flashType);
        return $this->redirect($redirect);
    }
}