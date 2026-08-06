<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Đánh phiên bản cho cache của phần giao diện khách.
 *
 * Trước đây các trang chủ / danh mục / chi tiết được cache 1 năm và không có
 * chỗ nào xoá, nên sửa giá hay thêm sản phẩm trong admin thì ngoài web không
 * đổi. Khoá cache lại nằm rải rác theo ngôn ngữ, theo slug và theo số trang
 * ('posts_for_category_{slug}_page_{n}'...) nên không thể liệt kê ra mà xoá
 * từng cái; driver `file` thì không hỗ trợ cache tag.
 *
 * Cách giải quyết: mọi khoá đều gắn thêm số phiên bản. Khi nội dung thay đổi
 * chỉ cần tăng phiên bản - toàn bộ khoá cũ lập tức không còn được đọc tới, và
 * tự hết hạn theo TTL (xem FontEndController::$cacheTime).
 */
final class FrontendCache
{
    private const VERSION_KEY = 'frontend_cache_version';

    /**
     * Khoá cache đã gắn phiên bản hiện hành.
     */
    public static function key(string $key): string
    {
        return $key . '.v' . self::version();
    }

    public static function version(): int
    {
        $version = Cache::get(self::VERSION_KEY);

        if ($version === null) {
            Cache::forever(self::VERSION_KEY, 1);

            return 1;
        }

        return (int) $version;
    }

    /**
     * Nội dung vừa thay đổi: sang phiên bản mới để khách thấy ngay.
     */
    public static function bump(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
