<?php

namespace App\Http\Controllers\Web\Payment;

use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\Order;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\PayPalFacadeAccessor;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

use App\Http\Controllers\Web\FontEndController;

class PaypalController extends FontEndController
{
    public function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider = PayPalFacadeAccessor::setProvider();
        $provider->setApiCredentials(config('payment.paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);
        $locale = app()->getLocale();
        $orderId = $request->query('orderId');
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {

            if ($orderId) {
                $order = Order::where('transaction_code', $orderId)->first();

                if ($order) {
                    $order->status = Order::STATUS_PAID;
                    $order->save();
                    // Create session for orderId
                    session(['orderId' => $order->transaction_code]);

                    // Redirect to thanks page
                    return redirect(route('payment.web.thanks', [], true, $locale));
                } else {
                    Log::error('Order not found for orderId: ' . $orderId);
                    return redirect(route('payment.web.checkout', [], true, $locale))->with('error', 'Order not found');
                }
            } else {
                Log::error('Order ID not found in the request');
                return redirect(route('payment.web.checkout', [], true, $locale))->with('error', 'Order ID not found');
            }
        }
        // Delete order if payment not completed
        if ($orderId) {
            $order = Order::where('transaction_code', $orderId)->first();
            if ($order) {
                $order->delete();
            }
        }
        Log::error('Payment not completed');
        return redirect(route('payment.web.checkout', [], true, $locale))->with('error', 'Payment not completed');
    }


    public function cancel()
    {

        return redirect()->route('payment.web.checkout');
    }
}
