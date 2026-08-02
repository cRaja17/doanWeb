<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleOrders = [
            [
                'transaction_code' => 'DH20260727001',
                'name' => 'Nguyễn Văn An',
                'gender' => 'Nam',
                'email' => 'nguyenvanan@gmail.com',
                'phone' => '0988123456',
                'address' => '123 Đường Cầu Giấy, Hà Nội',
                'message' => 'Giao hàng giờ hành chính giúp tôi.',
                'method_payment' => 'COD',
                'total' => 32990000,
                'unit_payment' => 'VND',
                'order_status' => Order::ORDER_STATUS_CONTACTED,
                'status' => Order::STATUS_PAID,
                'content' => json_encode([
                    [
                        'name' => 'iPhone 15 Pro Max 256GB Gold',
                        'quantity' => 1,
                        'unit_amount' => ['value' => 32990000, 'currency_code' => 'VND']
                    ]
                ]),
            ],
            [
                'transaction_code' => 'DH20260727002',
                'name' => 'Trần Thị Bích',
                'gender' => 'Nữ',
                'email' => 'tranthibich@gmail.com',
                'phone' => '0912345678',
                'address' => '45 Lê Lợi, Quận 1, TP. Hồ Chí Minh',
                'message' => 'Gói quà giúp mình nhé.',
                'method_payment' => 'PayPal',
                'total' => 249,
                'unit_payment' => 'USD',
                'order_status' => Order::ORDER_STATUS_DELIVERED,
                'status' => Order::STATUS_PAID,
                'content' => json_encode([
                    [
                        'name' => 'Apple AirPods Pro 2 Type-C Earbuds',
                        'quantity' => 1,
                        'unit_amount' => ['value' => 249, 'currency_code' => 'USD']
                    ]
                ]),
            ],
            [
                'transaction_code' => 'DH20260727003',
                'name' => 'Phạm Minh Cường',
                'gender' => 'Nam',
                'email' => 'minhcuong@gmail.com',
                'phone' => '0977654321',
                'address' => '78 Nguyễn Văn Linh, Đà Nẵng',
                'message' => 'Gọi trước khi giao 30 phút.',
                'method_payment' => 'COD',
                'total' => 65990000,
                'unit_payment' => 'VND',
                'order_status' => Order::ORDER_STATUS_WAITING,
                'status' => Order::STATUS_PENDING,
                'content' => json_encode([
                    [
                        'name' => 'ASUS ROG Zephyrus G16 OLED RTX 4080',
                        'quantity' => 1,
                        'unit_amount' => ['value' => 65990000, 'currency_code' => 'VND']
                    ]
                ]),
            ],
        ];

        foreach ($sampleOrders as $data) {
            Order::create($data);
        }
    }
}
