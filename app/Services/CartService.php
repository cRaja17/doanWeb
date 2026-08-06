<?php

namespace App\Services;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;

/**
 * Đối chiếu giỏ hàng (lưu trong session) với dữ liệu sản phẩm hiện tại.
 *
 * Tên, ảnh, giá và tồn kho luôn đọc lại từ bảng `products`. Phần lưu trong
 * session chỉ là bản chụp lúc khách bấm "thêm vào giỏ" - dùng để phát hiện sản
 * phẩm đã đổi giá, không bao giờ dùng để tính tiền.
 */
final class CartService
{
    /**
     * Từng dòng giỏ hàng đã ghép với sản phẩm trong DB.
     *
     * @return array<int, array<string, mixed>>
     */
    public function lines(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $lines = [];

        foreach (Cart::content() as $item) {
            $product = $this->resolveProduct($item);
            $snapshot = $this->snapshotPrice($item, $locale);
            $price = $product?->priceFor($locale);

            $lines[] = [
                'rowId' => $item->rowId,
                'code' => $item->id,
                'qty' => (int) $item->qty,
                'product' => $product,
                // Sản phẩm bị xoá hoặc bị ẩn trong admin đều coi là không bán nữa.
                'missing' => $product === null || ! $product->isPurchasable(),
                'available' => $product ? $product->quantity : 0,
                'name' => $product ? $product->getTranslation('name', $locale) : $item->name,
                'image' => get_image_url($product?->image),
                'slug' => $product->slug ?? '',
                'unit' => $product ? (string) $product->getTranslation('unit', $locale) : '',
                'price' => $price,
                'snapshot_price' => $snapshot,
                'price_changed' => $price !== null && $snapshot !== null && abs($price - $snapshot) > 0.001,
            ];
        }

        return $lines;
    }

    /**
     * Giá hiện tại của một dòng theo ngôn ngữ/tiền tệ yêu cầu.
     */
    public function priceIn(array $line, string $priceLocale): ?float
    {
        $product = $line['product'] ?? null;

        return $product instanceof Product ? $product->priceFor($priceLocale) : null;
    }

    /**
     * Tổng tiền theo một loại tiền tệ. Dòng thiếu giá tính là 0 - đây chỉ dùng
     * để hiển thị, còn lúc chốt đơn thì OrderController chặn hẳn.
     */
    public function total(array $lines, string $priceLocale): float
    {
        $total = 0.0;

        foreach ($lines as $line) {
            $total += ($this->priceIn($line, $priceLocale) ?? 0) * $line['qty'];
        }

        return $total;
    }

    /**
     * Dấu vân tay của bảng giá đang hiển thị cho khách.
     *
     * Trang checkout lưu lại giá trị này, lúc submit so lại: nếu khác nghĩa là
     * giá (hoặc số lượng) đã đổi giữa chừng, và ta không thu tiền theo mức giá
     * khách chưa từng nhìn thấy.
     */
    public function priceFingerprint(array $lines): string
    {
        $parts = [];

        foreach ($lines as $line) {
            $parts[] = implode(':', [
                $line['rowId'],
                $line['qty'],
                $this->priceIn($line, 'vi') ?? '-',
                $this->priceIn($line, 'en') ?? '-',
            ]);
        }

        sort($parts);

        return md5(implode('|', $parts));
    }

    /**
     * Có dòng nào đã đổi giá so với lúc khách thêm vào giỏ hay không.
     */
    public function hasPriceChanges(array $lines): bool
    {
        foreach ($lines as $line) {
            if ($line['price_changed']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Những dòng không giao được: sản phẩm đã ngừng bán, hoặc đặt quá tồn kho.
     *
     * @return array<int, string> thông báo hiển thị cho khách
     */
    public function stockProblems(array $lines): array
    {
        $problems = [];

        foreach ($lines as $line) {
            if ($line['missing']) {
                $problems[] = translate('cart.product_unavailable', ['name' => $line['name']]);
            } elseif ($line['available'] <= 0) {
                $problems[] = translate('cart.out_of_stock', ['name' => $line['name']]);
            } elseif ($line['qty'] > $line['available']) {
                $problems[] = translate('cart.insufficient_stock', [
                    'name' => $line['name'],
                    'available' => $line['available'],
                ]);
            }
        }

        return $problems;
    }

    /**
     * Số lượng của một sản phẩm đang nằm trong giỏ, dùng để không cho khách
     * thêm vượt tồn kho qua nhiều lần bấm.
     */
    public function quantityInCart(Product $product, ?string $exceptRowId = null): int
    {
        $total = 0;

        foreach (Cart::content() as $item) {
            if ($exceptRowId !== null && $item->rowId === $exceptRowId) {
                continue;
            }

            if ($this->resolveProduct($item)?->id === $product->id) {
                $total += (int) $item->qty;
            }
        }

        return $total;
    }

    /**
     * Sản phẩm ứng với một dòng giỏ hàng.
     */
    public function resolveProduct($item): ?Product
    {
        $productId = $item->options['product_id'] ?? null;

        if ($productId) {
            return Product::find($productId);
        }

        // Giỏ hàng tạo trước khi bắt đầu lưu product_id: tra theo code rồi tới id.
        return Product::findByCodeOrId($item->id);
    }

    /**
     * Giá đã lưu trong session lúc thêm vào giỏ (chỉ để so sánh).
     */
    private function snapshotPrice($item, string $locale): ?float
    {
        $prices = $item->options['price'] ?? [];

        if (is_object($prices)) {
            $prices = (array) $prices;
        }

        if (! is_array($prices) || ! isset($prices[$locale]) || $prices[$locale] === '') {
            return null;
        }

        return (float) $prices[$locale];
    }
}
