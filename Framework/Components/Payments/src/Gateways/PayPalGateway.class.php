<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\Dotenv\Dotenv;

class PayPalGateway implements PaymentGatewayInterface
{
    private mixed $response;

    public function __construct(Dotenv $dotenv)
    {
        $dotenv->load(dirname(getcwd()) . '/.env');
        $this->response = $this->connectWithGuzzle();
    }

    public function isSuccess(): bool
    {
        return true;
    }

    public function pay(float $amount, string $currency): string
    {
        return "Payment of $amount $currency processed through PayPal.";
    }

    public function refund(string $transactionId, float $amount): string
    {
        // Logic to refund payment through PayPal
        return "Refund of $amount for transaction $transactionId processed through PayPal.";
    }

    public function getTransactionDetails(string $transactionId): array
    {
        // Logic to retrieve transaction details from PayPal
        return [
            'transactionId' => $transactionId,
            'status' => 'Completed',
            'amount' => 100.00,
            'currency' => 'USD',
        ];
    }

    /**
     * @return mixed
     */
    public function getResponse(): mixed
    {
        return $this->response;
    }

    private function connectWithGuzzle() : mixed
    {
        $client = new Client();
        if (empty($_ENV['PAYPAL_CLIENT_ID']) || empty($_ENV['PAYPAL_CLIENT_SECRET'])) {
            throw new RuntimeException('PayPal client ID and secret must be set in the environment variables.');
        }
        // Ensure that the PayPal client ID and secret are set
        $credentials = base64_encode($_ENV['PAYPAL_CLIENT_ID'] . ':' . $_ENV['PAYPAL_CLIENT_SECRET']);
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . $credentials,
        ];
        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials',
            ]];
        $request = new Request('POST', $_ENV['PAYPAL_SANDBOX_URL'], $headers);
        return $client->sendAsync($request, $options)->wait();
    }

    private function connectWithCurl() : mixed
    {
        $credentials = base64_encode($_ENV['PAYPAL_CLIENT_ID'] . ':' . $_ENV['PAYPAL_CLIENT_SECRET']);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v1/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' . $credentials,
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}