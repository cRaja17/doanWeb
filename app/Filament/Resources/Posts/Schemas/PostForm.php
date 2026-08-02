<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Models\Media;
use App\Models\User;
use App\Models\PostCategory;
use App\Support\SlugField;

class PostForm
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
                SlugField::make('posts')
                    ->columnSpan(3),

                // Hàng 2: Danh mục - Người đăng
                // Một bài viết thuộc nhiều danh mục (quan hệ nhiều-nhiều qua
                // bảng post_category_relation) nên dùng multiple().
                // relationship() để Filament tự sync pivot khi lưu.
                Select::make('categories')->label('Danh mục')
                    ->relationship('categories')
                    ->getOptionLabelFromRecordUsing(fn (PostCategory $record): string => $record->name)
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->columnSpan(3),
                // Chọn theo tên thay vì nhập id thủ công.
                Select::make('user_id')->label('Người đăng')
                    ->options(fn (): array => User::query()->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required()
                    ->columnSpan(3),

                // Hàng 3: Từ khóa (Tag)
                TextInput::make('tag')->label('Từ khóa (Tag)')
                    ->default(null)
                    ->columnSpanFull(),

                // Hàng 3: Hình ảnh
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

                // Hàng 4: SEO Tiêu đề - SEO Từ khóa
                Textarea::make('meta_title')->label('SEO Tiêu đề')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),
                Textarea::make('meta_keyword')->label('SEO Từ khóa')
                    ->default(null)
                    ->rows(3)
                    ->columnSpan(3),

                // Hàng 5: SEO Mô tả - SEO Robots
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

                // Hàng 6: Rel - Target - Lượt xem - Trạng thái
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
                TextInput::make('view')->label('Lượt xem')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),
                Toggle::make('status')->label('Trạng thái')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(1),

                // Hàng 7 & 8: Tóm tắt, Mô tả
                RichEditor::make('summary')->label('Tóm tắt')
                    ->default(null)
                    ->columnSpanFull(),
                RichEditor::make('description')->label('Mô tả')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
