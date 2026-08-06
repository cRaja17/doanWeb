<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Chuyển các dòng hàng đang nằm trong `orders.content` (JSON) sang bảng
 * `order_details`. Cột `content` được giữ lại nguyên vẹn làm bản lưu.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Map code -> id một lần, tránh query lặp cho từng dòng hàng.
        $productIdByCode = DB::table('products')
            ->whereNotNull('code')
            ->pluck('id', 'code');

        $now = now();

        DB::table('orders')
            ->select('id', 'content')
            ->whereNotNull('content')
            ->orderBy('id')
            ->chunkById(200, function ($orders) use ($productIdByCode, $now) {
                $rows = [];

                foreach ($orders as $order) {
                    $lines = json_decode((string) $order->content, true);

                    if (!is_array($lines)) {
                        continue;
                    }

                    foreach ($lines as $line) {
                        if (!is_array($line)) {
                            continue;
                        }

                        $code = $line['code'] ?? null;
                        $price = (float) ($line['unit_amount']['value'] ?? 0);
                        $quantity = (int) ($line['quantity'] ?? 1);

                        $rows[] = [
                            'order_id' => $order->id,
                            'product_id' => $code !== null ? ($productIdByCode[$code] ?? null) : null,
                            'product_code' => $code !== null ? (string) $code : null,
                            'name' => (string) ($line['name'] ?? ''),
                            'price' => $price,
                            'quantity' => max($quantity, 1),
                            'currency_code' => $line['unit_amount']['currency_code'] ?? null,
                            'subtotal' => $price * max($quantity, 1),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('order_details')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        // `content` chưa bị xoá nên chỉ cần dọn sạch bảng dẫn xuất.
        DB::table('order_details')->truncate();
    }
};
