<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\NetworkPaymentService;
use App\Services\NetworkPaymentSettlementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNetworkPaymentsCommand extends Command
{
    protected $signature = 'app:sync-network-payments';

    protected $description = 'Reconcile non-success Network payments older than 2 minutes against the gateway';

    public function handle(NetworkPaymentService $networkPayment, NetworkPaymentSettlementService $settlement): int
    {
        $payments = Payment::with(['items'])
            ->where('status', '<>', 'success')
            ->where('created_at', '<', now()->subMinutes(1))
            ->where('payment_method', 'network')
            ->whereNotNull('order_id')
            ->orderBy('id', 'desc')
            ->get();

        $settled = 0;

        foreach ($payments as $payment) {
            try {
                $settledIncrement = DB::transaction(function () use ($payment, $networkPayment, $settlement): int {
                    $locked = Payment::query()
                        ->whereKey($payment->id)
                        ->where('status', '<>', 'success')
                        ->lockForUpdate()
                        ->first();

                    if (! $locked) {
                        return 0;
                    }

                    $result = $networkPayment->getOrderDetails($locked->order_id);

                    if (! $settlement->orderIsCaptured($result)) {
                        return 0;
                    }

                    $locked->loadMissing('items');
                    $settlement->applyCapturedOrder($locked, $result, false);

                    return 1;
                });

                $settled += $settledIncrement;
            } catch (\Throwable $e) {
                Log::error('Sync network payment failed', [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($settled > 0) {
            $this->info("Settled {$settled} Network payment(s) from gateway.");
        }

        return self::SUCCESS;
    }
}
