<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\ProductCategory;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // `name` là cột dịch đa ngữ (Spatie) lưu dạng JSON
                // {"vi":"...","en":"..."} nên phải tự viết truy vấn tìm kiếm,
                // searchable() mặc định không khớp được kiểu dữ liệu này.
                TextColumn::make('name')->label('Tên')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('products.name', 'like', "%{$search}%"))
                    ->sortable(),
                // Lấy tên danh mục qua quan hệ category() thay vì hiện id.
                TextColumn::make('category.name')->label('Danh mục')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'category',
                        fn (Builder $q): Builder => $q->where('name', 'like', "%{$search}%"),
                    ))
                    ->sortable(),
                ImageColumn::make('image')->label('Hình ảnh')
                    ->disk('public'),
                TextColumn::make('quantity')->label('Số lượng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('made')->label('Xuất xứ')
                    ->searchable(),
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
                // Tên danh mục là cột dịch đa ngữ (JSON) nên phải tự nạp
                // options qua accessor, không dùng relationship() được.
                SelectFilter::make('product_category_id')->label('Danh mục')
                    ->options(fn (): array => ProductCategory::query()
                        ->get()
                        ->mapWithKeys(fn (ProductCategory $c): array => [$c->id => $c->name])
                        ->all())
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
