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
    $category = Arr::get($menu, 'category', []);

    if (is_array($category)) {
        // ['麺'] or [['value' => '麺']] の両対応
        $first = $category[0] ?? null;

        if (is_array($first) && isset($first['value']) && !empty($first['value'])) {
            $categoryValue = $first['value'];
        } elseif (is_string($first) && !empty($first)) {
            $categoryValue = $first;
        } else {
            $categoryValue = '未分類';
        }
    } elseif (is_string($category) && !empty($category)) {
        $categoryValue = $category;
    } else {
        $categoryValue = '未分類';
    }

    return app(OrderItem::class)
        ->setName(Str::limit(Arr::get($menu, 'name'), 150))
        ->setCategory(Str::limit($categoryValue, 255))
        ->setProductId(Str::limit((string) Arr::get($menu, 'id'), 255))
        ->setQuantity(1)
        ->setUnitPrice([
            'amount' => (int) Arr::get($menu, 'price'),
            'currency' => config('paypay.currency', 'JPY'),
        ]);
}

}