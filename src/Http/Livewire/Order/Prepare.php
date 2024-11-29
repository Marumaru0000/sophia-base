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
    public string $payment_method = 'cash';

    public function mount()
    {
        $this->payments = app(PaymentMethodFactory::class)->methods();
    }

    /**
     * @return Collection
     */
    public function getItemsProperty(): Collection
    {
    return Cart::items(Cart::all(), $this->getMenus());
    }

    private function getMenus(): Collection
    {
    // Menusコンポーネントと同様にAPIを使ってデータを取得
    $client = new Client();
    $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
        'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')]
    ]);
    $data = json_decode($response->getBody()->getContents(), true);
    return collect($data['contents'] ?? []);
    }
    /**
     * カートから削除.
     *
     * @param  int  $index
     */
    public function deleteCart(int $index)
    {
        Cart::delete($index);
    }

    /**
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
     * @return RedirectResponse|Redirector
     */
    public function redirectTo()
    {
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