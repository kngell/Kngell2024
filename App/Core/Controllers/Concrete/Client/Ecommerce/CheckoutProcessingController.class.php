<?php

declare(strict_types=1);

class CheckoutProcessingController extends Controller
{
    public function __construct(
        private CheckoutService $service,
    ) {
    }

    public function saveUserData(): Response
    {
        $isAjax = $this->request->isAjax();
        $step = $this->request->get('step', null);

        if ($step === null && $isAjax) {
            return $this->respondError(
                isAjax: true,
                message: 'Invalid Step',
                redirect: $this->getRedirectUrl(),
                extraData: [],
            );
        }
        $stepResponse = $this->service->saveStep();
    }
}