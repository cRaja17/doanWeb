<?php

namespace App\Console\Commands;

use App\Entities\PayOS;
use App\Exceptions\PayOSException;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Dọn các đơn nằm mãi ở trạng thái "chờ thanh toán".
 *
 * Khách bỏ dở giữa chừng thì đơn vẫn giữ hàng trong kho (xem Order::deductStock)
 * và không bao giờ tự đóng lại - để lâu thì tồn kho hiển thị sai và danh sách
 * đơn đầy rác. Lệnh này đóng chúng lại và trả hàng về kho.
 *
 * Trước khi huỷ, đơn payOS còn được hỏi lại cổng thanh toán một lần cuối: nếu
 * webhook lúc trước bị rớt mà tiền thực ra đã về thì phải ghi nhận đã thanh
 * toán chứ không được huỷ.
 */
class CancelStalePendingOrders extends Command
{
    protected $signature = 'orders:cancel-stale
                            {--hours=24 : Đơn chờ thanh toán lâu hơn số giờ này thì bị đóng}
                            {--dry-run : Chỉ liệt kê, không thay đổi gì}';

    protected $description = 'Đóng các đơn chờ thanh toán quá hạn và trả hàng đã giữ về kho';

    /** Cổng thanh toán xác nhận đơn này thực ra đã có tiền. */
    private const VERDICT_PAID = 'paid';

    /** Không hỏi được cổng thanh toán - chưa dám kết luận. */
    private const VERDICT_UNKNOWN = 'unknown';

    /** Chắc chắn chưa thanh toán, đóng đơn được. */
    private const VERDICT_UNPAID = 'unpaid';

    public function handle(PayOS $payOS): int
    {
        $hours = max((int) $this->option('hours'), 1);
        $dryRun = (bool) $this->option('dry-run');

        $orders = Order::query()
            ->where('status', Order::STATUS_PENDING)
            ->where('created_at', '<', now()->subHours($hours))
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info("Không có đơn nào chờ thanh toán quá {$hours} giờ.");

            return self::SUCCESS;
        }

        $this->info("Tìm thấy {$orders->count()} đơn chờ thanh toán quá {$hours} giờ.");

        $cancelled = 0;
        $recovered = 0;
        $skipped = 0;

        foreach ($orders as $order) {
            $verdict = $this->checkGateway($order, $payOS, $dryRun);

            if ($verdict === self::VERDICT_PAID) {
                $recovered++;
                $this->warn("  {$order->transaction_code}: thực tế ĐÃ thanh toán, không huỷ.");

                continue;
            }

            if ($verdict === self::VERDICT_UNKNOWN) {
                $skipped++;
                $this->warn("  {$order->transaction_code}: không hỏi được cổng thanh toán, để lại lần sau.");

                continue;
            }

            if ($dryRun) {
                $this->line("  {$order->transaction_code}: sẽ bị huỷ.");
                $cancelled++;

                continue;
            }

            if ($order->markAsCancelled()) {
                $cancelled++;
                $this->line("  {$order->transaction_code}: đã huỷ, hàng đã trả về kho.");
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "Thử nghiệm: sẽ huỷ {$cancelled} đơn, giữ lại {$recovered} đơn đã thanh toán."
            : "Đã huỷ {$cancelled} đơn, ghi nhận {$recovered} đơn thực ra đã thanh toán.");

        if ($skipped > 0) {
            $this->warn("{$skipped} đơn chưa xử lý được do không hỏi được cổng thanh toán - lần chạy sau sẽ thử lại.");
        }

        return self::SUCCESS;
    }

    /**
     * Hỏi payOS xem đơn này thực ra đã được trả tiền chưa (phòng khi webhook rớt).
     *
     * @return self::VERDICT_*
     */
    private function checkGateway(Order $order, PayOS $payOS, bool $dryRun): string
    {
        $reference = $order->payos_payment_link_id ?: $order->payos_order_code;

        // Đơn không qua payOS (PayPal, COD) thì không có gì để tra cứu.
        if (!$reference || !$payOS->isConfigured()) {
            return self::VERDICT_UNPAID;
        }

        try {
            $link = $payOS->getPaymentLink($reference);
        } catch (PayOSException $e) {
            // Mạng lỗi hay payOS trục trặc: để đơn lại cho lần chạy sau, an toàn
            // hơn là huỷ một đơn có thể đã có tiền.
            Log::info('orders:cancel-stale - không tra cứu được link payOS', [
                'order' => $order->transaction_code,
                'message' => $e->getMessage(),
            ]);

            return self::VERDICT_UNKNOWN;
        }

        if (($link['status'] ?? null) !== PayOS::STATUS_PAID) {
            return self::VERDICT_UNPAID;
        }

        if ((int) ($link['amountPaid'] ?? 0) < (int) round((float) $order->total)) {
            return self::VERDICT_UNPAID;
        }

        if (!$dryRun) {
            $transaction = $link['transactions'][0] ?? [];
            $order->markAsPaid($transaction['reference'] ?? null, $transaction['transactionDateTime'] ?? null);

            Log::warning('orders:cancel-stale - đơn đã thanh toán nhưng chưa được ghi nhận', [
                'order' => $order->transaction_code,
            ]);
        }

        return self::VERDICT_PAID;
    }
}
