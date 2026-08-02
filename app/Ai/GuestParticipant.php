<?php

namespace App\Ai;

/**
 * Người tham gia hội thoại là khách vãng lai (chưa đăng nhập).
 *
 * Laravel AI chỉ yêu cầu participant là một object có thuộc tính `id`, nên khách
 * chưa đăng nhập vẫn có thể có lịch sử hội thoại riêng, khoá theo session id.
 */
class GuestParticipant
{
    public function __construct(public string $id) {}
}
