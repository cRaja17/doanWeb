@php
    // Chuỗi giao diện của widget. Đặt trực tiếp tại đây (thay vì bảng translations)
    // để widget vẫn chạy đúng khi các khoá dịch chưa được nhập trong trang quản trị.
    $locale = app()->getLocale();
    $t = [
        'vi' => [
            'title' => 'Tư vấn trực tuyến',
            'subtitle' => 'Thường phản hồi ngay',
            'greeting' => 'Xin chào! Mình có thể giúp bạn tìm sản phẩm, kiểm tra đơn hàng hoặc giải đáp chính sách của cửa hàng. Bạn cần hỗ trợ gì ạ?',
            'placeholder' => 'Nhập câu hỏi của bạn...',
            'send' => 'Gửi',
            'open' => 'Mở khung chat',
            'close' => 'Đóng',
            'typing' => 'Đang trả lời...',
            'error' => 'Không gửi được tin nhắn. Bạn vui lòng thử lại nhé.',
            'limit' => 'Bạn đang gửi hơi nhanh. Vui lòng chờ một lát rồi thử lại.',
            'staff' => 'Yêu cầu nhân viên hỗ trợ',
            'staff_note' => 'Nhân viên sẽ liên hệ lại với bạn.',
            'name' => 'Tên của bạn (không bắt buộc)',
            'contact' => 'Email hoặc số điện thoại',
            'question' => 'Bạn cần hỗ trợ điều gì?',
            'submit' => 'Gửi yêu cầu',
            'cancel' => 'Quay lại',
            'sent' => 'Đã gửi yêu cầu! Nhân viên sẽ liên hệ với bạn sớm nhất.',
            'required' => 'Vui lòng nhập thông tin liên hệ và nội dung cần hỗ trợ.',
        ],
        'en' => [
            'title' => 'Online support',
            'subtitle' => 'Usually replies instantly',
            'greeting' => 'Hi there! I can help you find products, check an order, or answer questions about our policies. What can I do for you?',
            'placeholder' => 'Type your question...',
            'send' => 'Send',
            'open' => 'Open chat',
            'close' => 'Close',
            'typing' => 'Typing...',
            'error' => 'Your message could not be sent. Please try again.',
            'limit' => 'You are sending messages too quickly. Please wait a moment.',
            'staff' => 'Request human support',
            'staff_note' => 'Our staff will get back to you.',
            'name' => 'Your name (optional)',
            'contact' => 'Email or phone number',
            'question' => 'What do you need help with?',
            'submit' => 'Submit request',
            'cancel' => 'Back',
            'sent' => 'Request sent! Our staff will contact you shortly.',
            'required' => 'Please provide your contact details and your question.',
        ],
    ];
    $s = $t[$locale] ?? $t['vi'];
@endphp

<div id="chatbot-widget" class="chatbot" data-locale="{{ $locale }}">
    <button type="button" class="chatbot__launcher" id="chatbot-launcher" aria-label="{{ $s['open'] }}">
        <i class="fa-solid fa-comment-dots"></i>
    </button>

    <div class="chatbot__panel" id="chatbot-panel" role="dialog" aria-label="{{ $s['title'] }}">
        <div class="chatbot__header">
            <div class="chatbot__avatar"><i class="fa-solid fa-headset"></i></div>
            <div class="chatbot__heading">
                <strong>{{ $s['title'] }}</strong>
                <span>{{ $s['subtitle'] }}</span>
            </div>
            <button type="button" class="chatbot__close" id="chatbot-close" aria-label="{{ $s['close'] }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="chatbot__body" id="chatbot-body">
            <div class="chatbot__msg chatbot__msg--bot">{{ $s['greeting'] }}</div>
        </div>

        <form class="chatbot__support" id="chatbot-support-form" hidden>
            <p class="chatbot__support-note">{{ $s['staff_note'] }}</p>
            <input type="text" id="chatbot-sup-name" placeholder="{{ $s['name'] }}" maxlength="100">
            <input type="text" id="chatbot-sup-contact" placeholder="{{ $s['contact'] }}" maxlength="150" required>
            <textarea id="chatbot-sup-question" placeholder="{{ $s['question'] }}" rows="3" maxlength="2000" required></textarea>
            <div class="chatbot__support-actions">
                <button type="button" class="chatbot__btn-ghost" id="chatbot-sup-cancel">{{ $s['cancel'] }}</button>
                <button type="submit" class="chatbot__btn-solid">{{ $s['submit'] }}</button>
            </div>
        </form>

        <div class="chatbot__footer" id="chatbot-footer">
            <button type="button" class="chatbot__staff-link" id="chatbot-staff-btn">
                <i class="fa-solid fa-user-headset"></i> {{ $s['staff'] }}
            </button>
            <form class="chatbot__input" id="chatbot-form">
                <input type="text" id="chatbot-input" placeholder="{{ $s['placeholder'] }}" maxlength="1000" autocomplete="off">
                <button type="submit" aria-label="{{ $s['send'] }}"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</div>

<style>
    .chatbot { position: fixed; right: 24px; bottom: 24px; z-index: 99998; font-size: 15px; }
    .chatbot__launcher { width: 58px; height: 58px; border-radius: 50%; border: 0; cursor: pointer;
        background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-size: 1.5rem;
        box-shadow: 0 10px 25px rgba(37, 99, 235, .35); transition: transform .2s ease; }
    .chatbot__launcher:hover { transform: scale(1.06); }
    .chatbot__panel { position: absolute; right: 0; bottom: 74px; width: 370px; max-width: calc(100vw - 32px);
        height: 520px; max-height: calc(100vh - 120px); background: #fff; border-radius: 16px; overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25); display: none; flex-direction: column; }
    .chatbot__panel.is-open { display: flex; }
    .chatbot__header { display: flex; align-items: center; gap: .75rem; padding: .9rem 1rem;
        background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; }
    .chatbot__avatar { width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center; flex: 0 0 auto; }
    .chatbot__heading { display: flex; flex-direction: column; line-height: 1.25; flex: 1 1 auto; min-width: 0; }
    .chatbot__heading span { font-size: .78rem; opacity: .85; }
    .chatbot__close { background: none; border: 0; color: #fff; cursor: pointer; font-size: 1.1rem; }
    .chatbot__body { flex: 1 1 auto; overflow-y: auto; padding: 1rem; background: #f8fafc;
        display: flex; flex-direction: column; gap: .6rem; }
    .chatbot__msg { padding: .65rem .85rem; border-radius: 14px; max-width: 85%; word-wrap: break-word;
        line-height: 1.5; font-size: .92rem; white-space: pre-wrap; }
    .chatbot__msg--bot { background: #fff; color: #0f172a; align-self: flex-start; border: 1px solid #e2e8f0; }
    .chatbot__msg--user { background: #2563eb; color: #fff; align-self: flex-end; }
    .chatbot__msg--error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; align-self: flex-start; }
    .chatbot__msg a { color: inherit; text-decoration: underline; font-weight: 600; }
    .chatbot__msg--bot a { color: #1d4ed8; }
    .chatbot__msg ul { margin: .4rem 0 0; padding-left: 1.1rem; }
    .chatbot__msg--typing { color: #64748b; font-style: italic; }
    .chatbot__footer { border-top: 1px solid #e2e8f0; background: #fff; }
    .chatbot__staff-link { width: 100%; border: 0; background: #f1f5f9; color: #475569; cursor: pointer;
        padding: .5rem; font-size: .82rem; font-weight: 600; }
    .chatbot__staff-link:hover { background: #e2e8f0; }
    .chatbot__input { display: flex; align-items: center; gap: .5rem; padding: .6rem; margin: 0; }
    .chatbot__input input { flex: 1 1 auto; border: 1px solid #cbd5e1; border-radius: 999px;
        padding: .55rem .9rem; outline: none; font-size: .92rem; margin: 0; height: auto; box-shadow: none; }
    .chatbot__input input:focus { border-color: #2563eb; }
    .chatbot__input button { width: 40px; height: 40px; flex: 0 0 auto; border: 0; border-radius: 50%;
        background: #2563eb; color: #fff; cursor: pointer; }
    .chatbot__input button:disabled { background: #94a3b8; cursor: not-allowed; }
    .chatbot__support { padding: 1rem; background: #fff; border-top: 1px solid #e2e8f0; overflow-y: auto; }
    .chatbot__support-note { font-size: .85rem; color: #64748b; margin-bottom: .6rem; }
    .chatbot__support input, .chatbot__support textarea { width: 100%; border: 1px solid #cbd5e1;
        border-radius: 10px; padding: .55rem .75rem; margin-bottom: .5rem; font-size: .9rem; height: auto; box-shadow: none; }
    .chatbot__support-actions { display: flex; gap: .5rem; }
    .chatbot__btn-ghost, .chatbot__btn-solid { flex: 1 1 0; border-radius: 10px; padding: .55rem; cursor: pointer;
        font-weight: 600; font-size: .88rem; border: 1px solid #cbd5e1; }
    .chatbot__btn-ghost { background: #fff; color: #475569; }
    .chatbot__btn-solid { background: #2563eb; color: #fff; border-color: #2563eb; }
    @media (max-width: 480px) {
        .chatbot { right: 14px; bottom: 14px; }
        .chatbot__panel { width: calc(100vw - 28px); height: calc(100vh - 110px); }
    }
</style>

<script>
    (function () {
        var STR = @json($s);
        var conversationId = null;
        var busy = false;

        var $panel = $('#chatbot-panel');
        var $body = $('#chatbot-body');
        var $input = $('#chatbot-input');
        var $supportForm = $('#chatbot-support-form');
        var $footer = $('#chatbot-footer');

        function escapeHtml(text) {
            return $('<div>').text(text).html();
        }

        // Chuyển phần markdown cơ bản mà model hay dùng sang HTML an toàn.
        function renderMarkdown(text) {
            var html = escapeHtml(text);
            html = html.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,
                '<a href="$2" target="_blank" rel="noopener">$1</a>');
            html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
            return html;
        }

        function addMessage(text, type) {
            var cls = 'chatbot__msg chatbot__msg--' + type;
            var content = (type === 'bot') ? renderMarkdown(text) : escapeHtml(text);
            $body.append($('<div>').attr('class', cls).html(content));
            $body.scrollTop($body[0].scrollHeight);
        }

        function setBusy(state) {
            busy = state;
            $('#chatbot-form button').prop('disabled', state);
            if (state) {
                $body.append($('<div>').attr('class', 'chatbot__msg chatbot__msg--bot chatbot__msg--typing')
                    .attr('id', 'chatbot-typing').text(STR.typing));
                $body.scrollTop($body[0].scrollHeight);
            } else {
                $('#chatbot-typing').remove();
            }
        }

        $('#chatbot-launcher').on('click', function () {
            $panel.toggleClass('is-open');
            if ($panel.hasClass('is-open')) { $input.trigger('focus'); }
        });

        $('#chatbot-close').on('click', function () {
            $panel.removeClass('is-open');
        });

        $('#chatbot-form').on('submit', function (e) {
            e.preventDefault();
            var message = $.trim($input.val());
            if (!message || busy) { return; }

            addMessage(message, 'user');
            $input.val('');
            setBusy(true);

            $.ajax({
                url: "{{ route('ajax_chatbot_send') }}",
                type: 'POST',
                data: { message: message, conversation_id: conversationId },
                dataType: 'json'
            }).done(function (res) {
                setBusy(false);
                conversationId = res.conversation_id || conversationId;
                addMessage(res.reply, 'bot');
                if (res.escalated) { $('#chatbot-staff-btn').hide(); }
            }).fail(function (xhr) {
                setBusy(false);
                if (xhr.status === 429) {
                    addMessage(STR.limit, 'error');
                } else if (xhr.status === 503 && xhr.responseJSON && xhr.responseJSON.reply) {
                    addMessage(xhr.responseJSON.reply, 'error');
                } else if (xhr.status === 419) {
                    location.reload();
                } else {
                    addMessage(STR.error, 'error');
                }
            });
        });

        $('#chatbot-staff-btn').on('click', function () {
            $supportForm.prop('hidden', false);
            $footer.hide();
            $('#chatbot-sup-question').val($.trim($input.val()));
        });

        $('#chatbot-sup-cancel').on('click', function () {
            $supportForm.prop('hidden', true);
            $footer.show();
        });

        $supportForm.on('submit', function (e) {
            e.preventDefault();
            var contact = $.trim($('#chatbot-sup-contact').val());
            var question = $.trim($('#chatbot-sup-question').val());

            if (!contact || !question) {
                addMessage(STR.required, 'error');
                return;
            }

            $.ajax({
                url: "{{ route('ajax_chatbot_support') }}",
                type: 'POST',
                data: {
                    name: $.trim($('#chatbot-sup-name').val()),
                    contact: contact,
                    question: question,
                    conversation_id: conversationId
                },
                dataType: 'json'
            }).done(function () {
                $supportForm.prop('hidden', true).trigger('reset');
                $footer.show();
                $('#chatbot-staff-btn').hide();
                addMessage(STR.sent, 'bot');
            }).fail(function (xhr) {
                addMessage(xhr.status === 429 ? STR.limit : STR.error, 'error');
            });
        });
    })();
</script>
