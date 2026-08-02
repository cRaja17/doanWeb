<?php

namespace App\Http\Controllers\Web;

use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use App\Entities\Paypal;

class OrderController extends FontEndController
{

    /**
     * @Description : 
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */

    public function checkout()
    {
        $status_payment = Config::where('name', '=', 'status_payment')->first();
        if (Cart::content()->isEmpty()) {
            return abort(404);
        }
        $cartItems = Cart::Content();
        $totalVND = 0;
        $totalUSD = 0;
        foreach ($cartItems as $cart) {
            $totalVND += $cart->options->price['vi'] * $cart->qty;
            $totalUSD += $cart->options->price['en'] * $cart->qty;
        }
        return $this->render(
            'web.order.checkout',
            compact(
                'status_payment',
                'cartItems',
                'totalVND',
                'totalUSD'
            )
        );
    }


    /**
     * @Description : 
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */

    public function thanks()
    {
        $locale = app()->getLocale();
        // get session 'orderId'
        $orderId = session('orderId');
        // $orderId = "DH2024053011575593";
        $method_payment = session('method_payment');
        $order = Order::where('transaction_code', $orderId)->first();
        if ($order == null) {
            return abort(404);
        }

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

        $listProduct = "<table><thead><tr>" .
            "<th>" . config('config.product.name.' . $locale) . "</th>" .
            "<th width='150'>" . config('config.product.quantity.' . $locale) . "</th>" .
            "<th width='150'>" . config('config.product.price.' . $locale) . "</th>" .
            "<th width='150'>" . config('config.product.total.' . $locale) . "</th></tr>
        </thead> <tbody>";
        foreach (json_decode($order->content, true) as $product) {
            $listProduct .= '<tr>';
            $listProduct .= "<td>{$product['name']}</td>";
            $listProduct .= "<td>" . $product['quantity'] . "</td>";
            $listProduct .= "<td>" . number_format($product['unit_amount']['value']) . " " . $product['unit_amount']['currency_code'] . "</td>";
            $listProduct .= "<td>" . number_format($product['quantity'] * $product['unit_amount']['value']) . " " . $product['unit_amount']['currency_code'] . "</td>";
            $listProduct .= '</tr>';
        }
        $listProduct .= '</tbody></table>';

        $replacements = [
            '[id]' => $data['id'],
            '[name]' => $data['name'],
            '[email]' => $data['email'],
            '[phone]' => $data['phone'],
            '[address]' => $data['address'],
            '[date]' => $data['date'],
            '[list_product]' => $listProduct,
            '[total_price]' => number_format($data['total']) . ' ' . $data['unit'],
            '[method_payment]' => $data['payment'],
            '[status_payment]' => $data['status']
        ];
        $format = $method_payment == false ? LanguageLine::where('group', 'payment')->where('key', 'content_thanks_you_nonpayment')->first() : LanguageLine::where('group', 'payment')->where('key', 'content_thanks_you')->first();
        $format = $format->text;
        $data = strtr($format, $replacements);
        $data = html_entity_decode($data);
        session()->forget('orderId');

        // gửi email 
        $this->sendEmailOrder($order, $locale, $method_payment);
        // xóa cart
        Cart::destroy();
        // get order by id


        return $this->render('web.order.thanks', [
            'data' => $data,
        ]);
    }

    /**
     * @Description : handle checkout
     *
     * @throws 	: NotFoundException
     * @param 	: int data
     * @return 	: array
     * @Author 	: DRaja
     */


    public function handle_checkout(Request $request)
    {
        $locale = app()->getLocale();

        $dataReq = $request->only([
            'firstName',
            'lastName',
            'country',
            'state',
            'city',
            'zipCode',
            'address',
            'email',
            'phone_full',
            'description',
            'method_payment'
        ]);

        $cartContent = Cart::content();
        $totalPrice = 0;
        $data = [];
        $currency_code = 'USD';
        $method_payment = $request->input('method_payment');

        foreach ($cartContent as $item) {
            if ($method_payment == 'paypal') {
                $price = $item->options['price']['en'];
                $currency_code = 'USD';
            } elseif ($method_payment == 'bank') {
                $price = $item->options['price']['vi'];
                $currency_code = 'VND';
            } else {
                $price = $item->options['price'][$locale];
                $currency_code = $item->options['unit'][$locale];
            }

            $totalPrice += $price * $item->qty;

            $data[] = [
                'code' => $item->id,
                'name' => $item->options['name'][$locale],
                'unit_amount' => [
                    'currency_code' => $currency_code,
                    'value' => $price,
                ],
                'quantity' => $item->qty,
            ];
        }

        $order = new Order();
        $order->name = $dataReq['firstName'] . ' ' . $dataReq['lastName'];
        $order->email = $dataReq['email'];
        $order->phone = $dataReq['phone_full'];
        $order->address = $dataReq['address'] . ', ' . $dataReq['city']  . ', ' . $dataReq['state'] . ', ' . $dataReq['country'] . ' ' . $dataReq['zipCode'];
        $order->message = $dataReq['description'] ?? '';
        $order->method_payment = $dataReq['method_payment'];
        $order->content = json_encode($data);
        $order->total = $totalPrice;
        $order->unit_payment = $currency_code;
        $order->ip = $request->ip();
        $order->proxy = $request->server('HTTP_X_FORWARDED_FOR');
        $order->status = Order::STATUS_PENDING;
        $order->order_status = Order::ORDER_STATUS_WAITING;
        $order->save();

        $transaction_code = Order::genCode($order->id);
        $order->transaction_code = $transaction_code;
        $order->save();


        if ($method_payment == 'paypal') {
            $response = new Paypal();
            $response = $response->payment($order);
            if ($response['errorCode'] == 0) {
                return redirect($response['url']);
            } else {
                return redirect()->back()->with('error', 'Payment failed: ' . $response['message']);
            }
        } else if ($method_payment == 'bank') {
            // Redirect to thanks page
            //create session orderId
            session(['orderId' => $order->transaction_code]);
            session(['method_payment' => true]);

            return redirect(route('payment.web.bank', [], true, $locale));
        } else if ($method_payment == "none") {
            //create session orderId
            session(['orderId' => $order->transaction_code]);

            session(['method_payment' => false]);
            return redirect(route('payment.web.thanks', [], true, $locale));
        } else {
            return redirect()->back()->with('error', 'Payment method not found');
        }
    }
}
