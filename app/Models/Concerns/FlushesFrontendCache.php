<?php

namespace App\Models\Concerns;

use App\Support\FrontendCache;

/**
 * Nội dung của model này hiển thị ở trang khách và đang được cache, nên mỗi lần
 * lưu hoặc xoá đều phải đẩy cache sang phiên bản mới - nếu không, sửa trong
 * admin xong ngoài web vẫn là bản cũ.
 */
trait FlushesFrontendCache
{
    public static function bootFlushesFrontendCache(): void
    {
        static::saved(static fn () => FrontendCache::bump());
        static::deleted(static fn () => FrontendCache::bump());
    }
}
