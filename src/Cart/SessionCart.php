<?php

declare(strict_types=1);

namespace Revolution\Ordering\Cart;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Revolution\Ordering\Contracts\Cart\CartFactory;
use Revolution\Ordering\Facades\Menu;

class SessionCart implements CartFactory
{
    use Macroable;

    public const CART = 'cart';
    public const MEMO = 'memo';

    /**
     * カートの商品一覧をメニューにマージして返す.
     *
     * @param  Collection|array|null  $items
     * @param  Collection|array|null  $menus
     * @return Collection
     */
    public function items($items = null, $menus = null): Collection
{
    $rawItems = Collection::wrap($items ?? $this->all());
    $menus = Collection::wrap($menus ?? Menu::get());

    return $rawItems->map(function ($cartItem) use ($menus) {

        if (is_scalar($cartItem)) {
            $cartItem = [
                'id' => $cartItem,
                'selected_option' => null,
            ];
        }

        $menu = $menus->firstWhere('id', $cartItem['id']);
        if (empty($menu)) {
            return [
                'id' => $cartItem['id'] ?? 'unknown',
                'name' => '不明な商品',
                'price' => 0,
                'selected_option' => $cartItem['selected_option'] ?? null,
                'category' => [],
                'image' => config('ordering.menu.no_image'),
            ];
        }

        // メニュー情報 + カート内オプション
        $item = array_merge($menu, [
            'selected_option' => $cartItem['selected_option'] ?? null,
            'category' => $menu['category'] ?? [],
        ]);

        // ベース価格
        $price = $item['price'] ?? 0;
        // オプションの内部値 (例: "rice-big", "rice-small", "noodle-big")
        $option = $item['selected_option'] ?? '';

        // +60円の判定
        if ($option === 'rice-big' || $option === 'noodle-big') {
            $price += 60;
        }

        // **表示用** に selected_option を書き換え
        switch ($option) {
            case 'rice-big':
                $item['selected_option'] = 'ライス大盛り(+60円)';
                break;
            case 'rice-small':
                $item['selected_option'] = 'ライス小に変更';
                break;
            case 'noodle-big':
                $item['selected_option'] = '麺大盛り(+60円)';
                break;
            default:
                // 選択なしまたは他の値なら、そのままか空にする
                // $item['selected_option'] = '';
                break;
        }

        $item['price'] = $price;
        return $item;
    });
}


    /**
     * カート内容の生配列を取得.
     *
     * @return array
     */
    public function all(): array
    {
        return session(self::CART, []);
    }

    /**
     * カートにアイテムを追加.
     *
     * @param  int|string|array  $id
     * @return void
     */
    public function add($id): void
    {
        $items = $this->all();

        // スカラーなら ['id' => $id, 'selected_option' => null] を挿入
        if (is_scalar($id)) {
            $items[] = [
                'id' => $id,
                'selected_option' => null,
            ];
        }
        // 既に配列ならそのまま push
        elseif (is_array($id)) {
            $items[] = $id;
        }

        session([self::CART => $items]);
    }

    /**
     * カートアイテムを更新（例: selected_option を変更など）
     *
     * @param  int    $index
     * @param  array  $data
     * @return void
     */
    public function update(int $index, array $data): void
    {
        $items = $this->all();

        if (isset($items[$index])) {
            // スカラーなら配列化
            if (!is_array($items[$index])) {
                $items[$index] = [
                    'id' => $items[$index],
                ];
            }
            $items[$index] = array_merge($items[$index], $data);
        }

        session([self::CART => $items]);
    }

    /**
     * $index 番目のアイテムを削除.
     *
     * @param  int  $index
     * @return void
     */
    public function delete(int $index): void
    {
        $items = $this->all();

        // 指定キーだけ除去
        $items = Arr::except($items, [$index]);
        // キーを詰め直し
        $items = array_values($items);

        session([self::CART => $items]);
    }

    /**
     * カート・メモをリセット.
     *
     * @return void
     */
    public function reset(): void
    {
        session()->forget([self::CART, self::MEMO]);
    }
}
