<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Revolution\Ordering\Contracts\Actions\AddHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AddHistoryAction implements AddHistory
{
    public function add(array $history): void
    {
        $apiKey    = env('AIRTABLE_API_KEY');
        $baseId    = env('AIRTABLE_BASE_ID', 'appZZt8lk3gW6I0Lm');
        $tableName = env('AIRTABLE_TABLE_NAME', 'Orders');

        if (!session()->has('customer_id')) {
            session(['customer_id' => uniqid('cust_', true)]);
        }

        $customerId = session('customer_id');

        foreach ($history['items'] as $item) {
            $category = $item['category'] ?? [];

            if (is_array($category)) {
                if (isset($category[0]['value'])) {
                    // 形式: [['value' => '麺']]
                    $categoryValue = $category[0]['value'];
                } elseif (is_string($category[0])) {
                    // 形式: ['麺']
                    $categoryValue = $category[0];
                } else {
                    $categoryValue = '未分類';
                }
            } elseif (is_string($category)) {
                $categoryValue = $category;
            } else {
                $categoryValue = '未分類';
            }
            

            $payload = [
                'records' => [
                    [
                        'fields' => [
                            'customer_id'     => (string) $customerId,
                            'order_id'        => (string) $history['order_id'],
                            'item_id'         => (string) $item['id'],
                            'item_name'       => (string) $item['name'],
                            'price'           => (float) $item['price'],
                            'selected_option' => (string) ($item['selected_option'] ?? ''),
                            'category'        => $categoryValue,
                            'purchase_time'   => now()->toIso8601String(),
                            'status'          => '未準備',
                            'payment_method'  => (string) ($history['payment'] ?? '未指定'),
                        ]
                    ]
                ]
            ];

            Log::info('Airtable API Request', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type'  => 'application/json',
            ])->post("https://api.airtable.com/v0/{$baseId}/{$tableName}", $payload);

            Log::info('Airtable API Response', [
                'request'  => $payload,
                'response' => $response->json(),
                'status'   => $response->status(),
            ]);

            if (!$response->successful()) {
                Log::error('Airtable API Error', [
                    'status'  => $response->status(),
                    'message' => $response->body(),
                ]);
            }
        }
    }
}
