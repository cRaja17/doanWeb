<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    public function __construct(protected ChatBotService $chatbot) {}

    /**
     * Nhận tin nhắn của khách và trả về câu trả lời của trợ lý AI.
     */
    public function ajaxSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'uuid'],
        ]);

        $result = $this->chatbot->ask(
            message: $validated['message'],
            conversationId: $validated['conversation_id'] ?? null,
            sessionId: $request->session()->getId(),
        );

        return response()->json($result, $result['ok'] ? 200 : 503);
    }

    /**
     * Khách chủ động để lại thông tin để nhân viên liên hệ lại.
     */
    public function ajaxSupport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'contact' => ['required', 'string', 'max:150'],
            'question' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'uuid'],
        ]);

        $ticket = $this->chatbot->createSupportRequest(
            $validated,
            $validated['conversation_id'] ?? null
        );

        return response()->json([
            'ok' => true,
            'ticket_id' => $ticket->id,
        ]);
    }
}
