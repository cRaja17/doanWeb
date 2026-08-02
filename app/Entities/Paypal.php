<?php

namespace App\Entities;

use Srmklive\PayPal\PayPalFacadeAccessor;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class Paypal
{

    public function payment($order)
    {
        $provider = new PayPalClient;
        $provider = PayPalFacadeAccessor::setProvider();
        $provider->setApiCredentials(config('payment.paypal'));
        $accessToken = $provider->getAccessToken();

        $data = [
            'intent' => 'CAPTURE',
            'application_context' => [
                'return_url' => route('payment.web.success', ['orderId' => $order->transaction_code], true, app()->getLocale()),
                'cancel_url' => route('payment.web.cancel', [], true, app()->getLocale()),
            ],
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $order->unit_payment,
                        'value' => $order->total
                    ]
                ]
            ]
        ];

        $response = $provider->createOrder($data);

        Log::info('PayPal Order Response: ', $response);

        $res['url'] = '';
        if (!empty($response['id']) && $response['id'] != '') {
            foreach ($response['links'] as $key => $val) {
                if ($val['rel'] == "approve") {
                    $res['url'] = $val['href'];
                    $res['errorCode'] = 0;
                }
            }
        } else {
            Log::error('Error creating PayPal order: ', $response);
            $res['errorCode'] = 1;
            $res['message'] = 'Error creating PayPal order: ' . json_encode($response);
        }

        return $res;
    }
}
