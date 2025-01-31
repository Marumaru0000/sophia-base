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
    public string $currentTime = '';
    public bool $isConfirmationModalVisible = false;
    public function mount()
{
    $this->currentTime = now()->format('H:i:s');
}

public function updateCurrentTime()
{
    $this->currentTime = now()->format('H:i:s');
}
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
    return $history;
}

private function getMenus(): Collection
{
    try {
        $client = new Client();
        $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
            'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')],
            'query' => [
                'limit' => config('ordering.menu.micro-cms.limit'),
            // 必要なら 'orders' => config('ordering.menu.micro-cms.orders'),
            ],
            ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['contents']) || !is_array($data['contents'])) {
            Log::error('Invalid response from MicroCMS', ['response' => $data]);
            return collect([]);
        }

        return collect($data['contents']);
    } catch (\Exception $e) {
        Log::error('Failed to fetch menus from MicroCMS', ['error' => $e->getMessage()]);
        return collect([]);
    }
}


public function confirmReceipt()
{
    if (empty($this->selectedItems)) {
        $this->isConfirmationModalVisible = true;
        session()->flash('confirmation_data', [
            'error' => '少なくとも1つの商品を選択してください。',
        ]);
        return;
    }

    // 選択されたアイテムを取得
    $selectedItems = collect(session('history', []))->flatMap(function ($history) {
        return collect($history['items'])->whereIn('id', $this->selectedItems);
    })->toArray();

    // 確認画面用データをセッションに保存
    session()->flash('confirmation_data', [
        'items' => $selectedItems,
        'purchase_time' => now()->toDateTimeString(),
    ]);
    $this->isConfirmationModalVisible = true; // モーダル表示
}

public function showConfirmation(array $selectedItemIds)
{
    $this->selectedItems = $selectedItemIds;
    $this->emit('showConfirmationModal'); // フロントエンドでモーダルを表示
}
public function getConfirmationDataProperty(): array
{
    $selectedItems = collect(session('history', []))->flatMap(function ($history) {
        return collect($history['items'])->whereIn('id', $this->selectedItems);
    });

    return [
        'items' => $selectedItems->toArray(),
        'purchase_time' => now()->toDateTimeString(),
    ];
}


public function deleteSelectedItems()
{
    Log::info('Selected Items:', ['selectedItems' => $this->selectedItems]);

    $selectedItemIds = $this->selectedItems;

    $updatedHistories = collect(session('history', []))->map(function ($history) use ($selectedItemIds) {
        // $history['items']が配列であることを確認
        if (isset($history['items']) && is_array($history['items'])) {
            $history['items'] = collect($history['items'])->reject(function ($item) use ($selectedItemIds) {
                // $itemが配列ならIDを確認
                $itemId = is_array($item) ? ($item['id'] ?? null) : $item;
                return in_array($itemId, $selectedItemIds, true);
            })->values()->toArray();
        }
        return $history;
    });

    session()->put('history', $updatedHistories->toArray());

    Log::info('Updated History:', ['history' => session('history')]);

    $this->selectedItems = [];
    session()->flash('message', '選択された商品を受け取り済みにしました。');
}
public function closeConfirmationModal()
{
    $this->deleteSelectedItems(); // 選択アイテムを削除
    $this->isConfirmationModalVisible = false; // モーダル非表示
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