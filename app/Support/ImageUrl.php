<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ImageUrl
{
    /**
     * Sinh URL hiển thị cho cột ảnh.
     *
     * Dữ liệu hiện lẫn hai dạng nên không thể dùng ->disk('public') cố định:
     *  - URL ngoài  : "https://picsum.photos/..."     -> giữ nguyên
     *  - Path nội bộ: "images/products/abc.webp"      -> qua disk `public`
     *  - Path đã có sẵn tiền tố "/storage/..."        -> giữ nguyên
     */
    public static function resolve(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $url = str_starts_with($path, '/storage/')
            ? asset($path)
            : Storage::disk('public')->url($path);

        return static::encodePath($url);
    }

    /**
     * Mã hoá dấu cách và ký tự đặc biệt trong phần path.
     *
     * Tên file kiểu "s-l1600 (80).webp" làm FILTER_VALIDATE_URL trả về false,
     * khiến ImageColumn tưởng là path nội bộ và đi tra trên disk -> ảnh trống.
     */
    private static function encodePath(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['path'])) {
            return $url;
        }

        $encoded = implode('/', array_map(rawurlencode(...), explode('/', $parts['path'])));

        return ($parts['scheme'] ?? 'http').'://'.($parts['host'] ?? '')
            .(isset($parts['port']) ? ':'.$parts['port'] : '')
            .$encoded;
    }
}
