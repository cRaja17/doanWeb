<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\Request;
use App\Support\FrontendCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryController extends FontEndController
{
    private function applyFilters($query, Request $request, $locale)
    {
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $made = $request->input('made');
        $inStock = $request->input('in_stock');
        $sortBy = $request->input('sort', 'newest');

        if (is_numeric($minPrice) || is_numeric($maxPrice)) {
            // Cùng lý do như khi sắp xếp: phải so sánh trên giá trị số, không phải chuỗi.
            $priceExpr = $this->priceSortExpression($locale);

            if (is_numeric($minPrice)) {
                $query->whereRaw("$priceExpr >= ?", [(float) $minPrice]);
            }
            if (is_numeric($maxPrice)) {
                $query->whereRaw("$priceExpr <= ?", [(float) $maxPrice]);
            }
        }

        if ($made) {
            $query->where('made->' . $locale, $made);
        }

        if ($inStock) {
            $query->where('quantity', '>', 0);
        }

        $priceSort = $this->priceSortExpression($locale);

        if ($sortBy == 'price_asc') {
            $query->orderByRaw("$priceSort asc");
        } elseif ($sortBy == 'price_desc') {
            $query->orderByRaw("$priceSort desc");
        } elseif ($sortBy == 'name_asc') {
            $query->orderBy('name->' . $locale, 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }

    private function priceSortExpression($locale)
    {
        $locale = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $locale);

        return "CAST(JSON_UNQUOTE(JSON_EXTRACT(`price`, '$.\"{$locale}\"')) AS DECIMAL(15,2))";
    }

    private function getFilterData($locale)
    {
        $categories = Cache::remember(FrontendCache::key('product_categories_' . $locale), $this->cacheTime, function () {
            return ProductCategory::where('status', 1)
                ->withCount(['products' => fn($q) => $q->where('status', 1)])
                ->get();
        });

        $origins = Cache::remember(FrontendCache::key('product_origins_' . $locale), $this->cacheTime, function () use ($locale) {
            return Product::where('status', 1)
                ->get()
                ->map(fn($p) => $p->getTranslation('made', $locale, false))
                ->filter()
                ->unique()
                ->values();
        });

        return compact('categories', 'origins');
    }

    /**
     * Category Products Page
     */
    public function index(Request $request, ProductCategory $category, $slug)
    {
        $locale = app()->getLocale();
        $category = Cache::remember(FrontendCache::key('product_category_' . $slug . '_' . $locale), $this->cacheTime, function () use ($slug) {
            return ProductCategory::where(["slug" => $slug, "status" => 1])->first();
        });

        if (!$category)
            return abort(404);

        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [6, 12, 24, 48]))
            $perPage = 12;

        $query = $category->products()->where('status', 1);
        $query = $this->applyFilters($query, $request, $locale);

        $products = $query->paginate($perPage)->withQueryString();
        extract($this->getFilterData($locale));

        $this->header = [
            'canonical' => Route('web_list_product', $category->slug),
            'robots' => $category->robots ?? 'index,follow',
            'title' => $category->name,
            'description' => $category->meta_description
                ?? translate('product_category.meta_description_default', ['name' => $category->name]),
            'keywords' => $category->meta_keywords
                ?? translate('product_category.meta_keywords_default', ['name' => $category->name]),
            'og_image' => $category->image,
            'og_url' => Route('web_list_product', $category->slug),
        ];

        return $this->render(
            'web.product_category.index',
            compact('category', 'categories', 'origins', 'products', 'perPage')
        );
    }

    /**
     * All Products Page (/products)
     */
    public function allProducts(Request $request)
    {
        $locale = app()->getLocale();
        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [6, 12, 24, 48]))
            $perPage = 12;

        $selectedCatSlug = $request->input('category', null);
        $query = Product::where('status', 1);

        if ($selectedCatSlug && $selectedCatSlug != 'all') {
            $cat = ProductCategory::where('slug', $selectedCatSlug)->first();
            if ($cat) {
                $query->where('product_category_id', $cat->id);
            }
        }

        $query = $this->applyFilters($query, $request, $locale);
        $products = $query->paginate($perPage)->withQueryString();
        extract($this->getFilterData($locale));

        $allProductsLabel = translate('product_category.all_products');
        $category = new ProductCategory();
        $category->slug = 'all';
        $category->name = $allProductsLabel;

        $this->header = [
            'canonical' => route('web_all_products'),
            'robots' => 'index,follow',
            'title' => translate('product_category.all_meta_title'),
            'description' => translate('product_category.all_meta_description'),
            'keywords' => translate('product_category.all_meta_keywords'),
            'og_image' => '',
            'og_url' => route('web_all_products'),
        ];

        return $this->render(
            'web.product_category.index',
            compact('category', 'categories', 'origins', 'products', 'perPage')
        );
    }

    public function ajaxGetProducts(Request $request)
    {
        $data = $request->all();

        // Chỉ nhận ngôn ngữ nằm trong danh sách được hỗ trợ: setLocale() với giá
        // trị tuỳ ý sẽ khiến các truy vấn bên dưới đọc khoá JSON không tồn tại.
        $locale = $data['locale'] ?? app()->getLocale();
        if (!array_key_exists($locale, (array) config('laravellocalization.supportedLocales', []))) {
            $locale = app()->getLocale();
        }
        app()->setLocale($locale);

        $minPrice = (int) ($data['filter']['minPrice'] ?? 0);
        $maxPrice = (int) ($data['filter']['maxPrice'] ?? 10000000);
        $brand = $data['filter']['brand'] ?? null;
        $sortBy = $data['filter']['sort'] ?? 'newest';
        // Thiếu `slug` trước đây là undefined key; mặc định coi như xem tất cả.
        $slug = $data['slug'] ?? 'all';

        if ($slug != 'all') {
            $category = ProductCategory::where('slug', $slug)->first();
            if ($category) {
                $query = $category->products()->where('status', 1);
            } else {
                $query = Product::where('status', 1);
            }
        } else {
            $query = Product::where('status', 1);
        }

        if ($minPrice > 0 || $maxPrice < 10000000) {
            $query->where(function ($q) use ($locale, $minPrice, $maxPrice) {
                $q->where('price->' . $locale, '>=', $minPrice)
                    ->where('price->' . $locale, '<=', $maxPrice);
            });
        }

        if ($brand) {
            $query->where('made->' . $locale, 'LIKE', '%' . $brand . '%');
        }

        if ($sortBy == 'price_asc') {
            $query->orderBy('price->' . $locale, 'asc');
        } elseif ($sortBy == 'price_desc') {
            $query->orderBy('price->' . $locale, 'desc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->get();
        $response = [];

        foreach ($products as $product) {
            $response[] = [
                'id' => $product->id,
                'name' => $product->getTranslation('name', $locale),
                'image' => get_image_url($product->image),
                'url' => route('web_product_detail', $product->slug, true, $locale),
                'price' => (float) $product->getTranslation('price', $locale),
                'formatted_price' => FormatCurrency($product->getTranslation('price', $locale)),
                'unit' => $product->getTranslation('unit', $locale),
                'made' => $product->getTranslation('made', $locale),
                'meta_description' => $product->getTranslation('summary', $locale) ?: $product->meta_description,
            ];
        }

        return response()->json($response);
    }
}
