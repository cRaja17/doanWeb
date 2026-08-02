<?php

namespace App\Ai\Tools;

use App\Models\ProductCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListProductCategories implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Liệt kê các danh mục sản phẩm của cửa hàng. Dùng khi khách hỏi cửa hàng bán những gì, '
            .'có những loại sản phẩm nào, hoặc khi cần biết tên danh mục trước khi tìm sản phẩm theo danh mục.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $locale = app()->getLocale();

        $categories = ProductCategory::query()
            ->where('status', 1)
            ->take(30)
            ->get()
            ->map(fn (ProductCategory $c) => [
                'name' => $c->getTranslation('name', $locale, false),
                'url' => route('web_list_product', ['slug' => $c->slug], locale: $locale),
            ]);

        if ($categories->isEmpty()) {
            return 'Hiện chưa có danh mục sản phẩm nào.';
        }

        return json_encode($categories->all(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
