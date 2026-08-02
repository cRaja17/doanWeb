<?php

namespace App\Http\Controllers\Web;

use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\Order;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;

class PaymentController extends FontEndController
{
    private PayPalClient $paypal;

    public function __construct(PayPalClient $paypal)
    {
        $this->paypal = $paypal;
        $this->configurePayPal();
    }

    private function configurePayPal()
    {
        try {
            $this->paypal->setApiCredentials($this->buildPaypalConfig(config('paypal')));
            $this->paypal->setAccessToken($this->paypal->getAccessToken());
        } catch (\Exception $e) {
            Log::error('PayPal configuration error: ' . $e->getMessage());
            // Handle the error according to your needs
        }
    }

    private function buildPaypalConfig($config)
    {
        return [
            'mode' => data_get($config, 'mode'),
            'sandbox' => [
                'client_id' => data_get($config, 'sandbox.client_id'),
                'client_secret' => data_get($config, 'sandbox.client_secret'),
                'app_id' => data_get($config, 'sandbox.app_id'),
            ],
            'live' => [
                'client_id' => data_get($config, 'live.client_id'),
                'client_secret' => data_get($config, 'live.client_secret'),
                'app_id' => data_get($config, 'live.app_id'),
            ],
            'payment_action' => data_get($config, 'payment_action'),
            'currency' => data_get($config, 'currency'),
            'notify_url' => data_get($config, 'notify_url'),
            'locale' => data_get($config, 'locale'),
            'validate_ssl' => data_get($config, 'validate_ssl'),
            'webhook_id' => data_get($config, 'webhook_id'),
        ];
    }

    public function createOrder(Request $request)
    {
        try {
            $locale = app()->getLocale();

            $shippingData = $request->only([
                'firstName',
                'lastName',
                'country',
                'state',
                'zipCode',
                'address',
                'email',
                'phone',
                'description'
            ]);

            $cartContent = Cart::content();

            $totalPrice = 0;
            $purchaseItems = [];
            $currency_code = 'USD';

            foreach ($cartContent as $item) {
                if ($locale === 'vi') {
                    $currency_locale = 'en';
                    $price = $item->options['price'][$currency_locale];
                    $totalPrice += $price * $item->qty;

                    $purchaseItems[] = [
                        'code' => $item->id,
                        'name' => $item->options['name'][$locale],
                        'unit_amount' => [
                            'currency_code' => 'USD',
                            'value' => $price,
                        ],
                        'quantity' => $item->qty,
                    ];
                } else {
                    $price = $item->options['price']['en'];
                    $totalPrice += $price * $item->qty;

                    $purchaseItems[] = [
                        'code' => $item->id,
                        'name' => $item->options['name'][$locale],
                        'unit_amount' => [
                            'currency_code' => $item->options['unit']['en'],
                            'value' => $price,
                        ],
                        'quantity' => $item->qty,
                    ];
                }
            }
            $response = $this->paypal->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency_code,
                            'value' => $totalPrice,
                            "breakdown" => [
                                "item_total" => [
                                    "currency_code" => $currency_code,
                                    "value" => $totalPrice
                                ],
                            ],
                        ],

                        'description' => $shippingData['description'],
                        'items' => $purchaseItems,
                    ],
                ],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);


            if ($response['status'] == 'CREATED') {
                $orderId = $response['id'];
                $order = new Order();
                $order->transaction_code = $orderId;
                $order->name = $shippingData['firstName'] . ' ' . $shippingData['lastName'];
                $order->email = $shippingData['email'];
                $order->phone = $shippingData['phone'];
                $order->address = $shippingData['address'] . '-' . $shippingData['state'] . '-' . $shippingData['country'];
                $order->message = $shippingData['description'];
                $order->method_payment = 'PayPal';
                $order->content = json_encode($purchaseItems);
                $order->total = $totalPrice;
                $order->unit_payment = $currency_code;
                $order->ip = $request->ip();
                $order->proxy = $request->server('HTTP_USER_AGENT');
                $order->status = Order::STATUS_PENDING;
                $order->save();

                if ($order->id) {
                    Cart::destroy();
                }

                return response()->json(['id' => $orderId]);
            } else {
                return response()->json(['error' => 'Error creating PayPal order'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error creating PayPal order: ' . $e->getMessage());
            return response()->json(['error' => 'Error creating PayPal order'], 500);
        }
    }




    public function captureOrder($orderId)
    {
        $locale = app()->getLocale();
        try {
            $capturedOrder = $this->paypal->capturePaymentOrder($orderId);

            if ($capturedOrder['status'] == 'COMPLETED') {
                $order = Order::where('transaction_code', $orderId)->first();
                $order->status = Order::STATUS_PAID;
                $order->save();

                $data = [
                    'id' => $order->transaction_code,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                    'address' => $order->address,
                    'total' => $order->total,
                    'unit' => $order->unit_payment,
                    'date' => $order->created_at,
                    'payment' => $order->method_payment,
                    'status' => config('config.payment.status.' . $order->status . '.' . $locale),
                    'products' => json_decode($order->content, true),
                ];
                // lấy format email thanh toán từ db
                $formatEmailSendUser = LanguageLine::where('group', 'payment')->where('key', 'content_email_user')->first();
                $formatUser = $formatEmailSendUser->text;

                // lấy format email gửi về admin 
                $formatEmailSendAdmin = Config::where('name', 'content_email_admin')->first();
                $formatAdmin = $formatEmailSendAdmin->value;
                $email = Config::where('name', 'email_payment')->first()->value;

                $listProduct = '';
                foreach ($data['products'] as $product) {
                    $listProduct .= "{$product['name']} --- {$product['quantity']} --- " . number_format($product['unit_amount']['value']) . " {$product['unit_amount']['currency_code']}\n";
                }

                $replacements = [
                    '[id]' => $data['id'],
                    '[name]' => $data['name'],
                    '[email]' => $data['email'],
                    '[phone]' => $data['phone'],
                    '[address]' => $data['address'],
                    '[date]' => $data['date'],
                    '[list_product]' => nl2br(e($listProduct)),
                    '[total_price]' => number_format($data['total']) . ' ' . $data['unit'],
                    '[method_payment]' => $data['payment'],
                    '[status_payment]' => $data['status']
                ];

                //content and subject user
                $emailContentUser = strtr($formatUser, $replacements);
                $linesUser = explode("\n", strip_tags($emailContentUser));
                $subjectLineUser = isset($linesUser[0]) ? trim($linesUser[0]) : 'Order Confirmation';
                $subjectLineUser = html_entity_decode($subjectLineUser);
                //content and subject admin
                $emailContentAdmin = strtr($formatAdmin, $replacements);
                $linesAdmin = explode("\n", strip_tags($emailContentAdmin));
                $subjectLineAdmin = isset($linesAdmin[0]) ? trim($linesAdmin[0]) : 'Order Confirmation';
                $subjectLineAdmin = html_entity_decode($subjectLineAdmin);

                // gửi email cho người dùng
                $this->sendEmail($data['email'], $subjectLineUser, $emailContentUser);

                // gửi email cho admin
                $this->sendEmail($email, $subjectLineAdmin, $emailContentAdmin);
            }
            Session::put('orderId', $orderId);
            return response()->json($capturedOrder, 200);
        } catch (\Exception $e) {
            Log::error('Error capturing PayPal order: ' . $e->getMessage());
            return response()->json(['error' => 'Could not capture order. Please try again later.'], 500);
        }
    }
}
