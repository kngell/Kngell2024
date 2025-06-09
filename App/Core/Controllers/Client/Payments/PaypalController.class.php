<?php

declare(strict_types=1);

class PaypalController extends Controller
{
    public function __construct(private ProductModel $productModel)
    {
        $this->currentModel($productModel);
    }

    public function product() : string
    {
        $this->pageTitle('Product');
        $products = new ProductsDecorator($this);
        $products = new UserCartItemDecorator($products);
        return $this->render('product', $products->page());
    }

    public function checkout() : string
    {
        $this->pageTitle('Checkout');
        return $this->render('checkout');
    }

    public function addToCart() : Response
    {
        $this->pageTitle('Add to Cart');
        $data = $this->request->getPost()->getAll();
        return new RedirectResponse('/paypal/cart');
    }

    public function removeFromCart($id) : Response
    {
        $this->pageTitle('Remove from Cart');
        return new RedirectResponse('/paypal/cart');
    }

    public function updateQty($id) : Response
    {
        $this->pageTitle('Update Quantity');
        return new RedirectResponse('/paypal/cart');
    }

    public function createPayment() : Response
    {
        $this->pageTitle('Create Payment');

        // Logic to create payment

        return new RedirectResponse('/paypal/product');
    }

    /**
     * Process PayPal webhook notifications.
     *
     * This method handles asynchronous event notifications from PayPal
     * for payment status updates, refunds, disputes, etc.
     *
     * @return Response JSON response to PayPal
     */
    public function webhook() : Response
    {
        // Get the raw POST data
        $payload = file_get_contents('php://input');
        $headers = getallheaders();

        // Log the raw webhook for debugging (optional)
        $this->logger->info('PayPal Webhook received', [
            'payload' => $payload,
            'headers' => $headers,
        ]);

        // Verify the webhook signature (important security measure)
        if (! $this->verifyWebhookSignature($payload, $headers)) {
            // Return 401 if signature verification fails
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        // Parse the event data
        $event = json_decode($payload, true);
        $eventType = $event['event_type'] ?? '';

        // Process different event types
        switch ($eventType) {
            case 'PAYMENT.CAPTURE.COMPLETED':
                $this->handlePaymentCompleted($event);
                break;
            case 'PAYMENT.CAPTURE.DENIED':
                $this->handlePaymentDenied($event);
                break;
            case 'PAYMENT.CAPTURE.REFUNDED':
                $this->handlePaymentRefunded($event);
                break;
                // Add other event types as needed

            default:
                // Log unhandled event type
                $this->logger->info('Unhandled PayPal webhook event', ['event_type' => $eventType]);
                break;
        }

        // Always return 200 to acknowledge receipt (PayPal will retry if non-200)
        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Verify the webhook signature from PayPal.
     *
     * @param string $payload The raw webhook payload
     * @param array $headers The request headers
     * @return bool True if signature is valid
     */
    private function verifyWebhookSignature(string $payload, array $headers) : bool
    {
        // Get PayPal signature from headers
        $paypalSignature = $headers['Paypal-Transmission-Sig'] ?? '';
        $paypalCertUrl = $headers['Paypal-Cert-Url'] ?? '';
        $paypalTransmissionId = $headers['Paypal-Transmission-Id'] ?? '';
        $paypalTransmissionTime = $headers['Paypal-Transmission-Time'] ?? '';

        // Get webhook ID from configuration
        $webhookId = $_ENV['PAYPAL_WEBHOOK_ID'] ?? '';

        // In a production environment, you would:
        // 1. Verify the certificate URL belongs to PayPal
        // 2. Download and cache the certificate
        // 3. Use the certificate to verify the signature
        // 4. Check the timestamp to prevent replay attacks

        // For now, we'll return true for development purposes
        // TODO: Implement proper signature verification

        // Log verification attempt
        $this->logger->info('PayPal webhook signature verification', [
            'signature' => $paypalSignature,
            'transmission_id' => $paypalTransmissionId,
            'timestamp' => $paypalTransmissionTime,
        ]);

        // For testing, assume it's valid (replace with actual verification in production)
        return true;
    }

    /**
     * Handle successful payment completion.
     *
     * @param array $event The event data from PayPal
     */
    private function handlePaymentCompleted(array $event) : void
    {
        // Extract payment details
        $resource = $event['resource'] ?? [];
        $orderId = $resource['id'] ?? '';
        $amount = $resource['amount']['value'] ?? 0;
        $currency = $resource['amount']['currency_code'] ?? '';
        $payerId = $resource['payer']['payer_id'] ?? '';

        // Log the successful payment
        $this->logger->info('PayPal payment completed', [
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'payer_id' => $payerId,
        ]);

        // Update order status in database
        try {
            // TODO: Update order status in database
            // $this->orderModel->updateStatus($orderId, 'paid');

            // TODO: Send confirmation email to customer
            // $this->emailService->sendPaymentConfirmation($orderId);
        } catch (Exception $e) {
            $this->logger->error('Failed to process payment completion', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
        }
    }

    /**
     * Handle denied payment.
     *
     * @param array $event The event data from PayPal
     */
    private function handlePaymentDenied(array $event) : void
    {
        $resource = $event['resource'] ?? [];
        $orderId = $resource['id'] ?? '';

        $this->logger->info('PayPal payment denied', ['order_id' => $orderId]);

        // TODO: Update order status in database
        // $this->orderModel->updateStatus($orderId, 'failed');
    }

    /**
     * Handle refunded payment.
     *
     * @param array $event The event data from PayPal
     */
    private function handlePaymentRefunded(array $event) : void
    {
        $resource = $event['resource'] ?? [];
        $orderId = $resource['id'] ?? '';
        $refundAmount = $resource['amount']['value'] ?? 0;

        $this->logger->info('PayPal payment refunded', [
            'order_id' => $orderId,
            'refund_amount' => $refundAmount,
        ]);

        // TODO: Update order status in database
        // $this->orderModel->updateStatus($orderId, 'refunded');
    }
}
