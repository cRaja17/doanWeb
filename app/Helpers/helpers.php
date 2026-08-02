<?php



// lấy cấu hình hệ thống
if (!function_exists('setting')) {
    function setting($key)
    {
        $key = Config::get($key);
        if ($key->first()) {
            return $key->first()->value;
        }
        return null;
    }
}

// Hàm lấy URL hình ảnh an toàn, tự động thêm storage/ nếu thiếu
if (!function_exists('get_image_url')) {
    function get_image_url($path, $default = '/images/products/omega3_fish_oil.png') {
        if (empty($path)) {
            return asset($default);
        }
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            $appUrl = rtrim(url('/'), '/');
            if (\Illuminate\Support\Str::startsWith($path, $appUrl)) {
                $path = str_replace($appUrl, '', $path);
            } else {
                return $path;
            }
        }
        
        $cleanPath = ltrim($path, '/');
        if (\Illuminate\Support\Str::startsWith($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }
        
        return asset('storage/' . $cleanPath);
    }
}

if (!function_exists('FormatCurrency')) {
    function FormatCurrency($amount, $currency = null)
    {
        $amount = (float) $amount;

        if ($currency) {
            $currency = strtoupper(trim($currency));
            if ($currency === 'USD' || $currency === '$') {
                return '$' . number_format($amount, 2, '.', ',');
            } elseif ($currency === 'VND' || $currency === 'VNĐ' || $currency === 'Đ') {
                return number_format($amount, 0, ',', '.') . ' VND';
            }
            return number_format($amount, 0, ',', '.') . ' ' . $currency;
        }

        $locale = app()->getLocale() ?: 'vi';

        if ($locale === 'en') {
            return '$' . number_format($amount, 2, '.', ',');
        } else {
            return number_format($amount, 0, ',', '.') . ' VND';
        }
    }
}

// Lấy ngôn ngữ. Có thể thay đổi trong langauge_lines table
if (!function_exists('translate')) {
    function translate($textOrKey, $replace = [])
    {
        if (empty($textOrKey)) {
            return '';
        }

        $locale = app()->getLocale() ?: 'vi';

        if (is_array($textOrKey)) {
            return $textOrKey[$locale] ?? (reset($textOrKey) ?: '');
        }

        if (is_string($textOrKey)) {
            $decoded = json_decode($textOrKey, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded[$locale] ?? (reset($decoded) ?: $textOrKey);
            }
        }

        $parts = explode('.', $textOrKey, 2);
        if (count($parts) == 2) {
            static $dbLines = null;
            if ($dbLines === null) {
                $dbLines = [];
                try {
                    $lines = \App\Models\LanguageLine::all();
                    foreach ($lines as $line) {
                        $textArray = is_string($line->text) ? json_decode($line->text, true) : $line->text;
                        $dbLines[$line->group . '.' . $line->key] = $textArray;
                    }
                } catch (\Exception $e) {
                    // Fail gracefully if DB is not available
                }
            }

            if (isset($dbLines[$textOrKey]) && is_array($dbLines[$textOrKey]) && !empty($dbLines[$textOrKey][$locale])) {
                $text = $dbLines[$textOrKey][$locale];
                foreach ($replace as $k => $v) {
                    $text = str_replace(':' . $k, $v, $text);
                }
                return $text;
            }
        }

        return trans($textOrKey, $replace);
    }
}