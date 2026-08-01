<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncNitaPaymentStatuses extends Command
{
    protected $signature = 'nita:sync-payment-status {--limit=10 : Maximum number of orders to process per run}';

    protected $description = 'Check pending NITA orders and mark them as paid when the remote API confirms payment';

    public function handle(): int
    {
        $config = config('services.nita');
        $limit = max(1, (int) $this->option('limit'));

        // Log::info('Starting NITA payment status sync', ['limit' => $limit]);

        $requiredKeys = [
            'api_key' => $config['api_key'] ?? null,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'authenticate_url' => $config['authenticate_url'] ?? null,
            'check_status_url' => $config['check_status_url'] ?? null,
        ];

        foreach ($requiredKeys as $key => $value) {
            if (blank($value)) {
                $this->error("Missing NITA configuration value: {$key}");
                return self::FAILURE;
            }
        }

        $orders = Order::query()
            ->where('payment_status', 'pending')
            ->where('order_status', '!=', 'cancelled')
            ->whereNotNull('request_id')
            ->whereNotNull('reference_code')
            ->where('request_id', '!=', '')
            ->where('reference_code', '!=', '')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No eligible orders found for NITA sync.');
            // Log::info('NITA sync skipped because no eligible orders were found.');
            return self::SUCCESS;
        }

        // Log::info('NITA auth request starting', [
        //     'url' => $config['authenticate_url'],
        //     'username' => $config['username'],
        // ]);

        $authResponse = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'X-NT-API-KEY' => $config['api_key'],
            ])
            ->timeout(30)
            ->post($config['authenticate_url'], [
                'username' => $config['username'],
                'password' => $config['password'],
            ]);

        // Log::info('NITA auth response received', [
        //     'status' => $authResponse->status(),
        //     'successful' => $authResponse->successful(),
        //     'body' => $authResponse->body(),
        // ]);

        if (! $authResponse->successful()) {
            $this->error('NITA authentication request failed.');
            Log::warning('NITA authentication failed', [
                'status' => $authResponse->status(),
                'body' => $authResponse->body(),
            ]);

            return self::FAILURE;
        }

        $token = data_get($authResponse->json(), 'data.token');

        if (blank($token)) {
            $this->error('NITA authentication token was not returned.');
            Log::warning('NITA authentication response missing token', [
                'body' => $authResponse->json(),
            ]);

            return self::FAILURE;
        }

        $paidCount = 0;
        $pendingCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            Log::info('NITA status check request starting', [
                'order_id' => $order->id,
                'request_id' => $order->request_id,
                'url' => $config['check_status_url'],
            ]);

            $checkResponse = Http::acceptJson()
                ->asJson()
                ->withHeaders([
                    'X-NT-API-KEY' => $config['api_key'],
                ])
                ->withToken($token)
                ->timeout(30)
                ->post($config['check_status_url'], [
                    'requestId' => $order->request_id,
                    'longTransaction' => $config['long_transaction'] ?? '2.0301',
                    'latTransaction' => $config['lat_transaction'] ?? '13.5123',
                    'adresseIp' => $config['ip_address'] ?? '127.0.0.1',
                ]);

            // Log::info('NITA status check response received', [
            //     'order_id' => $order->id,
            //     'request_id' => $order->request_id,
            //     'status' => $checkResponse->status(),
            //     'successful' => $checkResponse->successful(),
            //     'body' => $checkResponse->body(),
            // ]);

            if (! $checkResponse->successful()) {
                $failedCount++;
                $this->warn("Order #{$order->id}: status check failed.");
                Log::warning('NITA status check failed', [
                    'order_id' => $order->id,
                    'request_id' => $order->request_id,
                    'status' => $checkResponse->status(),
                    'body' => $checkResponse->body(),
                ]);
                continue;
            }

            $remoteCode = (string) data_get($checkResponse->json(), 'data.code');
            // Log::info('NITA status code parsed', [
            //     'order_id' => $order->id,
            //     'request_id' => $order->request_id,
            //     'remote_code' => $remoteCode,
            // ]);

            if ($remoteCode === '1') {
                $order->forceFill([
                    'payment_status' => 'paid',
                ])->save();

                $paidCount++;
                $this->info("Order #{$order->id} marked as paid.");
                continue;
            }

            if ($remoteCode === '0') {
                $pendingCount++;
                $this->line("Order #{$order->id} is still pending.");
                continue;
            }

            $failedCount++;
            $this->warn("Order #{$order->id}: unexpected NITA code '{$remoteCode}'.");
            // Log::warning('NITA status check returned unexpected code', [
            //     'order_id' => $order->id,
            //     'request_id' => $order->request_id,
            //     'status' => $checkResponse->status(),
            //     'response' => $checkResponse->json(),
            // ]);
        }

        $this->info(sprintf(
            'NITA sync complete. Paid: %d, still pending: %d, failed: %d.',
            $paidCount,
            $pendingCount,
            $failedCount
        ));

        // Log::info('NITA sync completed', [
        //     'paid' => $paidCount,
        //     'pending' => $pendingCount,
        //     'failed' => $failedCount,
        //     'processed' => $orders->count(),
        // ]);

        return self::SUCCESS;
    }
}
