<?php

namespace Webkul\FlutterwavePayment\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Webkul\Sales\Repositories\OrderRepository;

class FlutterwavePayment extends Payment
{
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
    public function getRedirectUrl($order = null)
    {
        // Prefer order if passed (some flows pass order), otherwise use current cart
        $cart = $order ?: $this->getCart();

        if (! $cart) {
            return route('shop.checkout.cart.index');
        }

        // Billing info (cart or order shape)
        $billing = $cart->billing_address ?? ($cart->billing ?? null);

        // Amount & currency
        $amount = $this->formatCurrencyValue($cart->grand_total ?? $cart->base_grand_total ?? 0);
        $currency = $cart->order_currency_code ?? $cart->cart_currency_code ?? core()->getCurrentCurrencyCode() ?? 'NGN';

        // Get keys from admin config (system.php fields)
        $secretKey     = $this->getConfigData('secret_key');
        $publicKey     = $this->getConfigData('public_key');
        $encryptionKey = $this->getConfigData('encryption_key');

        if (! $secretKey) {
            Log::error('Flutterwave: missing secret_key in payment config');
            return route('shop.checkout.cart.index');
        }

        $txRef = 'BAG-' . ($cart->id ?? uniqid());

        $payload = [
            'tx_ref'       => $txRef,
            'amount'       => (string) $amount,
            'currency'     => $currency,
            'redirect_url' => route('flutterwave.callback'),
            'customer'     => [
                'email' => $billing->email ?? $cart->customer_email ?? null,
                'name'  => trim(($billing->first_name ?? $cart->customer_first_name ?? '') . ' ' . ($billing->last_name ?? $cart->customer_last_name ?? '')),
                'phone' => $this->formatPhone($billing->phone ?? $billing->telephone ?? ''),
            ],
            'payment_options' => 'card,banktransfer,ussd',
            'customizations' => [
                'title'       => $this->getConfigData('title') ?: 'Payment',
                'description' => $this->getConfigData('description') ?: '',
                'logo'        => $this->getImage(),
            ],
        ];

        try {
            $response = Http::withToken($secretKey)
                ->post('https://api.flutterwave.com/v3/payments', $payload)
                ->json();
        } catch (\Throwable $e) {
            Log::error('Flutterwave init exception: ' . $e->getMessage(), ['payload' => $payload]);
            return route('shop.checkout.cart.index');
        }

        if (isset($response['status']) && $response['status'] === 'success' && isset($response['data']['link'])) {
            return $response['data']['link'];
        }

        Log::error('Flutterwave init failed', ['response' => $response, 'payload' => $payload]);

        return route('shop.checkout.cart.index');
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
