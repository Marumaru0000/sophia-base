<?php

declare(strict_types=1);

namespace Revolution\Ordering\Http\Livewire\Order;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Redirector;
use GuzzleHttp\Client;
use Revolution\Ordering\Facades\Menu;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class History extends Component
{
    public string $currentTime = '';

    public function mount(): void
    {
        $this->currentTime = now()->format('H:i:s');
    }

    public function updateCurrentTime(): void
    {
        $this->currentTime = now()->format('H:i:s');
    }

    protected Collection $menus;

    public function boot(): void
    {
        $this->menus = Collection::wrap(Menu::get());
    }

    public function getHistoriesProperty(): Collection
    {
        $apiKey     = env('AIRTABLE_API_KEY');
        $baseId     = env('AIRTABLE_BASE_ID');
        $tableName  = env('AIRTABLE_TABLE_NAME');
        $lineUserId = Auth::user()->line_user_id;

        Log::info("Fetching history for line_user_id: {$lineUserId}");

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
        ])->get("https://api.airtable.com/v0/{$baseId}/{$tableName}",
        ['filterByFormula' => "{line_user_id} = '{$lineUserId}'"]
        );

        Log::info('Airtable 履歴取得レスポンス:', ['response' => $response->json()]);

        if (!$response->successful()) {
            Log::error('Airtable API 履歴取得エラー:', [
                'status'  => $response->status(),
                'message' => $response->body(),
            ]);
            return collect([]);
        }

        $records = $response->json()['records'] ?? [];
        $menus   = $this->getMenus();

        return collect($records)->map(function ($record) use ($menus) {
            $menu  = $menus->firstWhere('id', $record['fields']['item_id']) ?? [];
            $image = $menu['image'] ?? config('ordering.menu.no_image');

            return [
                'id'              => $record['id'],
                'order_id'        => $record['fields']['order_id'] ?? '',
                'customer_id'     => $record['fields']['customer_id'] ?? '',
                'item_id'         => $record['fields']['item_id'] ?? '',
                'item_name'       => $record['fields']['item_name'] ?? '',
                'price'           => $record['fields']['price'] ?? 0,
                'selected_option' => $record['fields']['selected_option'] ?? '',
                'purchase_time'   => $record['fields']['purchase_time'] ?? '',
                'status'          => $record['fields']['status'] ?? '未準備',
                'payment_method'  => $record['fields']['payment_method'] ?? 'PayPay',
                'image'           => $image,
                'category'        => $record['fields']['category'] ?? '未分類',
            ];
        })->values();
    }

    private function getMenus(): Collection
    {
        try {
            $client   = new Client();
            $response = $client->get(env('ORDERING_MICROCMS_ENDPOINT'), [
                'headers' => ['X-API-KEY' => env('ORDERING_MICROCMS_API_KEY')],
                'query'   => [
                    'limit' => config('ordering.menu.micro-cms.limit'),
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
    public function loadHistories(): void
{
    // 単純に再レンダリングのために $this->getHistoriesProperty() を読み出す
    $this->render();
}

    public function back()
    {
        return redirect()->route('customer.order');
    }

    public function render()
    {
        return view()->first([
            'ordering-theme::livewire.order.history',
            'ordering::livewire.order.history',
        ]);
    }
}