<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Bản ghi bị cấm xoá vĩnh viễn.
 *
 * Cố ý ném lỗi chứ không lặng lẽ bỏ qua: nếu chỉ trả về false trong sự kiện
 * `deleting` thì Filament vẫn báo "đã xoá thành công" trong khi thực tế không
 * xoá gì - người dùng sẽ tưởng dữ liệu đã mất.
 */
class DeletionNotAllowedException extends RuntimeException
{
}
