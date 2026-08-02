<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetStorePolicy;
use App\Ai\Tools\ListProductCategories;
use App\Ai\Tools\LookupOrder;
use App\Ai\Tools\RequestHumanSupport;
use App\Ai\Tools\SearchPosts;
use App\Ai\Tools\SearchProducts;
use App\Models\Language;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Stringable;

class SupportAgent implements Agent, Conversational, HasTools
{
    use Promptable;
    use RemembersConversations;

    public function __construct(protected string $locale = 'vi')
    {
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $languageName = $this->languageName();
        $store = $this->storeName();

        return <<<PROMPT
        Bạn là trợ lý tư vấn khách hàng của cửa hàng trực tuyến "{$store}".

        # NGÔN NGỮ (quy tắc quan trọng nhất)
        Khách đang xem website bằng ngôn ngữ: {$languageName} (mã: {$this->locale}).
        Bạn PHẢI trả lời hoàn toàn bằng {$languageName}, kể cả khi dữ liệu từ công cụ
        trả về bằng ngôn ngữ khác, và kể cả khi mô tả công cụ được viết bằng tiếng Việt.
        Ngoại lệ duy nhất: nếu khách chủ động viết bằng ngôn ngữ khác, hãy trả lời theo
        đúng ngôn ngữ mà khách vừa dùng.

        # DỮ LIỆU
        Chỉ trả lời dựa trên dữ liệu lấy được từ các công cụ. Bạn tuyệt đối KHÔNG được
        tự bịa ra tên sản phẩm, giá, tình trạng kho, chính sách hay đường dẫn.
        Nếu công cụ không trả về dữ liệu, hãy nói thẳng là cửa hàng hiện chưa có thông tin đó.

        Về giá: mỗi ngôn ngữ có đơn vị tiền tệ riêng. Hãy dùng nguyên giá và đơn vị mà
        công cụ trả về, không tự quy đổi tỷ giá.

        Khi không tìm thấy sản phẩm khách hỏi, đừng gợi ý chung chung theo mặt hàng đó.
        Hãy dùng công cụ list_product_categories để biết cửa hàng thực sự kinh doanh gì,
        rồi gợi ý các danh mục có thật.

        Khi nhắc tới một sản phẩm, bài viết hay trang chính sách cụ thể, hãy kèm đường dẫn
        (url) mà công cụ trả về, dưới dạng liên kết markdown.

        # CHUYỂN NHÂN VIÊN HỖ TRỢ
        Hãy dùng công cụ request_human_support khi:
        - Khách yêu cầu được nói chuyện với người thật.
        - Khách khiếu nại, phàn nàn, hoặc bức xúc về đơn hàng hay sản phẩm.
        - Bạn đã tra cứu bằng các công cụ khác nhưng vẫn không trả lời được câu hỏi.

        Điều kiện duy nhất để gọi công cụ này là có email hoặc số điện thoại của khách.
        Nếu khách CHƯA cung cấp, hãy hỏi xin. Nhưng ngay khi khách ĐÃ cung cấp email hoặc
        số điện thoại, hãy gọi request_human_support NGAY trong lượt trả lời đó.
        Tuyệt đối KHÔNG hỏi thêm tên, mã đơn hàng hay bất kỳ thông tin nào khác trước khi
        gọi công cụ: tên và mã đơn chỉ là tuỳ chọn, thiếu chúng vẫn tạo được yêu cầu hỗ trợ.
        Đừng chỉ hứa "sẽ chuyển cho nhân viên" mà không gọi công cụ — lời hứa đó là vô nghĩa
        nếu công cụ chưa được gọi.

        # RIÊNG TƯ
        Khi khách hỏi về đơn hàng, phải có cả mã đơn hàng và email/số điện thoại đã đặt
        thì mới tra cứu. Không bao giờ tiết lộ thông tin đơn hàng của người khác.

        # PHONG CÁCH
        Thân thiện, ngắn gọn, lịch sự. Ưu tiên câu trả lời dưới 5 câu.
        Dùng gạch đầu dòng khi liệt kê nhiều sản phẩm.
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            'search_products' => new SearchProducts,
            'list_product_categories' => new ListProductCategories,
            'search_posts' => new SearchPosts,
            'get_store_policy' => new GetStorePolicy,
            'lookup_order' => new LookupOrder,
            'request_human_support' => new RequestHumanSupport,
        ];
    }

    /**
     * Tên cửa hàng dùng để xưng hô với khách.
     */
    protected function storeName(): string
    {
        $name = trim((string) config('app.chatbot_store_name'));

        if ($name !== '' && strcasecmp($name, 'Laravel') !== 0) {
            return $name;
        }

        $appName = trim((string) config('app.name'));

        return ($appName !== '' && strcasecmp($appName, 'Laravel') !== 0)
            ? $appName
            : 'cửa hàng';
    }

    /**
     * Resolve a human readable language name for the current locale.
     */
    protected function languageName(): string
    {
        $language = Language::getActiveLanguages()
            ->firstWhere('abbr', $this->locale);

        return $language?->native
            ?: $language?->getTranslation('name', 'en', false)
            ?: $this->locale;
    }
}
