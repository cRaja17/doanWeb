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
                'return_url' => route('payment.web.success', ['orderId' => $order->transaction_code], true),
                // Mang theo mã đơn để khi khách huỷ còn biết đơn nào cần đóng lại,
                // thay vì để đơn treo ở trạng thái chờ thanh toán mãi mãi.
                'cancel_url' => route('payment.web.cancel', ['orderId' => $order->transaction_code], true),
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

        // Mặc định là lỗi: chỉ khi tìm được link "approve" mới coi là tạo thành
        // công, nếu không nơi gọi sẽ redirect khách tới một URL rỗng.
        $res = [
            'url' => '',
            'errorCode' => 1,
            'message' => 'Error creating PayPal order: ' . json_encode($response),
        ];

        foreach ($response['links'] ?? [] as $val) {
            if (($val['rel'] ?? null) === 'approve' && !empty($val['href'])) {
                $res['url'] = $val['href'];
                $res['errorCode'] = 0;
                $res['message'] = '';
            }
        }

        if ($res['errorCode'] !== 0) {
            Log::error('Error creating PayPal order: ', is_array($response) ? $response : []);
        }

        return $res;
    }
}
