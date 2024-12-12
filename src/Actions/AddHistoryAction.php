<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Revolution\Ordering\Contracts\Actions\AddHistory;
use Revolution\Ordering\Facades\Menu;

class AddHistoryAction implements AddHistory
{
    public function add(array $history): void
    {
        $menus = collect(Menu::get());

        $history['items'] = collect($history['items'] ?? [])->map(function ($itemId) use ($menus) {
            return $menus->firstWhere('id', $itemId) ?? ['id' => $itemId, 'name' => '不明な商品', 'price' => 0];
        })->toArray();

        $histories = collect(session('history', []))->prepend($history)->take(config('ordering.history.limit', 100));

        session(['history' => $histories->toArray()]);
    }
}
