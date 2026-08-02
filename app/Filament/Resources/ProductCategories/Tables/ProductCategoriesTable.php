<?php

namespace App\Filament\Resources\ProductCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\ImageColumn::make('image')
                    ->label('Hình ảnh')
                    ->disk('public'),
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
