<?php

namespace App\Exceptions;

use Exception;

/**
 * Ném ra khi tồn kho không đủ cho một dòng hàng. Cố ý ném từ bên trong
 * transaction tạo đơn để cả đơn được rollback thay vì tạo ra đơn không giao được.
 */
class OutOfStockException extends Exception
{
    public function __construct(
        private readonly string $productName,
        private readonly int $available
    ) {
        parent::__construct("Sản phẩm \"{$productName}\" chỉ còn {$available} trong kho.");
    }

    /**
     * Thông báo hiển thị cho khách, theo ngôn ngữ đang xem.
     */
    public function userMessage(): string
    {
        if ($this->available <= 0) {
            return translate('cart.out_of_stock', ['name' => $this->productName]);
        }

        return translate('cart.insufficient_stock', [
            'name' => $this->productName,
            'available' => $this->available,
        ]);
    }
}
