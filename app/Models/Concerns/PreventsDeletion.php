<?php

namespace App\Models\Concerns;

use App\Exceptions\DeletionNotAllowedException;

/**
 * Chặn xoá vĩnh viễn ở tầng model.
 *
 * Gỡ nút xoá trong admin thôi là chưa đủ: chỉ cần một đoạn code, một lệnh
 * tinker hay một action mới thêm sau này là dữ liệu bay mất. Đây là chốt chặn
 * cuối cùng, áp cho những bảng mà mất dữ liệu là không khôi phục được.
 */
trait PreventsDeletion
{
    public static function bootPreventsDeletion(): void
    {
        static::deleting(function ($model): void {
            throw new DeletionNotAllowedException($model->deletionBlockedReason());
        });
    }

    /**
     * Vì sao bản ghi này không được xoá (hiện trong log / thông báo lỗi).
     */
    abstract public function deletionBlockedReason(): string;
}
