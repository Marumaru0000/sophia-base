<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Redirector;
use GuzzleHttp\Client;
use Revolution\Ordering\Contracts\Payment\PaymentMethodFactory;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Facades\Payment;

class Prepare extends Component
{
    /**
     * @var string
     */
    public string $memo = '';

    /**
     * @var Collection
     */
    public Collection $payments;

    /**
     * @var string
     */
    public string $payment_method = 'paypay';

    public function mount()
    {
        $this->payments = app(PaymentMethodFactory::class)->methods();
    }

    /**
     * カートアイテム一覧
     */
    public function getItemsProperty(): Collection
{
    return Cart::items(Cart::all(), $this->getMenus());
}


    /**
     * microCMSからメニューを再取得
     */
    private function getMenus(): Collection
    {
        $client = new Client();
        $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
            'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')],
            'query' => [
                'limit' => config('ordering.menu.micro-cms.limit'),
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return collect($data['contents'] ?? []);
    }

    /**
     * 変更点: オプション更新メソッド
     */
    public function updateOption($index, $selectedOption)
{
    // Cart::update($index, ['selected_option' => xxx]) でカートを更新
    Cart::update($index, [
        'selected_option' => $selectedOption,
    ]);
}


    /**
     * カートから削除
     */
    public function deleteCart($index)
    {
        $index = (int) $index;
        Cart::delete($index);
    }

    /**
     * 前の画面に戻る
     *
     * @return RedirectResponse|Redirector
     */
    public function back()
    {
        return redirect()->route('order', ['table' => session('table')]);
    }

    /**
     * @param  string  $memo
     */
    public function updatedMemo(string $memo)
    {
        session(['memo' => $memo]);
    }

    /**
     * 注文確定して支払いに進む
     *
     * @return RedirectResponse|Redirector
     */
    public function redirectTo()
    {
        if (empty($this->payment_method)) {
            session()->flash('payment_redirect_error', '支払い方法を選択してください。');
            return;
        }

        return Payment::driver($this->payment_method)->redirect();
    }

    public function render()
    {
        return view()->first([
            'ordering-theme::livewire.order.prepare',
            'ordering::livewire.order.prepare',
        ]);
    }
}
