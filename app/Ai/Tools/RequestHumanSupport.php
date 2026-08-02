<?php

namespace App\Ai\Tools;

use App\Models\SupportRequest;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RequestHumanSupport implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Chuyển tiếp câu hỏi của khách tới nhân viên hỗ trợ là người thật. '
            .'Dùng công cụ này khi: khách yêu cầu gặp nhân viên, hoặc bạn đã thử các công cụ khác '
            .'mà vẫn không tìm được câu trả lời, hoặc khách khiếu nại / phàn nàn về đơn hàng. '
            .'BẮT BUỘC phải hỏi và có thông tin liên hệ (email hoặc số điện thoại) của khách trước khi gọi công cụ này.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $contact = trim($request->string('contact', '')->value());
        $question = trim($request->string('question', '')->value());

        if ($contact === '') {
            return 'Chưa có thông tin liên hệ. Hãy hỏi khách email hoặc số điện thoại để nhân viên liên hệ lại, rồi gọi lại công cụ này.';
        }

        if ($question === '') {
            return 'Chưa có nội dung cần hỗ trợ. Hãy tóm tắt lại vấn đề của khách rồi gọi lại công cụ này.';
        }

        $ticket = SupportRequest::create([
            'conversation_id' => app()->bound('chatbot.conversation_id')
                ? app('chatbot.conversation_id')
                : null,
            'name' => str(trim($request->string('name', '')->value()))->limit(100)->value() ?: null,
            'contact' => str($contact)->limit(150)->value(),
            'locale' => app()->getLocale(),
            'question' => str($question)->limit(2000)->value(),
            'context' => str(trim($request->string('context', '')->value()))->limit(2000)->value() ?: null,
            'status' => 'pending',
            'ip' => request()->ip(),
        ]);

        return 'Đã tạo yêu cầu hỗ trợ #'.$ticket->id.'. Hãy báo với khách rằng yêu cầu đã được ghi nhận '
            .'và nhân viên sẽ liên hệ lại qua thông tin khách vừa cung cấp trong thời gian làm việc. '
            .'Nhắc khách ghi nhớ mã yêu cầu #'.$ticket->id.'.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'contact' => $schema->string()->required()
                ->description('Email hoặc số điện thoại của khách để nhân viên liên hệ lại.'),
            'question' => $schema->string()->required()
                ->description('Tóm tắt rõ ràng vấn đề khách cần hỗ trợ.'),
            'name' => $schema->string()
                ->description('Tên khách hàng, nếu khách có cung cấp.'),
            'context' => $schema->string()
                ->description('Bối cảnh thêm, ví dụ mã đơn hàng hoặc tên sản phẩm khách đang hỏi.'),
        ];
    }
}
