<?php

declare(strict_types=1);

namespace Revolution\Ordering\Payment\PayPay;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PayPay\OpenPaymentAPI\Models\ModelException;
use PayPay\OpenPaymentAPI\Models\OrderItem;

class CreateOrderItem
{
    /**
     * @param  array  $menu
     * @return OrderItem
     *
     * @throws ModelException
     */
    public function __invoke(array $menu): OrderItem
    {
        // categoryを取得して、選択されたvalueを取り出してカンマ区切りの文字列にする
        $category = Arr::get($menu, 'category', []);

        // 'category' が配列であれば、valueを取り出してカンマ区切りで結合
        if (is_array($category) && !empty($category)) {
            // 配列からvalueを取り出して、カンマ区切りで文字列に結合
            $categoryValues = implode(',', collect($category)->pluck('value')->toArray());
        } else {
            $categoryValues = 'Unknown'; // 'category'が配列でない場合のデフォルト
        }

        // OrderItemのインスタンスを返す
        return app(OrderItem::class)
            ->setName(Str::limit(Arr::get($menu, 'name'), 150))
            ->setCategory(Str::limit($categoryValues, 255))  // 修正後のcategoryをセット
            ->setProductId(Str::limit((string) Arr::get($menu, 'id'), 255))
            ->setQuantity(1)
            ->setUnitPrice([
                'amount' => (int) Arr::get($menu, 'price'),
                'currency' => config('paypay.currency', 'JPY'),
            ]);
    }
}