<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProducts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Tìm kiếm sản phẩm đang bán trong cửa hàng theo từ khoá, mã sản phẩm hoặc danh mục. '
            .'Dùng công cụ này bất cứ khi nào khách hỏi về sản phẩm, giá, còn hàng hay không, '
            .'hoặc muốn được gợi ý sản phẩm. Trả về tên, giá, tình trạng kho và đường dẫn sản phẩm.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $locale = app()->getLocale();
        $keyword = trim($request->string('keyword', '')->value());
        $limit = min($request->integer('limit', 5) ?: 5, 10);

        $query = Product::query()->where('status', 1);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword, $locale) {
                // Cột name/summary là JSON translatable, tìm trong bản dịch của locale hiện tại.
                $q->where("name->{$locale}", 'like', "%{$keyword}%")
                    ->orWhere("summary->{$locale}", 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('model', 'like', "%{$keyword}%");
            });
        }

        if ($category = trim($request->string('category', '')->value())) {
            $query->whereHas('category', function ($q) use ($category, $locale) {
                $q->where("name->{$locale}", 'like', "%{$category}%")
                    ->orWhere('slug', 'like', "%{$category}%");
            });
        }

        $products = $query->with('category')->take($limit)->get();

        if ($products->isEmpty()) {
            return 'Không tìm thấy sản phẩm nào phù hợp với yêu cầu này trong cửa hàng.';
        }

        return $products->map(fn (Product $p) => ProductPresenter::toArray($p, $locale))
            ->pipe(fn ($rows) => json_encode($rows->all(), JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()
                ->description('Từ khoá tìm kiếm, ví dụ tên sản phẩm hoặc mã sản phẩm.'),
            'category' => $schema->string()
                ->description('Tên hoặc slug danh mục sản phẩm, nếu khách muốn lọc theo danh mục.'),
            'limit' => $schema->integer()
                ->description('Số sản phẩm tối đa cần trả về, mặc định 5, tối đa 10.'),
        ];
    }
}
