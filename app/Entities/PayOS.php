<?php

namespace App\Entities;

use App\Exceptions\PayOSException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Client cho payOS Merchant API v2.
 *
 * Toàn bộ dữ liệu đi/về đều được ký HMAC-SHA256 bằng checksum key theo đúng
 * thuật toán của SDK chính thức (payos/payos):
 *  - Request tạo link thanh toán: ký 5 trường theo thứ tự cố định.
 *  - Response và webhook: ký object `data` sau khi sắp xếp key theo alphabet.
 *
 * @see https://payos.vn/docs
 */
class PayOS
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_UNDERPAID = 'UNDERPAID';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_FAILED = 'FAILED';

    /** Mã thành công của payOS. */
    public const CODE_SUCCESS = '00';

    /** payOS giới hạn description tối đa 25 ký tự (nội dung chuyển khoản). */
    public const DESCRIPTION_MAX_LENGTH = 25;

    private string $clientId;

    private string $apiKey;

    private string $checksumKey;

    private ?string $partnerCode;

    private string $baseUrl;

    private int $timeout;

    public function __construct()
    {
        $config = config('payment.payos', []);

        $this->clientId = (string) ($config['client_id'] ?? '');
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->checksumKey = (string) ($config['checksum_key'] ?? '');
        $this->partnerCode = $config['partner_code'] ?? null;
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? 'https://api-merchant.payos.vn'), '/');
        $this->timeout = (int) ($config['timeout'] ?? 20);
    }

    /**
     * Đã cấu hình đủ 3 khoá để gọi payOS hay chưa.
     */
    public function isConfigured(): bool
    {
        return $this->clientId !== '' && $this->apiKey !== '' && $this->checksumKey !== '';
    }

    /**
     * Tạo link thanh toán.
     *
     * @param  array  $payload  orderCode, amount, description, returnUrl, cancelUrl
     *                          và các trường tuỳ chọn (items, buyerName, expiredAt...).
     * @return array data trả về: checkoutUrl, qrCode, paymentLinkId, accountNumber...
     *
     * @throws PayOSException
     */
    public function createPaymentLink(array $payload): array
    {
        foreach (['orderCode', 'amount', 'description', 'returnUrl', 'cancelUrl'] as $field) {
            if (! isset($payload[$field])) {
                throw new PayOSException("Thiếu trường bắt buộc \"{$field}\" khi tạo link thanh toán payOS.");
            }
        }

        $payload['signature'] = $this->signPaymentRequest($payload);

        return $this->request('POST', '/v2/payment-requests', $payload);
    }

    /**
     * Lấy thông tin link thanh toán theo paymentLinkId hoặc orderCode.
     *
     * @throws PayOSException
     */
    public function getPaymentLink(string|int $id): array
    {
        return $this->request('GET', '/v2/payment-requests/'.$id);
    }

    /**
     * Huỷ link thanh toán theo paymentLinkId hoặc orderCode.
     *
     * @throws PayOSException
     */
    public function cancelPaymentLink(string|int $id, ?string $reason = null): array
    {
        return $this->request(
            'POST',
            '/v2/payment-requests/'.$id.'/cancel',
            $reason === null ? [] : ['cancellationReason' => $reason]
        );
    }

    /**
     * Đăng ký (và để payOS tự kiểm tra) URL nhận webhook.
     *
     * payOS sẽ gọi thử URL này bằng một payload mẫu; endpoint phải trả HTTP 2xx
     * thì việc đăng ký mới thành công.
     *
     * @throws PayOSException
     */
    public function confirmWebhook(string $webhookUrl): array
    {
        if (trim($webhookUrl) === '') {
            throw new PayOSException('URL webhook không được để trống.');
        }

        // Endpoint này không trả về `data` đã ký nên bỏ qua bước verify chữ ký.
        return $this->request('POST', '/confirm-webhook', ['webhookUrl' => $webhookUrl], verifyResponse: false);
    }

    /**
     * Kiểm tra chữ ký của webhook payOS gửi về và trả lại phần `data`.
     *
     * @throws PayOSException
     */
    public function verifyWebhook(array $payload): array
    {
        $data = $payload['data'] ?? null;
        $signature = $payload['signature'] ?? null;

        if (! is_array($data) || $data === []) {
            throw new PayOSException('Webhook payOS không có dữ liệu.');
        }

        if (! is_string($signature) || $signature === '') {
            throw new PayOSException('Webhook payOS thiếu chữ ký.');
        }

        if (! hash_equals($this->signObject($data), $signature)) {
            throw new PayOSException('Chữ ký webhook payOS không hợp lệ.');
        }

        return $data;
    }

    /**
     * Chữ ký của request tạo link thanh toán: 5 trường, thứ tự cố định.
     */
    public function signPaymentRequest(array $payload): string
    {
        $raw = sprintf(
            'amount=%s&cancelUrl=%s&description=%s&orderCode=%s&returnUrl=%s',
            $payload['amount'],
            $payload['cancelUrl'],
            $payload['description'],
            $payload['orderCode'],
            $payload['returnUrl']
        );

        return hash_hmac('sha256', $raw, $this->checksumKey);
    }

    /**
     * Chữ ký của một object dữ liệu (response body & webhook): sắp xếp key theo
     * alphabet rồi nối thành chuỗi query `key=value&...`.
     */
    public function signObject(array $data): string
    {
        ksort($data);

        $pairs = [];
        foreach ($data as $key => $value) {
            $pairs[] = $key.'='.$this->stringifyValue($value);
        }

        return hash_hmac('sha256', implode('&', $pairs), $this->checksumKey);
    }

    /**
     * Chuẩn hoá giá trị về chuỗi theo đúng cách payOS serialize khi ký.
     */
    private function stringifyValue(mixed $value): string
    {
        if (is_array($value)) {
            // Object lồng nhau được payOS serialize thành "[object Object]".
            if (! array_is_list($value)) {
                return '[object Object]';
            }

            $value = array_map(function ($item) {
                if (is_array($item) && ! array_is_list($item)) {
                    ksort($item);
                }

                return $item;
            }, $value);

            return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_object($value)) {
            return '[object Object]';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // payOS coi null / "null" / "undefined" là chuỗi rỗng khi ký.
        if ($value === null || $value === 'null' || $value === 'undefined') {
            return '';
        }

        return (string) $value;
    }

    /**
     * Gọi API payOS và trả về phần `data` đã được kiểm tra chữ ký.
     *
     * @throws PayOSException
     */
    private function request(string $method, string $uri, ?array $payload = null, bool $verifyResponse = true): array
    {
        if (! $this->isConfigured()) {
            throw new PayOSException('Chưa cấu hình PAYOS_CLIENT_ID / PAYOS_API_KEY / PAYOS_CHECKSUM_KEY.');
        }

        try {
            $response = $this->http()->send($method, $uri, $payload === null ? [] : ['json' => $payload]);
        } catch (Throwable $e) {
            throw new PayOSException('Không kết nối được tới payOS: '.$e->getMessage());
        }

        $body = $response->json();

        if (! is_array($body)) {
            throw new PayOSException('payOS trả về dữ liệu không hợp lệ (HTTP '.$response->status().').');
        }

        $code = isset($body['code']) ? (string) $body['code'] : null;

        if ($code !== self::CODE_SUCCESS) {
            throw new PayOSException(
                $body['desc'] ?? 'payOS trả về lỗi (HTTP '.$response->status().').',
                $code
            );
        }

        $data = $body['data'] ?? null;

        if (! is_array($data)) {
            return [];
        }

        if ($verifyResponse && ! hash_equals($this->signObject($data), (string) ($body['signature'] ?? ''))) {
            throw new PayOSException('Chữ ký phản hồi từ payOS không hợp lệ, dữ liệu có thể đã bị can thiệp.');
        }

        return $data;
    }

    private function http(): PendingRequest
    {
        $headers = [
            'x-client-id' => $this->clientId,
            'x-api-key' => $this->apiKey,
        ];

        if (! empty($this->partnerCode)) {
            $headers['x-partner-code'] = $this->partnerCode;
        }

        return Http::baseUrl($this->baseUrl)
            ->withHeaders($headers)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout($this->timeout);
    }
}
