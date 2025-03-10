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
use Illuminate\Support\Facades\Http;

class History extends Component
{
    public array $selectedItems = [];
    public string $currentTime = '';
    public bool $isConfirmationModalVisible = false;
    
    public function mount()
{
    $this->currentTime = now()->format('H:i:s');
    // 顧客 ID をセッションに保存する（ログイン機能がないため）
    if (!session()->has('customer_id')) {
        session(['customer_id' => uniqid('cust_', true)]);
    }
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
    $apiKey = env('AIRTABLE_API_KEY');
    $baseId = env('AIRTABLE_BASE_ID');
    $tableName = env('AIRTABLE_TABLE_NAME');
    $customerId = session('customer_id');

    Log::info("Fetching history for customer_id: {$customerId}");

    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
    ])->get("https://api.airtable.com/v0/{$baseId}/{$tableName}", [
        'filterByFormula' => "{customer_id} = '{$customerId}'"
    ]);

    Log::info('Airtable 履歴取得レスポンス:', ['response' => $response->json()]);

    if (!$response->successful()) {
        Log::error('Airtable API 履歴取得エラー:', [
            'status' => $response->status(),
            'message' => $response->body(),
        ]);
        return collect([]);
    }

    $records = $response->json()['records'] ?? [];
    $menus = $this->getMenus();

    return collect($records)->map(function ($record) use ($menus) {
        $menu = $menus->firstWhere('id', $record['fields']['item_id']) ?? [];
        $image = $menu['image'] ?? config('ordering.menu.no_image');
        return [
            'id' => $record['id'],
            'order_id' => $record['fields']['order_id'] ?? '',
            'customer_id' => $record['fields']['customer_id'] ?? '',
            'item_id' => $record['fields']['item_id'] ?? '',
            'item_name' => $record['fields']['item_name'] ?? '',
            'price' => $record['fields']['price'] ?? 0,
            'selected_option' => $record['fields']['selected_option'] ?? '',
            'purchase_time' => $record['fields']['purchase_time'] ?? '',
            'status' => $record['fields']['status'] ?? '未受取',
            'payment_method' => $record['fields']['payment_method'] ?? 'PayPay',
            'image' => $image,
        ];
    })->filter(fn($history) => $history['status'] !== '受取済み'); // 受取済みの履歴を除外
}

    /**
     * @param  array  $history
     * @return array
     */
    

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
        session()->flash('message', '受け取りたい商品を選択してください。');
        return; // モーダルを開かないように修正
    }

    // 選択された商品を取得
    $histories = $this->getHistoriesProperty();
    $selectedItems = $histories->whereIn('id', $this->selectedItems)->toArray();

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
    if (empty($this->selectedItems)) {
        session()->flash('message', '受け取りたい商品を選択してください。');
        return; // ここで処理を終了し、メッセージを適切に表示する
    }

    $apiKey = env('AIRTABLE_API_KEY');
    $baseId = env('AIRTABLE_BASE_ID');
    $tableName = env('AIRTABLE_TABLE_NAME');

    foreach ($this->selectedItems as $recordId) {
        Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->patch("https://api.airtable.com/v0/{$baseId}/{$tableName}", [
            'records' => [
                [
                    'id' => $recordId,
                    'fields' => [
                        'status' => '受取済み',
                    ]
                ]
            ]
        ]);
    }

    // 受け取った商品を履歴から削除
    $this->selectedItems = [];
    $this->updateHistory();

    session()->flash('message', '選択された商品を受け取り済みにしました。');
}

private function updateHistory()
{
    $this->histories = $this->getHistoriesProperty()->filter(function ($history) {
        return $history['status'] !== '受取済み';
    });
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