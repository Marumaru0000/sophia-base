<?php

declare(strict_types=1);

namespace Revolution\Ordering\Payment\PayPay;

use Illuminate\Support\Str;
use PayPay\OpenPaymentAPI\Controller\ClientControllerException;
use PayPay\OpenPaymentAPI\Models\CreateQrCodePayload;
use PayPay\OpenPaymentAPI\Models\ModelException;
use Revolution\Ordering\Facades\Cart;
use Revolution\PayPay\Facades\PayPay;

class CreateQrCode
{
    /**
     * @return array
     *
     * @throws ClientControllerException
     * @throws ModelException
     */
    public function __invoke(): array
    {
        return PayPay::code()->createQRCode($this->payload());
    }

    /**
     * @return CreateQrCodePayload
     *
     * @throws ModelException
     */
    protected function payload(): CreateQrCodePayload
    {
        $items = Cart::items();

        $itemsWithDefaults = $items->map(function ($item) {
            $category = $item['category'] ?? [];
        
            // category が空の場合、デフォルト値を設定
            if (is_array($category) && !empty($category)) {
                $categoryValues = implode(',', collect($category)->pluck('value')->toArray());
            } else {
                $categoryValues = 'Other'; // デフォルト値
            }
        
            return array_merge($item, [
                'category' => $categoryValues,
                'quantity' => $item['quantity'] ?? 1, // デフォルトの数量
            ]);
        });
        

        $payload = $this->createQrCodePayload();

        $payload->setAmount([
            'amount' => $items->sum('price'),
            'currency' => config('paypay.currency', 'JPY'),
        ]);

        $payload->setOrderItems(
            $itemsWithDefaults->map(app(CreateOrderItem::class))->toArray()
        );

        $payload->setOrderDescription(config('ordering.payment.paypay.order_description', ' '));

        return $payload;
    }

    /**
     * @return CreateQrCodePayload
     *
     * @throws ModelException
     */
    protected function createQrCodePayload(): CreateQrCodePayload
    {
        $merchantPaymentId = Str::limit(app(MerchantPaymentId::class)->create(), 64);

        return app(CreateQrCodePayload::class)
            ->setMerchantPaymentId($merchantPaymentId)
            ->setRedirectType('WEB_LINK')
            ->setRedirectUrl(route('paypay.callback', ['payment' => $merchantPaymentId]))
            ->setRequestedAt()
            ->setCodeType();
    }
}
