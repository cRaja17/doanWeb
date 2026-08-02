<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\LanguageLine;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FontEndController extends Controller
{
    public $cacheTime;

    public $lang;

    public function __construct()
    {
        $this->cacheTime = now()->addYears(1);
        $this->layout = 'web.layouts.index';
    }

    public function sendEmailOrder($order, $locale, $method_payment)
    {
        try {
            $formatEmailSendUser = $method_payment == true ? LanguageLine::where('group', 'payment')->where('key', 'content_email_user')->first() : LanguageLine::where('group', 'payment')->where('key', 'content_email_user_nonpayment')->first();
            $formatUser = $formatEmailSendUser->text;


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

            // lấy format email gửi về admin 
            $formatEmailSendAdmin = Config::where('name', 'content_email_admin')->first();
            $formatAdmin = $formatEmailSendAdmin->value;
            $email = Config::where('name', 'email_payment')->first()->value;

            $replacements = [
                '[id]' => $order['transaction_code'],
                '[name]' => $order['name'],
                '[email]' => $order['email'],
                '[phone]' => $order['phone'],
                '[address]' => $order['address'],
                '[date]' => $order['created_at'],
                '[list_product]' => $listProduct,
                '[total_price]' => number_format($order['total']) . ' ' . $order['unit_payment'],
                '[method_payment]' => $order['method_payment'],
                '[status_payment]' => $order['status']
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
            $this->sendEmail($order['email'], $subjectLineUser, $emailContentUser);

            // gửi email cho admin
            $this->sendEmail($email, $subjectLineAdmin, $emailContentAdmin);
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
        }
    }
}
