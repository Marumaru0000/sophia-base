<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use Livewire\Livewire;
use Revolution\Ordering\Http\Livewire\Order\History;
use Tests\TestCase;

class LivewireHistoryTest extends TestCase
{
    public function testHistory()
    {
        $this->withoutVite();

        $this->withSession([
            'history' => [
                [
                    'items' => [
                        'id' => 'test',
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('history'));

        $response->assertStatus(200)
                 ->assertSeeLivewire('ordering.history');
    }

    public function testHistoryDeleteHistory()
    {
        Livewire::test(History::class)
                ->call('deleteHistory')
                ->assertSessionMissing('history');
    }

    public function testHistoryRedirect()
    {
        $this->withSession([
            'table' => 'test',
        ]);

        Livewire::test(History::class)
                ->call('back')
                ->assertRedirect(route('order', ['table' => 'test']));
    }
    public function testDeleteSelectedItemsWithValidData()
{
    $this->withSession([
        'history' => [
            [
                'items' => ['item1', 'item2'],
            ],
        ],
    ]);

    Livewire::test(History::class)
        ->set('selectedItems', ['item1' => true]) // 正しい形式でセット
        ->call('deleteSelectedItems')
        ->assertSessionHas('history', [
            [
                'items' => ['item2'], // item1が削除されている
            ],
        ]);
}


public function testDeleteSelectedItemsWithInvalidData()
{
    $this->withSession([
        'history' => [
            [
                'items' => 'invalid_data', // 不正な形式
            ],
        ],
    ]);

    Livewire::test(History::class)
        ->set('selectedItems', ['item1'])
        ->call('deleteSelectedItems')
        ->assertSessionHas('history', [
            [
                'items' => [], // 不正なデータは空配列に置き換え
            ],
        ]);
}

public function testHistoryWithInvalidItems()
{
    $this->withSession([
        'history' => [
            [
                'items' => null, // 無効なデータ
            ],
        ],
    ]);

    Livewire::test(History::class)
        ->assertDontSee('名前がありません') // エラーレンダリングがないことを確認
        ->assertSet('selectedItems', []);
}

public function testHistoryItemsAreDisplayedCorrectly()
{
    $this->withSession([
        'history' => [
            [
                'items' => [
                    ['id' => 'item1', 'name' => 'メニュー1', 'price' => 500],
                    ['id' => 'item2', 'name' => 'メニュー2', 'price' => 700],
                ],
            ],
        ],
    ]);

    Livewire::test(History::class)
        ->assertSee('メニュー1')
        ->assertSee('メニュー2')
        ->assertSee('500円')
        ->assertSee('700円');
}

} 