<?php

declare(strict_types=1);

namespace Revolution\Ordering\Payment\PayPay;

use Illuminate\Support\Facades\Log;
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
        try {
            $payload = $this->payload();
            // ログにペイロードを記録
            Log::info('PayPay API Request Payload: ', ['payload' => $payload]);

            $response = PayPay::code()->createQRCode($payload);
            // ログにAPIレスポンスを記録
            Log::info('PayPay API Response: ', ['response' => $response]);

            return $response;
        } catch (\Exception $e) {
            // エラー発生時のログ
            Log::error('PayPay API Error: ', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // エラーを再スロー
        }
    }

    /**
     * @return CreateQrCodePayload
     *
     * @throws ModelException
     */
    protected function payload(): CreateQrCodePayload
{
    $items = Cart::items();

    Log::info('[PayPay] Raw Cart Items', $items->toArray());

    if ($items->isEmpty()) {
        throw new \Exception('Cart is empty, cannot create QR code.');
    }

    // デフォルト値を持つアイテムの生成
    $itemsWithDefaults = $items->map(function ($item) {
        $category = $item['category'] ?? [];

        if (is_array($category) && !empty($category)) {
            $categoryValues = implode(',', collect($category)->pluck('value')->toArray());
        } else {
            $categoryValues = 'Unknown'; // デフォルト値
        }

        return array_merge($item, [
            'category' => $categoryValues,
            'quantity' => $item['quantity'] ?? 1,
        ]);
    });

    Log::info('[PayPay] Items with Defaults', $itemsWithDefaults->toArray());

    // QRコードペイロードの作成
    $payload = $this->createQrCodePayload();

    $amount = $items->sum('price');
    Log::info('[PayPay] Total amount', ['amount' => $amount]);

    $payload->setAmount([
        'amount' => $amount,
        'currency' => config('paypay.currency', 'JPY'),
    ]);

    $orderItems = $itemsWithDefaults->map(app(CreateOrderItem::class))->toArray();

    Log::info('[PayPay] Order Items', $orderItems);

    $payload->setOrderItems($orderItems);

    $description = config('ordering.payment.paypay.order_description', ' ');
    Log::info('[PayPay] Order Description', ['description' => $description]);

    $payload->setOrderDescription($description);

    Log::info('[PayPay] Final Payload Object', [
        'payload' => json_decode(json_encode($payload), true)
    ]);

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
