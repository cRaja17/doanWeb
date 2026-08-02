<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use App\Support\SlugField;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // Dùng MỘT lưới 6 cột chung cho toàn form. Nếu mỗi hàng là một Grid
            // riêng thì các cột không thẳng hàng với nhau -> giao diện bị lệch.
            // 6 chia hết cho 2 và 3 nên bố trí được cả hàng 2 và 3 cột.
            ->columns(6)
            ->components([
                // Hàng 1: Tên - Đường dẫn tĩnh
                TextInput::make('name')->label('Tên')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(SlugField::suggestFrom('name'))
                    ->columnSpan(3),
                SlugField::make('product_categories')
                    ->columnSpan(3),

                // Hàng 2: Hình ảnh
                CuratorPicker::make('image')->label('Hình ảnh')
                    ->afterStateHydrated(function (CuratorPicker $component, $state): void {
                        if (filled($state) && ! is_numeric($state) && is_string($state)) {
                            $state = Media::where('path', $state)->value('id');
                        }

                        $component->state(
                            filled($state) ? get_media_items([$state])->toArray() : [],
                        );
                    })
                    ->dehydrateStateUsing(
                        fn ($state) => filled($state)
                            ? Media::find(collect($state)->first()['id'] ?? null)?->path
                            : null,
                    )
                    ->default(null)
                    // Hiển thị trọn ảnh (object-contain) thay vì cắt cúp,
                    // tránh viền đen hai bên khi ảnh không đúng tỉ lệ khung.
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

                // Hàng 5: Rel - Target - Trạng thái
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
                    ->columnSpan(2),

                // Hàng 6: Mô tả
                RichEditor::make('description')->label('Mô tả')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
