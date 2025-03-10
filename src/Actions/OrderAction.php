<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Illuminate\Support\Arr;
use Revolution\Ordering\Contracts\Actions\AddHistory;
use Revolution\Ordering\Contracts\Actions\Order;
use Revolution\Ordering\Contracts\Actions\ResetCart;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Events\OrderEntry;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Support\OrderId;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // ← これを追加！

class OrderAction implements Order
{
    /**
     * @param  null|array  $options
     * @return void
     */
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

    $apiKey = env('AIRTABLE_API_KEY');
    $baseId = env('AIRTABLE_BASE_ID');
    $tableName = env('AIRTABLE_TABLE_NAME');

    $records = [];
    foreach ($items as $item) {
        $records[] = [
            'fields' => [
                'customer_id' => (string) $customerId,
                'order_id' => (string) $order_id,
                'item_id' => (string) $item['id'],
                'item_name' => (string) $item['name'],
                'price' => (float) $item['price'],
                'selected_option' => (string) ($item['selected_option'] ?? ''),
                'purchase_time' => $date,
                'status' => '未受取',
                'payment_method' => (string) $payment,
            ]
        ];
    }

    Log::info('Airtable 送信データ:', ['records' => $records]);

    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->post("https://api.airtable.com/v0/{$baseId}/{$tableName}", ['records' => $records]);

    Log::info('Airtable API Response:', [
        'response' => $response->json(),
        'status' => $response->status(),
    ]);

    if (!$response->successful()) {
        Log::error('Airtable API Error:', [
            'status' => $response->status(),
            'message' => $response->body(),
        ]);
        return;
    }

    // session に履歴を保存する処理を削除
    app(ResetCart::class)->reset();
    session()->flash('order_completed_message', config('ordering.shop.order_completed_message'));
}

}
