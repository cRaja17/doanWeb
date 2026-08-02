<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use App\Support\SlugField;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // Một lưới 6 cột chung để các hàng thẳng cột với nhau.
            ->columns(6)
            ->components([
                // Hàng 1: Tên - Đường dẫn tĩnh
                TextInput::make('name')->label('Tên')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(SlugField::suggestFrom('name'))
                    ->columnSpan(3),
                SlugField::make('post_categories')
                    ->columnSpan(3),

                // Hàng 2: Hình ảnh
                // Cột `image` đang chứa URL ngoài (https://...) chứ không phải
                // path trong thư viện media, nên chỉ tra cứu media khi là path
                // nội bộ; URL ngoài giữ nguyên để không mất dữ liệu khi lưu.
                CuratorPicker::make('image')->label('Hình ảnh')
                    ->afterStateHydrated(function (CuratorPicker $component, $state): void {
                        if (filled($state) && ! is_numeric($state) && is_string($state)) {
                            $state = str_starts_with($state, 'http')
                                ? null
                                : Media::where('path', $state)->value('id');
                        }

                        $component->state(
                            filled($state) ? get_media_items([$state])->toArray() : [],
                        );
                    })
                    ->dehydrateStateUsing(
                        fn ($state, $record) => filled($state)
                            ? Media::find(collect($state)->first()['id'] ?? null)?->path
                            // Không chọn ảnh mới thì giữ nguyên URL cũ.
                            : $record?->getRawOriginal('image'),
                    )
                    ->default(null)
                    ->constrained()
                    ->columnSpanFull(),

                // Hàng 3: SEO Tiêu đề - SEO Từ khóa
                Textarea::make('meta_title')->label('SEO Tiêu đề')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),
                Textarea::make('meta_keyword')->label('SEO Từ khóa')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),

                // Hàng 4: SEO Mô tả - SEO Robots
                Textarea::make('meta_description')->label('SEO Mô tả')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),
                Select::make('meta_robots')->label('SEO Robots')
                    ->options([
                        'index,follow' => 'Index,follow',
                        'noindex,nofollow' => 'Noindex,nofollow',
                        'index,nofollow' => 'Index,nofollow',
                        'noindex,follow' => 'Noindex,follow',
                    ])
                    ->default('index,follow')
                    ->native(false)
                    ->columnSpan(3),

                // Hàng 5: Rel - Target - Trạng thái - Hiện trang chủ
                Select::make('rel')->label('Rel')
                    ->options(['nofollow' => 'Nofollow', 'dofollow' => 'Dofollow'])
                    ->default('dofollow')
                    ->native(false)
                    ->columnSpan(2),
                Select::make('target')->label('Target')
                    ->options(['_blank' => '_blank (Cửa sổ mới)', '_self' => '_self (Cùng cửa sổ)'])
                    ->default('_self')
                    ->native(false)
                    ->columnSpan(2),
                Toggle::make('status')->label('Trạng thái')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(1),
                Toggle::make('view_home')->label('Hiện trang chủ')
                    ->default(false)
                    ->inline(false)
                    ->columnSpan(1),

                // Hàng 6: Mô tả
                RichEditor::make('description')->label('Mô tả')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
