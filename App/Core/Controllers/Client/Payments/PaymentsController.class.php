<?php

declare(strict_types=1);

class PaymentsController extends Controller
{
    public function __construct(private PaymentGatewayInterface $payment, private CartModel $cartModel)
    {
    }

    public function pay() : Response|string
    {
        $userId = $this->session->get(CURRENT_USER_SESSION_NAME);
        $cartItems = $this->cartModel->getCart($userId);
        if (! empty($cartItems)) {
            $paymentLink = $this->payment->pay($cartItems, $_ENV['CURRENCY']);
        }
        if ($paymentLink) {
            return new RedirectResponse($paymentLink);
        }
        $this->eventManager->notify(PaymentEvent::class, $this);
        return $this->render('payment-success');
    }
}