<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class SlugField
{
    /**
     * Ô "Đường dẫn tĩnh" dùng chung cho các resource.
     *
     * - Bắt buộc nhập, không trùng với bản ghi khác (bỏ qua chính nó khi sửa).
     * - Chỉ cho phép chữ thường, số và dấu gạch ngang.
     *
     * Cố ý KHÔNG tự chuẩn hoá nội dung người dùng đã gõ: làm vậy sẽ âm thầm
     * biến "Sai Slug!@#" thành "sai-slug-at" và lưu luôn thay vì báo lỗi.
     * Muốn gợi ý slug thì dùng suggestFrom().
     */
    public static function make(string $table, string $label = 'Đường dẫn tĩnh'): TextInput
    {
        return TextInput::make('slug')
            ->label($label)
            ->required()
            ->maxLength(255)
            ->unique(table: $table, column: 'slug', ignoreRecord: true)
            ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
            ->validationMessages([
                'unique' => 'Đường dẫn tĩnh này đã được sử dụng, vui lòng chọn đường dẫn khác.',
                'required' => 'Vui lòng nhập đường dẫn tĩnh.',
                'regex' => 'Đường dẫn tĩnh chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            ])
            ->helperText('Chỉ gồm chữ thường không dấu, số và dấu gạch ngang. Ví dụ: tin-tuc-moi');
    }

    /**
     * Tự điền slug từ ô `$sourceField` khi ô slug đang để trống.
     *
     * Chỉ điền khi trống nên không ghi đè giá trị người dùng tự nhập.
     */
    public static function suggestFrom(string $sourceField): \Closure
    {
        return function (?string $state, callable $set, callable $get) use ($sourceField): void {
            if (blank($get('slug')) && filled($state)) {
                $set('slug', Str::slug($state));
            }
        };
    }
}
