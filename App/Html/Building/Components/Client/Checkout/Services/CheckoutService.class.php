<?php

declare(strict_types=1);

class CheckoutService
{
    private const string CHECKOUT_DATA_KEY = 'checkout_data';

    public function __construct(
        private SessionInterface $session,
        private UserCartItemService $userCart,
    ) {
    }

    public function saveStep(string $step, array $data): array
    {
        // Validate step exists
        if (!in_array($step, ['step1', 'step2', 'step3', 'step4', 'step5'])) {
            return ['success' => false, 'error' => 'Invalid step'];
        }
        return [];
    }
}