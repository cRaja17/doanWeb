<?php

namespace App\Models;

use App\Models\Concerns\FlushesFrontendCache;
use App\Models\Concerns\PreventsDeletion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use FlushesFrontendCache, HasFactory, HasTranslations, PreventsDeletion;

    /**
     * Sản phẩm có thể đã nằm trong đơn hàng cũ; xoá đi thì `order_details`
     * mất liên kết (product_id thành null) và trang quản lý đơn không còn xem
     * được ảnh/thông tin sản phẩm. Ngừng bán thì đặt Trạng thái = ẩn.
     */
    public function deletionBlockedReason(): string
    {
        return 'Sản phẩm không được phép xoá. Đặt Trạng thái sang "Ẩn" để ngừng bán.';
    }

    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'product_category_id',
        'name',
        'code',
        'model',
        'image',
        'multiple_image',
        'summary',
        'description',
        'quantity',
        'price',
        'slug',
        'tag',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_robots',
        'rel',
        'target',
        'made',
        'unit',
        'link_afiliate',
        'status',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'summary',
        'description',
        'price',
        'unit',
        'made',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Nếu sản phẩm chỉ có một danh mục:
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Giỏ hàng và đơn hàng lưu `code` của sản phẩm, nhưng rơi về `id` khi sản
     * phẩm chưa có code (xem CartController), nên phải thử cả hai.
     */
    public static function findByCodeOrId($code): ?self
    {
        if ($code === null || $code === '') {
            return null;
        }

        $product = static::query()->where('code', $code)->first();

        if (! $product && ctype_digit((string) $code)) {
            $product = static::query()->whereKey($code)->first();
        }

        return $product;
    }

    /**
     * Giá theo ngôn ngữ, hoặc null khi sản phẩm chưa có giá cho ngôn ngữ đó.
     *
     * Cố ý không rơi về ngôn ngữ khác vì mỗi ngôn ngữ ứng với một loại tiền:
     * 250.000 VND và 250.000 USD là hai con số rất khác nhau.
     */
    public function priceFor(string $locale): ?float
    {
        $prices = $this->getTranslations('price');

        // Sản phẩm cũ có thể lưu giá dạng số thuần thay vì JSON đa ngôn ngữ.
        if ($prices === []) {
            $raw = $this->getAttributes()['price'] ?? null;

            return ($raw === null || $raw === '') ? null : (float) $raw;
        }

        $price = $prices[$locale] ?? null;

        return ($price === null || $price === '') ? null : (float) $price;
    }

    /**
     * Sản phẩm còn được bán hay không (chưa bị ẩn trong admin).
     */
    public function isPurchasable(): bool
    {
        return (int) $this->status === 1;
    }
}
