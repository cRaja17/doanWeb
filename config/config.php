<?php
// <!-- cấu hình mặc đinh các từ khóa  -->

return [
    'payment' => [
        // Key trùng với các hằng App\Models\Order::STATUS_* (cột `orders.status`).
        'status' => [
            'pending' => [
                'vi' => 'Thanh toán đang chờ xử lý',
                'en' => 'Payment is pending',
                'fr' => 'Le paiement est en attente',

            ],
            'paid' => [
                'vi' => 'Thanh toán thành công',
                'en' => 'Payment successful',
                'fr' => 'Paiement réussi',
            ],
            'failed' => [
                'vi' => 'Thanh toán thất bại',
                'en' => 'Payment failed',
                'fr' => 'Paiement échoué',
            ],
            'cancelled' => [
                'vi' => 'Thanh toán bị hủy',
                'en' => 'Payment is canceled',
                'fr' => 'Le paiement est annulé',
            ],
        ],
    ],
    'product' => [
        'price' => [
            'vi' => 'Giá',
            'en' => 'Price',
            'fr' => 'Prix',
        ],
        'quantity' => [
            'vi' => 'Số lượng',
            'en' => 'Quantity',
            'fr' => 'Quantité',
        ],
        'total' => [
            'vi' => 'Tổng tiền',
            'en' => 'Total',
            'fr' => 'Total',
        ],
        'unit' => [
            'vi' => 'VNĐ',
            'en' => 'USD',
            'fr' => 'EUR',
        ],
        'name' => [
            'vi' => 'Tên sản phẩm',
            'en' => 'Product name',
            'fr' => 'Nom du produit',
        ],
    ]
];
