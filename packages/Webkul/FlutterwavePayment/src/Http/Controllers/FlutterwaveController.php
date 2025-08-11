<?php

namespace Webkul\FlutterwavePayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shop\Http\Controllers\Controller;

class FlutterwaveController extends Controller
{
    public function __construct(protected OrderRepository $orderRepository) {}

    /**
     * Redirect to Flutterwave payment page
     */
    public function redirect()
    {
        return view('flutterwavepayment::redirect');
    }

    public function callback(Request $request)
    {
        return $this->success($request);
    }
    /**
     * Cancel payment
     */
    public function cancel()
    {
        session()->flash('error', 'Flutterwave payment was cancelled.');

        return redirect()->route('shop.checkout.cart.index');
    }


    /**
     * Successful payment callback
     */
    public function success(Request $request)
    {
        $status = $request->status;
        $transactionId = $request->transaction_id;

        if ($status === 'successful') {
            // Verify payment with Flutterwave API
            $verify = Http::withToken(config('services.flutterwave.secret_key'))
                ->get("https://api.flutterwave.com/v3/transactions/{$transactionId}/verify")
                ->json();

           // if (($verify['status'] ?? '') === 'success') {
                $cart = Cart::getCart();

                if ($cart) {
                    $data = (new OrderResource($cart))->jsonSerialize();

                    $order = $this->orderRepository->create($data);

                    Cart::deActivateCart();

                    session()->flash('order_id', $order->id);

                    return redirect()->route('shop.checkout.onepage.success');
                } else{
                    Log::error('Flutterwave status cart not found ', ['cart' => $cart]);
                }
            // } else{
            //      Log::error('Flutterwave status', ['response' => $verify['status']]);
            // }
        }

        return redirect()->route('shop.checkout.cart.index')
            ->with('error', 'Payment verification failed.');
    }
}
