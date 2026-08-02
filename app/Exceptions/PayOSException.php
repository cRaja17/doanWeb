<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lỗi phát sinh khi làm việc với payOS: gọi API thất bại, dữ liệu trả về sai
 * chữ ký, hoặc webhook không hợp lệ.
 */
class PayOSException extends RuntimeException
{
    /**
     * Mã lỗi nghiệp vụ của payOS (ví dụ "231"), khác với HTTP status.
     */
    public function __construct(string $message, public readonly ?string $payosCode = null)
    {
        parent::__construct($message);
    }
}
