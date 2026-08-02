<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Support\ImageUrl;
use Filament\Tables\Filters\SelectFilter;
use App\Models\PostCategory;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // `name` là cột dịch đa ngữ lưu JSON nên phải tự viết truy vấn
                // tìm kiếm, searchable() mặc định không khớp được.
                TextColumn::make('name')->label('Tên')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('posts.name', 'like', "%{$search}%"))
                    ->sortable(),
                // Bài viết thuộc nhiều danh mục (quan hệ nhiều-nhiều).
                TextColumn::make('categories.name')->label('Danh mục')
                    ->badge()
                    ->placeholder('(chưa gán)'),
                TextColumn::make('user.name')->label('Người đăng')
                    ->searchable()
                    ->sortable(),
                // Dữ liệu lẫn cả URL ngoài lẫn path nội bộ nên không dùng
                // ->disk('public') cố định được, phải tự sinh URL theo từng loại.
                ImageColumn::make('image')->label('Hình ảnh')
                    ->getStateUsing(fn ($record): ?string => ImageUrl::resolve($record->getRawOriginal('image'))),
                TextColumn::make('view')->label('Lượt xem')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('status')->label('Trạng thái')
                    ->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái.')
                            ->success()
                            ->send();
                    }),
                TextColumn::make('created_at')->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')->label('Ngày sửa')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // Bài viết gắn danh mục qua quan hệ nhiều-nhiều
                // (bảng post_category_relation) nên lọc bằng whereHas.
                SelectFilter::make('category')->label('Danh mục')
                    ->options(fn (): array => PostCategory::query()
                        ->get()
                        ->mapWithKeys(fn (PostCategory $c): array => [$c->id => $c->name])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => blank($data['value'] ?? null)
                        ? $query
                        : $query->whereHas(
                            'categories',
                            fn (Builder $q): Builder => $q->whereKey($data['value']),
                        ))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Thành công')
                            ->body('Đã lưu bản ghi thành công.')
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
