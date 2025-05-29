<?php

declare(strict_types=1);

class PaymentsController extends Controller
{
    public function __construct(private PaymentGatewayInterface $paymentGateway)
    {
    }

    public function pay() : string
    {
        $res = $this->paymentGateway->getResponse();

        return $this->render('pay', ['response' => $res->getBody()]);
    }
}