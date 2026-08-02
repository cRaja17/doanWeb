<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_code')->label('Mã đơn hàng')
                    ->searchable(),
                TextColumn::make('name')->label('Tên')
                    ->searchable(),
                TextColumn::make('gender')->label('Giới tính')
                    ->searchable(),
                TextColumn::make('email')->label('Email')
                    ->searchable(),
                TextColumn::make('phone')->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('method_payment')->label('Phương thức TT')
                    ->searchable(),
                // Gộp `total` + `unit_payment` (đơn vị tiền tệ) thành một cột,
                // ghép giống cách frontend đang hiển thị.
                TextColumn::make('total')->label('Tổng tiền')
                    ->formatStateUsing(function ($state, $record): string {
                        $total = (float) $state;

                        // Giữ phần thập phân khi có (vd 49.99 USD), tránh
                        // number_format() làm tròn thành 50 USD.
                        $formatted = fmod($total, 1.0) === 0.0
                            ? number_format($total)
                            : number_format($total, 2);

                        return trim($formatted.' '.($record->unit_payment ?? ''));
                    })
                    ->sortable(),
                TextColumn::make('ip')->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('proxy')->label('Proxy')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                SelectColumn::make('status')->label('Trạng thái thanh toán')
                    ->options(Order::statusOptions())
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật trạng thái thanh toán.')
                            ->success()
                            ->send();
                    }),
                SelectColumn::make('order_status')->label('Tình trạng')
                    ->options(Order::orderStatusOptions())
                    ->selectablePlaceholder(false)
                    ->afterStateUpdated(function () {
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã cập nhật tình trạng đơn.')
                            ->success()
                            ->send();
                    }),
                TextColumn::make('created_at')->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')->label('Ngày cập nhật')
                    ->dateTime('d/m/Y H:i')
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
