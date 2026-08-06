<?php

namespace App\Models;

use App\Exceptions\DeletionNotAllowedException;
use App\Models\Concerns\FlushesFrontendCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\Cache;


class ProductCategory extends Model
{
    use FlushesFrontendCache, HasFactory;
    use HasTranslations;

    protected $table = 'product_categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'lang',
        'name',
        'image',
        'slug',
        'description',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'meta_robots',
        'rel',
        'target',
        'status',
    ];

    public $translatable = [
        'name',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'description',
    ];


    /**
     * `products.product_category_id` khai báo onDelete('cascade'), nên xoá một
     * danh mục là MySQL tự xoá sạch sản phẩm bên trong ở tầng CSDL - không đi
     * qua model nên chốt chặn của Product cũng không cứu được. Vì vậy chặn ngay
     * từ đây: muốn xoá danh mục thì phải chuyển hết sản phẩm sang danh mục khác.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $category): void {
            $count = $category->products()->count();

            if ($count > 0) {
                throw new DeletionNotAllowedException(
                    "Danh mục \"{$category->getTranslation('name', 'vi')}\" còn {$count} sản phẩm nên không xoá được. "
                    . 'Hãy chuyển các sản phẩm sang danh mục khác trước.'
                );
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }


    public static function getCategories()
    {
        return Cache::rememberForever('product_categories', function () {
            return self::where('status', 1)->get();
        });
    }
}
