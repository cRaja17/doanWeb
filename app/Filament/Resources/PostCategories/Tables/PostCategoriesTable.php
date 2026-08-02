<?php

namespace App\Filament\Resources\PostCategories\Tables;

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

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // `name` là cột dịch đa ngữ lưu JSON nên phải tự viết truy vấn
                // tìm kiếm, searchable() mặc định không khớp được.
                TextColumn::make('name')->label('Tên')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('name', 'like', "%{$search}%"))
                    ->sortable(),
                // Dữ liệu lẫn cả URL ngoài lẫn path nội bộ nên không dùng
                // ->disk('public') cố định được, phải tự sinh URL theo từng loại.
                ImageColumn::make('image')->label('Hình ảnh')
                    ->getStateUsing(fn ($record): ?string => ImageUrl::resolve($record->getRawOriginal('image'))),
                ToggleColumn::make('status')->label('Trạng thái')
                    ->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái.')
                            ->success()
                            ->send();
                    }),
                ToggleColumn::make('view_home')->label('Hiện trang chủ')
                    ->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật hiển thị trang chủ.')
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
                //
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
