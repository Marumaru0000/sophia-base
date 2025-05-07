<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Illuminate\Support\Arr;
use Revolution\Ordering\Contracts\Actions\Order;
use Revolution\Ordering\Contracts\Actions\ResetCart;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Support\OrderId;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderAction implements Order
{
    public function order(array $options = null): void
    {
        $items = Cart::items()->toArray();
        $memo = session('memo');

        if (empty($items)) {
            Log::error('注文エラー: カートが空です');
            return;
        }

        $order_id = $options['order_id'] ?? app(OrderId::class)->create();
        $date = now()->toIso8601String();
        $payment = app(PaymentMethodFactory::class)->name($options['payment'] ?? 'cash');

        if (!session()->has('customer_id')) {
            session(['customer_id' => uniqid('cust_', true)]);
        }

        $customerId = session('customer_id');
        $apiKey     = env('AIRTABLE_API_KEY');
        $baseId     = env('AIRTABLE_BASE_ID');
        $tableName  = env('AIRTABLE_TABLE_NAME');

        $records = [];

        foreach ($items as $item) {
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
                  

            $records[] = [
                'fields' => [
                    'customer_id'     => (string) $customerId,
                    'order_id'        => (string) $order_id,
                    'item_id'         => (string) $item['id'],
                    'item_name'       => (string) $item['name'],
                    'price'           => (float) $item['price'],
                    'selected_option' => (string) ($item['selected_option'] ?? ''),
                    'category'        => $categoryValue,
                    'purchase_time'   => $date,
                    'status'          => '未準備',
                    'payment_method'  => (string) $payment,
                ]
            ];
        }

        Log::info('[DEBUG] Airtable 送信レコード:', ['records' => $records]);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
        ])->post("https://api.airtable.com/v0/{$baseId}/{$tableName}", ['records' => $records]);

        Log::info('Airtable API Response:', [
            'response' => $response->json(),
            'status'   => $response->status(),
        ]);

        if (!$response->successful()) {
            Log::error('Airtable API Error:', [
                'status'  => $response->status(),
                'message' => $response->body(),
            ]);
            return;
        }

        app(ResetCart::class)->reset();
        session()->flash('order_completed_message', config('ordering.shop.order_completed_message'));
    }
}
