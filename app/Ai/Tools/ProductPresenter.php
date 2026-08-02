<?php

namespace App\Ai\Tools;

use App\Models\Product;

/**
 * Chuẩn hoá dữ liệu sản phẩm thành mảng gọn cho AI đọc.
 *
 * Lưu ý: cột `price` và `unit` là JSON translatable, nhưng chúng lưu giá theo
 * đơn vị tiền tệ của từng ngôn ngữ (vi => 480000 đ, en => 22 $), chứ không phải
 * bản dịch của cùng một con số. Vì vậy luôn ghép price với unit của cùng locale.
 */
class ProductPresenter
{
    public static function toArray(Product $product, string $locale): array
    {
        $price = $product->getTranslation('price', $locale, false);
        $unit = $product->getTranslation('unit', $locale, false);

        return [
            'name' => $product->getTranslation('name', $locale, false),
            'code' => $product->code,
            'price' => self::formatPrice($price, $unit),
            'in_stock' => (int) $product->quantity > 0,
            'quantity' => (int) $product->quantity,
            'summary' => str(html_entity_decode(
                strip_tags((string) $product->getTranslation('summary', $locale, false)),
                ENT_QUOTES | ENT_HTML5
            ))->squish()->limit(200)->value(),
            'category' => $product->category?->getTranslation('name', $locale, false),
            'url' => route('web_product_detail', ['slug' => $product->slug], locale: $locale),
        ];
    }

    protected static function formatPrice(mixed $price, mixed $unit): string
    {
        $price = trim((string) $price);
        $unit = trim((string) $unit);

        if ($price === '') {
            return 'Liên hệ';
        }

        // Giá lưu dạng chuỗi số thuần, thêm dấu phân cách hàng nghìn cho dễ đọc.
        if (is_numeric($price)) {
            $price = number_format((float) $price, 0, ',', '.');
        }

        return trim($price.' '.$unit);
    }
}
