<?php

namespace App\Filament\Resources\Information\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class InformationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên')
                    ->searchable(),
                ToggleColumn::make('status')->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái.')
                            ->success()
                            ->send();
                    })->label('Trạng thái'),
                ToggleColumn::make('view_header')->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái.')
                            ->success()
                            ->send();
                    })->label('Hiển thị Header'),
                ToggleColumn::make('view_footer')->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái.')
                            ->success()
                            ->send();
                    })->label('Hiển thị Footer'),
                TextColumn::make('created_at')->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')->label('Ngày cập nhật')
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
