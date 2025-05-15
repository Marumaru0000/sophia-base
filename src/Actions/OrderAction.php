<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Revolution\Ordering\Contracts\Actions\Order as OrderContract;
use Revolution\Ordering\Contracts\Actions\ResetCart;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Support\OrderId;

class OrderAction implements OrderContract
{
    /**
     * @param  array|null  $options
     */
    public function order(array $options = null): void
    {
        // 1) カート内アイテム取得
        $items = Cart::items()->toArray();
        if (empty($items)) {
            Log::error('注文エラー: カートが空です');
            return;
        }

        // 2) 注文ID と 支払い方法
        $orderId = $options['order_id'] ?? app(OrderId::class)->create();
        $payment = app(PaymentMethodFactory::class)
            ->name($options['payment'] ?? 'cash');
        $timestamp = now()->toIso8601String();

        // 3) ログインユーザー情報（LINEログイン済み）
        $user       = Auth::guard('customer')->user();
        $lineUserId = $user?->line_user_id;
        $customerId = (string) ($user?->id ?? uniqid('cust_', true));

        // 4) Airtable 環境変数
        $apiKey    = env('AIRTABLE_API_KEY');
        $baseId    = env('AIRTABLE_BASE_ID');
        $tableName = env('AIRTABLE_TABLE_NAME');

        // 5) レコード成形
        $records = [];
        foreach ($items as $item) {
            // カテゴリの先頭要素を取り出し
            $categoryRaw = $item['category'] ?? [];
            $categoryValue = '未分類';
            if (is_array($categoryRaw) && isset($categoryRaw[0])) {
                $first = $categoryRaw[0];
                $categoryValue = is_array($first) && isset($first['value'])
                    ? $first['value']
                    : (is_string($first) ? $first : '未分類');
            } elseif (is_string($categoryRaw)) {
                $categoryValue = $categoryRaw;
            }

            $records[] = [
                'fields' => [
                    'line_user_id'    => $lineUserId,
                    'customer_id'     => $customerId,
                    'order_id'        => $orderId,
                    'item_id'         => (string) $item['id'],
                    'item_name'       => (string) $item['name'],
                    'price'           => (float) $item['price'],
                    'selected_option' => (string) ($item['selected_option'] ?? ''),
                    'category'        => $categoryValue,
                    'purchase_time'   => $timestamp,
                    'status'          => '未準備',
                    'payment_method'  => $payment,
                ],
            ];
        }

        Log::info('[DEBUG] Airtable 送信レコード', ['records' => $records]);

        // 6) Airtable へポスト
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
        ])->post(
            "https://api.airtable.com/v0/{$baseId}/{$tableName}",
            ['records' => $records]
        );

        Log::info('Airtable API Response', [
            'status'   => $response->status(),
            'response' => $response->json(),
        ]);

        if (! $response->successful()) {
            Log::error('Airtable API Error', [
                'status'  => $response->status(),
                'message' => $response->body(),
            ]);
            return;
        }

        // 7) カートリセット＆完了メッセージ
        app(ResetCart::class)->reset();
        session()->flash(
            'order_completed_message',
            config('ordering.shop.order_completed_message')
        );
    }
}
