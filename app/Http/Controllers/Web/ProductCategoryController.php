<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use Http\Discovery\Exception\NotFoundException;
use Illuminate\Http\Request;
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

        // Giá lưu trong cột JSON dưới dạng chuỗi, nên phải CAST sang số:
        // nếu không MySQL so sánh theo ký tự và "1250000" sẽ đứng trước "240000".
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

    /**
     * Biểu thức SQL đọc giá theo ngôn ngữ và ép về số để so sánh/sắp xếp đúng.
     */
    private function priceSortExpression($locale)
    {
        // $locale đến từ danh sách ngôn ngữ được hỗ trợ, nhưng vẫn lọc lại cho chắc
        // vì giá trị này được nối thẳng vào câu SQL.
        $locale = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $locale);

        return "CAST(JSON_UNQUOTE(JSON_EXTRACT(`price`, '$.\"{$locale}\"')) AS DECIMAL(15,2))";
    }

    private function getFilterData($locale)
    {
        $categories = Cache::remember('product_categories_' . $locale, $this->cacheTime, function () {
            // withCount tránh N+1: sidebar cần số sản phẩm của từng danh mục.
            return ProductCategory::where('status', 1)
                ->withCount(['products' => fn($q) => $q->where('status', 1)])
                ->get();
        });

        $origins = Cache::remember('product_origins_' . $locale, $this->cacheTime, function () use ($locale) {
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
        $category = Cache::remember('product_category_' . $slug . '_' . $locale, $this->cacheTime, function () use ($slug) {
            return ProductCategory::where(["slug" => $slug, "status" => 1])->first();
        });

        if (!$category) return abort(404);

        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [6, 12, 24, 48])) $perPage = 12;

        $query = $category->products()->where('status', 1);
        $query = $this->applyFilters($query, $request, $locale);
        
        $products = $query->paginate($perPage)->withQueryString();
        extract($this->getFilterData($locale));

        $this->header = [
            'canonical' => Route('web_list_product', $category->slug),
            'robots' => $category->robots ?? 'index,follow',
            'title' => $category->name,
            'description' => $category->meta_description ?? "Thực phẩm chức năng cao cấp " . $category->name,
            'keywords' => $category->meta_keywords ?? "thực phẩm chức năng, bổ sung sức khỏe, " . $category->name,
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
        if (!in_array($perPage, [6, 12, 24, 48])) $perPage = 12;

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

        // Synthetic category model for "All Products"
        $category = new ProductCategory();
        $category->slug = 'all';
        $category->name = ['vi' => 'Tất Cả Sản Phẩm', 'en' => 'All Products'];

        $this->header = [
            'canonical' => route('web_all_products'),
            'robots' => 'index,follow',
            'title' => 'Tất Cả Sản Phẩm - HealthNutri',
            'description' => 'Khám phá tất cả các sản phẩm bổ sung sức khỏe, vitamin, whey protein và thảo dược detox nhập khẩu chính hãng.',
            'keywords' => 'tất cả sản phẩm, thực phẩm chức năng, vitamin, whey, collagen, detox',
            'og_image' => '',
            'og_url' => route('web_all_products'),
        ];

        return $this->render(
            'web.product_category.index',
            compact('category', 'categories', 'origins', 'products', 'perPage')
        );
    }

    /**
     * AJAX filter Handler for PLP
     */
    public function ajaxGetProducts(Request $request)
    {
        $data = $request->all();
        $locale = $data['locale'] ?? app()->getLocale();
        app()->setLocale($locale);

        $minPrice = (int) ($data['filter']['minPrice'] ?? 0);
        $maxPrice = (int) ($data['filter']['maxPrice'] ?? 10000000);
        $brand = $data['filter']['brand'] ?? null;
        $sortBy = $data['filter']['sort'] ?? 'newest';

        if ($data['slug'] != 'all') {
            $category = ProductCategory::where('slug', $data['slug'])->first();
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
