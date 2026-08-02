<?php

namespace App\Ai\Tools;

use App\Models\Post;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchPosts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Tìm bài viết, tin tức, hướng dẫn hoặc bài blog trên website. '
            .'Dùng khi khách hỏi về nội dung tư vấn, mẹo sử dụng, tin tức, hoặc muốn đọc thêm bài viết.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $locale = app()->getLocale();
        $keyword = trim($request->string('keyword', '')->value());
        $limit = min($request->integer('limit', 5) ?: 5, 10);

        $query = Post::query()->where('status', 1);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword, $locale) {
                $q->where("name->{$locale}", 'like', "%{$keyword}%")
                    ->orWhere("summary->{$locale}", 'like', "%{$keyword}%");
            });
        }

        $posts = $query->latest('id')->take($limit)->get();

        if ($posts->isEmpty()) {
            return 'Không tìm thấy bài viết nào phù hợp.';
        }

        $rows = $posts->map(fn (Post $p) => [
            'title' => $p->getTranslation('name', $locale, false),
            'summary' => str(strip_tags((string) $p->getTranslation('summary', $locale, false)))
                ->limit(200)
                ->value(),
            'url' => route('web_post_detail', ['slug' => $p->slug], locale: $locale),
        ]);

        return json_encode($rows->all(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()
                ->description('Từ khoá cần tìm trong tiêu đề hoặc tóm tắt bài viết.'),
            'limit' => $schema->integer()
                ->description('Số bài viết tối đa cần trả về, mặc định 5, tối đa 10.'),
        ];
    }
}
