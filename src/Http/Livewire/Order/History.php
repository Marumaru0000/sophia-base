<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Redirector;
use GuzzleHttp\Client;
use Revolution\Ordering\Facades\Cart;
use Revolution\Ordering\Facades\Menu;
use Illuminate\Support\Facades\Log;

class History extends Component
{
    public array $selectedItems = [];

    /**
     * @var Collection
     */
    protected Collection $menus;

    public function boot()
    {
        $this->menus = Collection::wrap(Menu::get());
    }

    /**
     * @return Collection
     */
    public function getHistoriesProperty(): Collection
    {
        return collect(session('history', []))->map([$this, 'replaceHistoryItems']);
    }

    /**
     * @param  array  $history
     * @return array
     */
    public function replaceHistoryItems(array $history): array
{
    $menus = $this->getMenus();

    // 'items'が適切でない場合に空配列として扱う
    $items = is_array($history['items'] ?? null) ? $history['items'] : [];
    $history['items'] = collect($items)->map(function ($itemId) use ($menus) {
        return $menus->firstWhere('id', $itemId) ?? ['id' => $itemId, 'name' => '不明な商品', 'price' => 0];
    })->toArray();

    return $history;
}
    private function getMenus(): Collection
    {
        $client = new Client();
        $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
            'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        return collect($data['contents'] ?? []);
    }

    public function confirmReceipt()
{
    Log::info('Selected Items (before filter):', ['selectedItems' => $this->selectedItems]);

    $selectedItemIds = $this->selectedItems;
    if (empty($selectedItemIds)) {
        session()->flash('message', '少なくとも1つの商品を選択してください。');
        return;
    }

    // テストとして、直接deleteSelectedItems()を呼んでみる
    $this->deleteSelectedItems();
}


    public function deleteSelectedItems()
{
    Log::info('Selected Items:', ['selectedItems' => $this->selectedItems]);

    $selectedItemIds = $this->selectedItems; 
    Log::info('Selected Item IDs:', ['selectedItemIds' => $selectedItemIds]);

    $updatedHistories = collect(session('history', []))->map(function ($history) use ($selectedItemIds) {
        if (isset($history['items']) && is_array($history['items'])) {
            // $history['items']は単純なIDの配列として保存されているため、
            // $itemは文字列ID。
            $history['items'] = collect($history['items'])->reject(function ($item) use ($selectedItemIds) {
                // $itemは'item_id'のような文字列なのでそのまま比較。
                return in_array($item, $selectedItemIds);
            })->values()->toArray();
        }
        return $history;
    });

    session()->put('history', $updatedHistories->toArray());

    Log::info('Updated History:', ['history' => session('history')]);

    $this->selectedItems = [];
    session()->flash('message', '選択された商品を受け取り済みにしました。');
}


    public function deleteHistory(): void
    {
        session()->forget('history');
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function back()
    {
        return redirect()->route('order');
    }

    public function render()
    {
        return view()->first([
            'ordering-theme::livewire.order.history',
            'ordering::livewire.order.history',
        ]);
    }
}