<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

/**
 * @property FlashInterface $flash
 * @property FlashRenderer $flashRenderer
 * @property Request $request
 * @property LoggerInterface $logger
 *
 * @method Response redirect(string $url)
 */
trait AjaxResponseTrait
{
    protected function respondSuccess(
        bool $isAjax,
        string $message,
        string $redirect,
        FlashType $flashType = FlashType::SUCCESS,
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        array $extraData = [],
        array $flashOptions = [],
    ): Response {
        if ($isAjax) {
            $dto = FlashMessageDTO::from(
                type:         $flashType,
                message:      $message,
                title:        $flashOptions['title'] ?? null,
                duration:     $flashOptions['duration'] ?? 5000,
                dismissible:  $flashOptions['dismissible'] ?? true,
                showProgress: $flashOptions['showProgress'] ?? true,
                extra:        $flashOptions['extra'] ?? [],
            );

            $response = [
                'success' => true,
                'message' => $message,
                'redirect' => $redirect,
                'flash' => $dto->toArray(),
            ];

            $response = array_merge($response, $extraData);
            $jsonResponse = new JsonResponse($response, HttpStatusCode::HTTP_OK);

            if (in_array($statusCode, [
                HttpStatusCode::HTTP_MOVED_PERMANENTLY,  // 301
                HttpStatusCode::HTTP_FOUND,              // 302
                HttpStatusCode::HTTP_SEE_OTHER,          // 303
                HttpStatusCode::HTTP_TEMPORARY_REDIRECT, // 307
                HttpStatusCode::HTTP_RESUME_INCOMPLETE,  // 308
            ], true)) {
                $jsonResponse->setHeader('X-Redirect-Action', '1');
            }

            return $jsonResponse;
        }

        $this->flash->add($message, $flashType, $flashOptions);

        if (isset($this->logger)) {
            $this->logger->info('Redirect with Flash', [
                'message' => $message,
                'redirect' => $redirect,
                'flash_type' => $flashType->value,
            ]);
        }

        return $this->redirect($redirect);
    }

    protected function respondError(
        bool $isAjax,
        string $message,
        string $redirect,
        FlashType $flashType = FlashType::DANGER,
        HttpStatusCode $statusCode = HttpStatusCode::HTTP_BAD_REQUEST,
        array $extraData = [],
        array $flashOptions = [],
    ): Response {
        $startTime = microtime(true);

        if ($isAjax) {
            $dto = FlashMessageDTO::from(
                type:         $flashType,
                message:      $message,
                title:        $flashOptions['title'] ?? null,
                duration:     $flashOptions['duration'] ?? null,
                dismissible:  $flashOptions['dismissible'] ?? true,
                showProgress: $flashOptions['showProgress'] ?? false,
                extra:        $flashOptions['extra'] ?? [],
            );

            $response = [
                'success' => false,
                'error' => $message,
                'redirect' => $redirect,
                'flash' => $dto->toArray(),
            ];

            $response = array_merge($response, $extraData);

            // Log AJAX error response
            if (isset($this->logger)) {
                $executionTime = microtime(true) - $startTime;
                $this->logger->error('AJAX Error Response', [
                    'message' => $message,
                    'redirect' => $redirect,
                    'execution_time_ms' => round($executionTime * 1000, 2),
                    'flash_type' => $flashType->value,
                    'status_code' => $statusCode->value,
                ]);
            }
            return new JsonResponse($response, $statusCode);
        }

        $this->flash->add($message, $flashType, $flashOptions);

        if (isset($this->logger)) {
            $this->logger->warning('Redirect with Error Flash', [
                'message' => $message,
                'redirect' => $redirect,
                'flash_type' => $flashType->value,
            ]);
        }

        return $this->redirect($redirect);
    }
}