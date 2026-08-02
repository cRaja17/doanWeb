<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use App\Models\ProductCategory;
use App\Support\SlugField;

class ProductForm
{
    /**
     * Bỏ tiền tố "/storage/" để khớp với cột `path` trong bảng media,
     * vì `multiple_image` lưu path dạng "/storage/images/...".
     */
    protected static function normalizePath(string $path): string
    {
        return ltrim(str_replace('/storage/', '', $path), '/');
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            // Một lưới 6 cột chung cho cả form để các hàng thẳng cột với nhau.
            // 6 chia hết cho 2 và 3 nên bố trí được cả hàng 2 và 3 ô.
            ->columns(6)
            ->components([
                // Hàng 1: Tên - Đường dẫn tĩnh
                TextInput::make('name')->label('Tên')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(SlugField::suggestFrom('name'))
                    ->columnSpan(3),
                SlugField::make('products')
                    ->columnSpan(3),

                // Hàng 2: Mã - Danh mục
                TextInput::make('code')->label('Mã')
                    ->default(null)
                    ->columnSpan(3),
                // Chọn theo TÊN danh mục thay vì nhập id thủ công.
                Select::make('product_category_id')->label('Danh mục')
                    ->options(fn (): array => ProductCategory::query()
                        ->get()
                        ->mapWithKeys(fn (ProductCategory $c): array => [$c->id => $c->name])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->columnSpan(3),

                // Hàng 3: Hình ảnh - Nhiều hình ảnh
                CuratorPicker::make('image')->label('Hình ảnh')
                    ->afterStateHydrated(function (CuratorPicker $component, $state): void {
                        if (filled($state) && !is_numeric($state) && is_string($state)) {
                            $state = Media::where('path', static::normalizePath($state))->value('id');
                        }

                        $component->state(
                            filled($state) ? get_media_items([$state])->toArray() : [],
                        );
                    })
                    ->dehydrateStateUsing(
                        fn($state) => filled($state)
                        ? Media::find(collect($state)->first()['id'] ?? null)?->path
                        : null,
                    )
                    ->default(null)
                    ->constrained()
                    ->columnSpan(3),

                // `multiple_image` lưu JSON mảng path, có tiền tố "/storage/".
                CuratorPicker::make('multiple_image')->label('Nhiều hình ảnh')
                    ->multiple()
                    ->afterStateHydrated(function (CuratorPicker $component, $state): void {
                        if (is_string($state)) {
                            $state = json_decode($state, true) ?: [];
                        }

                        $ids = collect($state)
                            ->filter(fn($item): bool => filled($item))
                            ->map(fn($item) => is_numeric($item)
                                ? (int) $item
                                : Media::where('path', static::normalizePath((string) $item))->value('id'))
                            ->filter()
                            ->all();

                        $component->state(
                            $ids === [] ? [] : get_media_items($ids)->toArray(),
                        );
                    })
                    // Giữ nguyên tiền tố "/storage/" như dữ liệu cũ: view frontend
                    // dùng asset($image) trực tiếp nên bỏ tiền tố sẽ vỡ ảnh.
                    ->dehydrateStateUsing(
                        fn($state) => collect($state)
                            ->pluck('path')
                            ->filter()
                            ->map(fn(string $path): string => '/storage/' . static::normalizePath($path))
                            ->values()
                            ->all(),
                    )
                    ->default(null)
                    ->columnSpan(3),

                // Hàng 4: Mẫu - Xuất xứ - Link Affiliate
                TextInput::make('model')->label('Mẫu')
                    ->default(null)
                    ->columnSpan(2),
                TextInput::make('made')->label('Xuất xứ')
                    ->default(null)
                    ->columnSpan(2),
                TextInput::make('link_affiliate')->label('Link Affiliate')
                    ->url()
                    ->default(null)
                    ->columnSpan(2),

                // Hàng 5: Số lượng* - Giá* - Đơn vị
                TextInput::make('quantity')->label('Số lượng')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->columnSpan(2),
                TextInput::make('price')->label('Giá')
                    ->required()
                    ->default('0')
                    ->columnSpan(2),
                Select::make('unit')->label('Đơn vị')
                    ->options(['đ' => 'đ (VNĐ)', '$' => '$ (USD)'])
                    ->default('đ')
                    ->native(false)
                    ->columnSpan(2),

                // Hàng 6: Từ khóa (Tag) - Trạng thái
                TextInput::make('tag')->label('Từ khóa (Tag)')
                    ->default(null)
                    ->columnSpan(3),
                Toggle::make('status')->label('Trạng thái')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(3),

                // Hàng 7: SEO Tiêu đề - SEO Từ khóa
                Textarea::make('meta_title')->label('SEO Tiêu đề')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),
                Textarea::make('meta_keyword')->label('SEO Từ khóa')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),

                // Hàng 8: SEO Mô tả - SEO Robots
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

                // Hàng 9: Rel - Target
                Select::make('rel')->label('Rel')
                    ->options(['nofollow' => 'Nofollow', 'dofollow' => 'Dofollow'])
                    ->default('dofollow')
                    ->native(false)
                    ->columnSpan(3),
                Select::make('target')->label('Target')
                    ->options(['_blank' => '_blank (Cửa sổ mới)', '_self' => '_self (Cùng cửa sổ)'])
                    ->default('_self')
                    ->native(false)
                    ->columnSpan(3),

                // Hàng 10 & 11: Tóm tắt, Mô tả
                RichEditor::make('summary')->label('Tóm tắt')
                    ->default(null)
                    ->columnSpanFull(),
                RichEditor::make('description')->label('Mô tả')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
