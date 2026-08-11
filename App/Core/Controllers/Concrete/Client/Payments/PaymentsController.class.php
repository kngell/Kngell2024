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
        return $this->render('payment-paypal-fail');
    }

    public function paypalSuccessOrder() : string
    {
        $this->pageTitle('Complete order');
        $query = $this->request->getQuery();
        $res = $this->payment->capturePayment($query->get('token'));

        // $transaction = $res['purchase_units'][0]['payments']['captures'][0] ?? null;
        // if ($transaction) {
        //     $transactionId = $transaction['id'];
        //     $status = $transaction['status'];
        //     $amount = $transaction['amount']['value'];
        //     $currency = $transaction['amount']['currency_code'];
        //     $payerEmail = $res['payer']['email_address'] ?? null;
        // }
        return $this->render('paypal-success-order');
    }
}