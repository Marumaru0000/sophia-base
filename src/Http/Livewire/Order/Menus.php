<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Redirector;
use Revolution\Ordering\Contracts\Actions\AddCart;
use Revolution\Ordering\Contracts\Actions\ResetCart;
use Revolution\Ordering\Facades\Cart;
use GuzzleHttp\Client;
use Revolution\Ordering\Facades\Menu;

class Menus extends Component
{
    /**
     * @var Collection
     */
    public Collection $menus;

    /**
     * @param  Request  $request
     */
    public function mount(Request $request)
    {
        $client = new Client();
        $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
            'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')],
            'query' => [
                'limit' => config('ordering.menu.micro-cms.limit'),
            ]
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data['contents'])) {
            $this->menus = collect($data['contents'])->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['name'] ?? '名前がありません',
                    'price' => $item['price'] ?? 0,
                    'description' => $item['description'] ?? '',
                    'image' => $item['image']['url'] ?? config('ordering.menu.no_image'),
                    'is_available' => $item['is_available'] ?? false,
                    'category' => $item['category'] ?? ['未分類'],

                    // ここでライス・麺のオプションも格納
                    'options' => [
                        'rice' => $item['rice_options'] ?? null,    // "ライス大盛り"など
                        'noodle' => $item['noodle_options'] ?? null, // "麺大盛り"など
                    ],

                    // ★ 変更点: 後からユーザが選択したオプションを格納するためのフィールド
                    'selected_option' => null,
                ];
            });
        } else {
            $this->menus = collect([]);
        }
    }

    /**
     * カートアイテム表示用
     */
    public function getItemsProperty(): Collection
    {
        return Cart::items(Cart::all(), $this->getMenus());
    }

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
     * カートに追加.
     *
     * @param  string|int  $id
     */
    public function addCart($id)
    {
        app(AddCart::class)->add($id);
    }

    /**
     * カートをリセット.
     */
    public function resetCart()
    {
        app(ResetCart::class)->reset();
    }

    /**
     * 次のページに移動.
     *
     * @return RedirectResponse|Redirector
     */
    public function redirectTo()
    {
        return redirect()->route(config('ordering.redirect.from_menus'));
    }

    public function render()
    {
        return view()->first([
            'ordering-theme::livewire.order.menus',
            'ordering::livewire.order.menus',
        ]);
    }
}
