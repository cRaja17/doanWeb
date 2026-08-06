<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use App\Services\CartService;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends FontEndController
{
    public function __construct(private readonly CartService $cart)
    {
        parent::__construct();
    }

    /**
     * Add product to shopping cart
     */
    public function ajaxAddToCart(Request $request)
    {
        $quantity = max((int) $request->input('qty', 1), 1);
        $product = Product::find($request->input('id'));

        if (!$product || !$product->isPurchasable()) {
            return response()->json([
                'status' => 404,
                'message' => translate('cart.product_not_found'),
            ], 404);
        }

        // Tính cả phần đã nằm trong giỏ: bấm "thêm" nhiều lần không được vượt kho.
        $remaining = $product->quantity - $this->cart->quantityInCart($product);

        if ($remaining <= 0 || $quantity > $remaining) {
            return response()->json([
                'status' => 422,
                'message' => $remaining <= 0
                    ? translate('cart.out_of_stock', ['name' => $product->getTranslation('name', app()->getLocale())])
                    : translate('cart.insufficient_stock', [
                        'name' => $product->getTranslation('name', app()->getLocale()),
                        'available' => $remaining,
                    ]),
            ], 422);
        }

        // Fallback khi sản phẩm chưa có bản dịch: phải phủ đủ locale được hỗ trợ,
        // nếu không translate() sẽ rơi về reset() và trả sai ngôn ngữ.
        $nameTrans = $product->getTranslations('name') ?: ['vi' => $product->name, 'en' => $product->name, 'fr' => $product->name];
        $priceTrans = $product->getTranslations('price') ?: ['vi' => $product->price, 'en' => $product->price, 'fr' => $product->price];
        $unitTrans = $product->getTranslations('unit') ?: ['vi' => 'đ', 'en' => '$', 'fr' => 'đ'];

        Cart::add(
            $product->code ?: (string) $product->id,
            $product->getTranslation('name', app()->getLocale()),
            $quantity,
            0,
            0,
            [
                // Khoá chắc chắn để về sau tra lại sản phẩm không phụ thuộc vào
                // `code` (có thể bị admin sửa sau khi khách đã thêm vào giỏ).
                'product_id' => $product->id,
                'name' => $nameTrans,
                'price' => $priceTrans,
                'image' => get_image_url($product->image),
                'slug' => $product->slug,
                'unit' => $unitTrans,
            ]
        );

        return response()->json([
            'status' => 200,
            'message' => 'Product added to cart',
            'cartCount' => Cart::count()
        ]);
    }

    /**
     * Fetch JSON cart items for AJAX widgets
     */
    public function ajaxGetCartData(Request $request)
    {
        $locale = $request->input('locale') ?: app()->getLocale();
        $cartData = [];
        $totalPrice = 0;

        // Đọc lại từ DB: giá và tên hiện trên widget giỏ hàng phải khớp với giá
        // khách sẽ thực sự phải trả ở trang thanh toán.
        foreach ($this->cart->lines($locale) as $line) {
            $price = $line['price'] ?? 0;

            $cartData[$line['rowId']] = [
                'rowId' => $line['rowId'],
                'code' => $line['code'],
                'name' => $line['name'],
                'price' => $price,
                'formatted_price' => FormatCurrency($price),
                'image' => $line['image'],
                'url' => route('web_product_detail', $line['slug'], true, $locale),
                'unit' => $line['unit'],
                'qty' => $line['qty'],
                'available' => $line['available'],
                'unavailable' => $line['missing'],
            ];

            $totalPrice += $price * $line['qty'];
        }

        return response()->json([
            'status' => 'success',
            'cartData' => $cartData,
            'total' => Cart::count(),
            'totalPrice' => $totalPrice,
            'formatted_total_price' => FormatCurrency($totalPrice),
        ]);
    }

    /**
     * Update quantity or remove item from cart
     */
    public function ajaxUpdateCart(Request $request)
    {
        $status = $request->input('status');
        $rowId = $request->input('rowId');
        $qty = max((int) $request->input('qty', 1), 1);

        if ($status == 'delete') {
            Cart::remove($rowId);

            return response()->json([
                'status' => 200,
                'message' => 'Product removed from cart',
                'cartCount' => Cart::count()
            ]);
        }

        if ($status == 'update') {
            $item = Cart::content()->get($rowId);

            if (!$item) {
                return response()->json(['status' => 404, 'message' => translate('cart.product_not_found')], 404);
            }

            $product = $this->cart->resolveProduct($item);

            if (!$product || !$product->isPurchasable()) {
                return response()->json([
                    'status' => 404,
                    'message' => translate('cart.product_not_found'),
                ], 404);
            }

            // Tồn kho còn lại cho dòng này = kho trừ đi các dòng khác cùng sản phẩm.
            $remaining = $product->quantity - $this->cart->quantityInCart($product, $rowId);

            if ($qty > $remaining) {
                return response()->json([
                    'status' => 422,
                    'message' => $remaining <= 0
                        ? translate('cart.out_of_stock', ['name' => $product->getTranslation('name', app()->getLocale())])
                        : translate('cart.insufficient_stock', [
                            'name' => $product->getTranslation('name', app()->getLocale()),
                            'available' => $remaining,
                        ]),
                    'maxQty' => max($remaining, 0),
                ], 422);
            }

            Cart::update($rowId, $qty);

            return response()->json([
                'status' => 200,
                'message' => 'Cart updated',
                'cartCount' => Cart::count()
            ]);
        }

        return response()->json(['status' => 400, 'message' => 'Invalid action']);
    }
}
