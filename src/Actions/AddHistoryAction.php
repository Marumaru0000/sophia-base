<?php

declare(strict_types=1);

namespace Revolution\Ordering\Actions;

use Revolution\Ordering\Contracts\Actions\AddHistory;

class AddHistoryAction implements AddHistory
{
    public function add(array $history): void
    {
        /*
         * 履歴に残す各商品の配列に、
         * 'category'・'selected_option' をしっかり含めるようにマージする。
         */
        $history['items'] = collect($history['items'] ?? [])->map(function ($item) {
            return array_merge($item, [
                'category' => $item['category'] ?? [],
                'selected_option' => $item['selected_option'] ?? null,
            ]);
        })->toArray();

        /*
         * 既存の session('history') の先頭に今回の履歴を追加し、
         * 履歴上限数（ordering.history.limit）以内に収める。
         */
        $histories = collect(session('history', []))
            ->prepend($history)
            ->take(config('ordering.history.limit', 100));

        session(['history' => $histories->toArray()]);
    }
}
