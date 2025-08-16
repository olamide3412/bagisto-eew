<?php

namespace Webkul\FlutterwavePayment\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Sales\Repositories\OrderRepository;

use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shop\Http\Controllers\Controller;

class FlutterwavePayment extends Payment
{
      public function __construct(protected OrderRepository $orderRepository) {}
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'flutterwavepayment';
 /**
     * Build the payment initialization and return the redirect link.
     * Uses cart data (Bagisto calls getRedirectUrl while still using cart).
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $cart = Cart::getCart();

        // Create the order first
        $data = (new OrderResource($cart))->jsonSerialize();
        $order = $this->orderRepository->create($data);
        Cart::deActivateCart();

        // Prepare payment request
        $payload = [
            'tx_ref' => 'BAG-' . $order->id,
            'amount' => $order->grand_total,
            'currency' => $order->order_currency_code,
            'redirect_url' => route('flutterwave.callback'),
            'customer' => [
                'email' => $order->customer_email,
                'name'  => $order->customer_first_name . ' ' . $order->customer_last_name,
                'phone' => $order->customer_phone ?? '',
            ],
            'payment_options' => 'card,banktransfer,ussd',
            'customizations' => [
                'title' => 'Flutterwave Payment',
                'description' => 'Payment for Order #' . $order->id,
                'logo' => core()->getConfigData('general.design.admin_logo.logo_image'),
            ]
        ];

        //$secretKey = config('services.flutterwave.secret_key');
        $secretKey     = $this->getConfigData('secret_key');
        //$publicKey     = $this->getConfigData('public_key');
        //$encryptionKey = $this->getConfigData('encryption_key');


        try {
            $response = Http::withToken($secretKey)
                ->post('https://api.flutterwave.com/v3/payments', $payload)
                ->json();

        } catch (\Throwable $e) {
            Log::error('Flutterwave init exception', ['error' => $e->getMessage()]);
            Log::error('Flutterwave init exception: ' . $e->getMessage(), ['payload' => $payload]);
             return route('shop.checkout.cart.index'); // <== HERE
        }

        if (isset($response['status']) && $response['status'] === 'success' && isset($response['data']['link'])) {
            session()->flash('order_id', $order->id);
            return $response['data']['link']; // <== Payment page link
        }

        Log::error('Flutterwave init failed', ['response' => $response, 'payload' => $payload]);
        session()->flash('error', 'Payment initialization failed. Please try again.');
        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Returns payment method image (uses admin-configured file)
     *
     * @return string|null
     */
    public function getImage()
    {
        // Bagisto system config might use 'logo' or 'image' name — try both
        $url = $this->getConfigData('logo') ?: $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/payments/flutterwave.png', 'shop');
    }

    /**
     * Format a currency value according to API constraints
     */
    public function formatCurrencyValue($number): float
    {
        return round((float) $number, 2);
    }

    /**
     * Normalize phone number (strip non-digits)
     */
    public function formatPhone($phone): string
    {
        return preg_replace('/[^0-9]/', '', (string) $phone);
    }

    /**
     * Title from admin config
     */
    public function getTitle()
    {
        return $this->getConfigData('title') ?: 'Flutterwave';
    }

    /**
     * Active state from admin config
     */
    public function isAvailable()
    {
        return $this->getConfigData('active') == 1;
    }
}
