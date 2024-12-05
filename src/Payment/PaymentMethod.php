<?php

declare(strict_types=1);

namespace Revolution\Ordering\Payment;

use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Payment\Concerns\WithPaymentMethodCollection;

class PaymentMethod implements PaymentMethodFactory
{
    use WithPaymentMethodCollection;
    use Macroable;

    /**
     * @return Collection
     */
    public function methods(): Collection
    {
        return collect([
            'paypay' => 'PayPay',
            // 他の有効な支払い方法を追加可能
        ]);
    }
}
