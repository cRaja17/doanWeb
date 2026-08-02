<?php

namespace App\Http\Controllers\Web;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends FontEndController
{


    /**
     * Add product to shopping cart
     */
    public function ajaxAddToCart(Request $request)
    {
        $id = $request->input('id');
        $quantity = (int) $request->input('qty', 1);
        if ($quantity < 1) {
            $quantity = 1;
        }

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found',
            ], 404);
        }

        $nameTrans = $product->getTranslations('name') ?: ['vi' => $product->name, 'en' => $product->name];
        $priceTrans = $product->getTranslations('price') ?: ['vi' => $product->price, 'en' => $product->price];
        $unitTrans = $product->getTranslations('unit') ?: ['vi' => 'đ', 'en' => '$'];

        Cart::add(
            $product->code ?: (string) $product->id,
            $product->getTranslation('name', app()->getLocale()),
            $quantity,
            0,
            0,
            [
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
        $cartItems = Cart::content();
        $total = Cart::count();
        $totalPrice = 0;
        $cartData = [];

        foreach ($cartItems as $item) {
            $nameOptions = json_decode(json_encode($item->options->name ?? []), true);
            $priceOptions = json_decode(json_encode($item->options->price ?? []), true);
            $unitOptions = json_decode(json_encode($item->options->unit ?? []), true);

            $nameVal = is_array($nameOptions) ? ($nameOptions[$locale] ?? (reset($nameOptions) ?: $item->name)) : (string) ($item->options->name ?? $item->name);
            $priceVal = is_array($priceOptions) ? (float) ($priceOptions[$locale] ?? (reset($priceOptions) ?: 0)) : (float) ($item->options->price ?? 0);
            $unitVal = is_array($unitOptions) ? ($unitOptions[$locale] ?? (reset($unitOptions) ?: '')) : (string) ($item->options->unit ?? '');

            $imageUrl = $item->options->image ?? '';
            if (str_starts_with($imageUrl, url('/')) && !str_contains($imageUrl, '/storage/')) {
                $imageUrl = str_replace(url('/'), url('/storage'), $imageUrl);
            }

            $cartData[$item->rowId] = [
                'rowId' => $item->rowId,
                'code' => $item->id,
                'name' => $nameVal,
                'price' => $priceVal,
                'formatted_price' => FormatCurrency($priceVal),
                'image' => $imageUrl,
                'url' => route('web_product_detail', $item->options->slug ?? '', true, $locale),
                'unit' => $unitVal,
                'qty' => $item->qty,
            ];
            $totalPrice += $priceVal * $item->qty;
        }

        return response()->json([
            'status' => 'success',
            'cartData' => $cartData,
            'total' => $total,
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
        $qty = (int) $request->input('qty', 1);

        if ($status == 'delete') {
            Cart::remove($rowId);
            return response()->json([
                'status' => 200,
                'message' => 'Product removed from cart',
                'cartCount' => Cart::count()
            ]);
        } elseif ($status == 'update') {
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
