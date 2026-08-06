<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use Filament\Actions\Action;
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
                // Chỉ hiển thị: trạng thái thanh toán do cổng thanh toán quyết định,
                // admin không được sửa tay nên dùng TextColumn thay cho SelectColumn.
                TextColumn::make('status')->label('Trạng thái thanh toán')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => Order::statusOptions()[$state] ?? (string) $state)
                    ->color(fn ($state): string => match ($state) {
                        Order::STATUS_PAID => 'success',
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_FAILED => 'danger',
                        Order::STATUS_CANCELLED => 'gray',
                        default => 'gray',
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
                // Đơn qua cổng thanh toán do cổng tự cập nhật trạng thái, nhưng
                // đơn COD thì không có cổng nào để làm việc đó - nếu không có
                // nút này thì đơn thu tiền mặt sẽ kẹt ở "chờ thanh toán" mãi.
                Action::make('confirm_cod')
                    ->label('Xác nhận đã thu tiền')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->method_payment === CheckoutRequest::METHOD_NONE
                        && $record->status === Order::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Xác nhận đã thu tiền')
                    ->modalDescription('Chỉ dùng cho đơn thanh toán khi nhận hàng (COD). Sau khi xác nhận sẽ không hoàn tác được.')
                    ->action(function (Order $record): void {
                        if ($record->markAsPaid()) {
                            Notification::make()
                                ->title('Thành công')
                                ->body('Đã ghi nhận đơn ' . $record->transaction_code . ' là đã thanh toán.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Không thực hiện được')
                            ->body('Đơn này đã được ghi nhận thanh toán trước đó.')
                            ->warning()
                            ->send();
                    }),
                EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Thành công')
                            ->body('Đã lưu bản ghi thành công.')
                    ),
            ])
            // Không có thao tác hàng loạt: xoá đơn hàng bị cấm hoàn toàn
            // (xem OrderResource::canDeleteAny() và Order::deletionBlockedReason()).
            ->toolbarActions([]);
    }
}
