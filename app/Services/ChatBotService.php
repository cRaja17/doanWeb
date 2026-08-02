<?php

namespace App\Services;

use App\Ai\Agents\SupportAgent;
use App\Ai\GuestParticipant;
use App\Models\SupportRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ChatBotService
{
    /**
     * Gửi một tin nhắn của khách tới trợ lý AI và trả về câu trả lời.
     *
     * @return array{reply: string, conversation_id: ?string, escalated: bool, ok: bool}
     */
    public function ask(string $message, ?string $conversationId = null, ?string $sessionId = null): array
    {
        $locale = app()->getLocale();

        // Trình duyệt (hoặc client lạ) có thể gửi chuỗi không phải UTF-8 hợp lệ.
        // Nếu để nguyên, json_encode trong tầng HTTP của nhà cung cấp AI sẽ ném lỗi.
        $message = $this->toValidUtf8($message);

        if ($message === '') {
            return [
                'ok' => false,
                'reply' => $this->fallbackMessage($locale),
                'conversation_id' => $conversationId,
                'escalated' => false,
            ];
        }

        $agent = new SupportAgent($locale);

        $participant = Auth::user() ?: new GuestParticipant($sessionId ?: (string) Str::uuid());

        // Nối tiếp hội thoại cũ nếu client gửi kèm id, ngược lại bắt đầu hội thoại mới.
        $conversationId
            ? $agent->continue($conversationId, $participant)
            : $agent->forParticipant($participant);

        // Đánh dấu mốc để nhận ra ticket vừa được tạo trong lượt này.
        $ticketIdBefore = SupportRequest::max('id');

        // Cho tool RequestHumanSupport biết nó đang thuộc hội thoại nào.
        app()->instance('chatbot.conversation_id', $conversationId);

        try {
            $response = $agent->prompt($message);

            // Với hội thoại mới, id chỉ có trên response chứ chưa có trên agent.
            $newConversationId = $response->conversationId
                ?: ($agent->currentConversation() ?: $conversationId);

            // Hội thoại mới chưa có id vào lúc tool chạy, nên gắn lại ticket ở đây.
            $escalated = $this->attachPendingTickets($ticketIdBefore, $newConversationId);

            return [
                'ok' => true,
                'reply' => $response->text,
                'conversation_id' => $newConversationId,
                'escalated' => $escalated || $this->wasEscalated($newConversationId),
            ];
        } catch (Throwable $e) {
            Log::error('Chatbot failed', [
                'message' => $e->getMessage(),
                'locale' => $locale,
                'exception' => $e::class,
                'at' => $e->getFile().':'.$e->getLine(),
            ]);

            return [
                'ok' => false,
                'reply' => $this->fallbackMessage($locale),
                'conversation_id' => $conversationId,
                'escalated' => false,
            ];
        }
    }

    /**
     * Ghi nhận yêu cầu hỗ trợ do khách chủ động bấm nút, không qua AI.
     */
    public function createSupportRequest(array $data, ?string $conversationId = null): SupportRequest
    {
        return SupportRequest::create([
            'conversation_id' => $conversationId,
            'name' => $data['name'] ?? null,
            'contact' => $data['contact'],
            'locale' => app()->getLocale(),
            'question' => $data['question'],
            'status' => 'pending',
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Ép chuỗi về UTF-8 hợp lệ, loại bỏ các byte hỏng.
     */
    protected function toValidUtf8(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            // Bỏ qua byte không hợp lệ thay vì đoán bảng mã nguồn.
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return trim($value);
    }

    /**
     * Gắn các ticket vừa được tạo trong lượt này vào hội thoại tương ứng.
     *
     * @return bool Có ticket nào được tạo trong lượt này hay không.
     */
    protected function attachPendingTickets(?int $ticketIdBefore, ?string $conversationId): bool
    {
        $query = SupportRequest::query()
            ->when($ticketIdBefore, fn ($q) => $q->where('id', '>', $ticketIdBefore));

        if (! $conversationId) {
            return $query->exists();
        }

        return $query->whereNull('conversation_id')
            ->update(['conversation_id' => $conversationId]) > 0;
    }

    /**
     * Kiểm tra hội thoại này có yêu cầu hỗ trợ nào đang chờ nhân viên xử lý không.
     */
    protected function wasEscalated(?string $conversationId): bool
    {
        if (! $conversationId) {
            return false;
        }

        return SupportRequest::where('conversation_id', $conversationId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Câu trả lời dự phòng khi không gọi được nhà cung cấp AI.
     */
    protected function fallbackMessage(string $locale): string
    {
        return match ($locale) {
            'en' => 'Sorry, our assistant is temporarily unavailable. Please try again in a moment, or leave your contact details so our staff can get back to you.',
            'fr' => "Désolé, notre assistant est momentanément indisponible. Réessayez dans un instant ou laissez vos coordonnées pour être recontacté.",
            default => 'Xin lỗi, trợ lý đang tạm thời bận. Bạn vui lòng thử lại sau giây lát, hoặc để lại thông tin liên hệ để nhân viên hỗ trợ bạn nhé.',
        };
    }
}
