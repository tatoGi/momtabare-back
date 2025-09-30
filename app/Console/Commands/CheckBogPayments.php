<?php

namespace App\Console\Commands;

use App\Models\BogPayment;
use App\Services\Frontend\BogAuthService;
use App\Services\Frontend\BogPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckBogPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bog:check-payments {--hours=24 : Check payments updated in the last N hours} {--status=pending : Filter by status} {--limit=50 : Maximum number of payments to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update the status of pending BOG payments';

    /**
     * @var BogAuthService
     */
    protected $bogAuth;

    /**
     * @var BogPaymentService
     */
    protected $bogPayment;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BogAuthService $bogAuth, BogPaymentService $bogPayment)
    {
        parent::__construct();
        $this->bogAuth = $bogAuth;
        $this->bogPayment = $bogPayment;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $status = $this->option('status');
        $limit = (int) $this->option('limit');

        $this->info("Checking for BOG payments with status '{$status}' from the last {$hours} hours...");

        // Get the access token
        $tokenResult = $this->bogAuth->getAccessToken();
        if (! $tokenResult || empty($tokenResult['access_token'])) {
            $this->error('Failed to authenticate with BOG');

            return 1;
        }

        $accessToken = $tokenResult['access_token'];
        $updated = 0;
        $failed = 0;

        // Get pending payments
        $payments = BogPayment::where('status', $status)
            ->where('updated_at', '>=', now()->subHours($hours))
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get();

        $this->info("Found {$payments->count()} payments to check.");

        foreach ($payments as $payment) {
            try {
                if (empty($payment->bog_order_id)) {
                    $this->warn("Skipping payment {$payment->id} - missing BOG order ID");

                    continue;
                }

                $this->line("Checking status for payment {$payment->id} (BOG Order: {$payment->bog_order_id})...");

                // Get order details from BOG
                $orderDetails = $this->bogPayment->getOrderDetails($accessToken, $payment->bog_order_id);

                if (! $orderDetails) {
                    $this->warn("Failed to get order details for payment {$payment->id}");
                    $failed++;

                    continue;
                }

                $newStatus = $orderDetails['order_status']['key'] ?? 'unknown';

                if ($newStatus !== $payment->status) {
                    // Update the payment status
                    $payment->update([
                        'status' => $newStatus,
                        'response_data' => array_merge((array) $payment->response_data, ['status_check' => $orderDetails]),
                        'updated_at' => now(),
                    ]);

                    $this->info("Updated payment {$payment->id} status from '{$payment->status}' to '{$newStatus}'");
                    $updated++;

                    // Trigger any status-specific logic
                    $this->handleStatusChange($payment, $newStatus, $orderDetails);
                } else {
                    $this->line("No status change for payment {$payment->id} - still '{$newStatus}'");
                }

            } catch (\Exception $e) {
                Log::error('Error checking BOG payment status', [
                    'payment_id' => $payment->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Error processing payment {$payment->id}: ".$e->getMessage());
                $failed++;
            }
        }

        $this->info("\nStatus check complete. Updated: {$updated}, Failed: {$failed}, Total processed: ".$payments->count());

        return 0;
    }

    /**
     * Handle status changes for payments
     *
     * @return void
     */
    protected function handleStatusChange(BogPayment $payment, string $newStatus, array $orderDetails)
    {
        // Add any status-specific logic here
        switch ($newStatus) {
            case 'success':
                // Handle successful payment
                // Example: Update your main order status, send notifications, etc.
                // $this->handleSuccessfulPayment($payment, $orderDetails);
                break;

            case 'failed':
            case 'cancelled':
            case 'expired':
                // Handle failed/cancelled payment
                // $this->handleFailedPayment($payment, $orderDetails);
                break;

            case 'refunded':
                // Handle refunded payment
                // $this->handleRefundedPayment($payment, $orderDetails);
                break;
        }
    }
}
