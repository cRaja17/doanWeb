<?php

namespace App\Ai\Tools;

use App\Models\Information;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetStorePolicy implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Đọc nội dung các trang thông tin và chính sách của cửa hàng: giới thiệu công ty, '
            .'chính sách bảo hành, điều khoản dịch vụ, chính sách bảo mật, thông tin liên hệ & hỗ trợ. '
            .'Dùng khi khách hỏi về bảo hành, đổi trả, giao hàng, thanh toán, liên hệ hoặc thông tin cửa hàng. '
            .'Gọi không kèm tham số để xem danh sách các trang hiện có.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $locale = app()->getLocale();
        $topic = trim($request->string('topic', '')->value());

        $query = Information::query()->where('status', 1);

        if ($topic !== '') {
            $query->where(fn ($q) => $q->where("name->{$locale}", 'like', "%{$topic}%")
                ->orWhere('slug', 'like', "%{$topic}%"));
        }

        $pages = $query->take($topic !== '' ? 2 : 10)->get();

        if ($pages->isEmpty()) {
            return 'Không tìm thấy trang thông tin nào phù hợp. Hãy gọi lại công cụ này không kèm tham số để xem danh sách trang hiện có.';
        }

        $rows = $pages->map(function (Information $info) use ($locale, $topic) {
            $row = [
                'title' => $info->getTranslation('name', $locale, false),
                'url' => route('web.information', ['slug' => $info->slug], locale: $locale),
            ];

            // Chỉ kèm nội dung khi khách hỏi cụ thể, tránh nhồi cả 5 trang vào ngữ cảnh.
            if ($topic !== '') {
                $row['content'] = str(html_entity_decode(
                    strip_tags((string) $info->getTranslation('description', $locale, false)),
                    ENT_QUOTES | ENT_HTML5
                ))->squish()->limit(1500)->value();
            }

            return $row;
        });

        return json_encode($rows->all(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'topic' => $schema->string()
                ->description('Chủ đề cần tra cứu, ví dụ: bảo hành, bảo mật, liên hệ, điều khoản, giới thiệu. '
                    .'Bỏ trống để lấy danh sách tất cả các trang thông tin.'),
        ];
    }
}
