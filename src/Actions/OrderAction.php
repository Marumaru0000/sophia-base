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

class OrderAction implements Order
{
    /**
     * @param  null|array  $options
     * @return void
     */
    public function order(array $options = null): void
    {
        // カートの最終状態（オプション込み）を取得
        // -> ここで「不明商品」にならないよう、Cart::items() でメニュー名やオプションを反映した情報を取得する
        $items = Cart::items()->toArray();

        $memo = session('memo');

        // カートをリセットする前に $items を確保しておく
        app(ResetCart::class)->reset();

        if (empty($items)) {
            return;
        }

        $order_id = $options['order_id'] ?? app(OrderId::class)->create();
        $date = now()->toDateTimeString();

        // 支払い方法
        $payment = app(PaymentMethodFactory::class)->name($options['payment'] ?? 'cash');

        // 履歴へ保存 (AddHistoryAction)
        app(AddHistory::class)->add([
            'order_id' => $order_id,
            'date'     => $date,
            'items'    => $items,  // <= ここが最終的なアイテム配列
            'memo'     => $memo,
            'payment'  => $payment,
        ]);

        // もしイベントを飛ばす必要がある場合はお好みで
        OrderEntry::dispatch(
            $order_id,
            $items,  // 既に merged済みのアイテム情報を引き渡す
            null,
            $memo,
            $options,
        );

        // フラッシュメッセージなど
        session()->flash('order_completed_message', config('ordering.shop.order_completed_message'));
    }
}
