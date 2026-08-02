<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Models\Language;
use App\Support\ImageUrl;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Tên')
                    ->searchable(),
                ImageColumn::make('flag')->label('Cờ')
                    ->getStateUsing(fn ($record): ?string => ImageUrl::resolve($record->flag)),
                TextColumn::make('abbr')
                    ->searchable(),
                TextColumn::make('script')
                    ->searchable(),
                TextColumn::make('native')
                    ->searchable(),
                TextColumn::make('unit')->label('Đơn vị')
                    ->searchable(),
                ToggleColumn::make('active')->label('Trạng thái')
                    ->sortable(),
                ToggleColumn::make('default')->label('Mặc định')
                    ->sortable()
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            Language::where('id', '!=', $record->id)->update(['default' => 0]);
                        }
                    }),
                TextColumn::make('created_at')->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
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
