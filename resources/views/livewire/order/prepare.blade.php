<div class="mx-auto pb-40">
    {{-- 例: 既存のヘッダー --}}
    @include('ordering::order.header')

    <h2 class="text-2xl font-bold my-4 px-3">注文内容の確認</h2>

    {{-- カートアイテム一覧 --}}
    @foreach($this->items as $index => $item)
        <div class="m-3 p-3 rounded-md border shadow-md">
            <div class="flex justify-between">
                <div>
                    <h3 class="font-bold">{{ $item['name'] ?? '商品名なし' }}</h3>
                    <p>{{ $item['price'] ?? 0 }}円</p>
                    <p class="text-sm text-gray-600">
                        カテゴリ: {{ implode(',', (array)Arr::get($item, 'category', [])) }}
                    </p>

                    @if(is_array(Arr::get($item, 'category', [])) && in_array('ライス', Arr::get($item, 'category', [])))
                        <div class="mt-2">
                            <label class="font-semibold">ライスオプション:</label>
                            <select wire:change="updateOption({{ $index }}, $event.target.value)">
                                <option value="">選択しない</option>
                                <option value="rice-big">ライス大盛り(+60円)</option>
                                <option value="rice-small">ライス小に変更</option>
                            </select>
                        </div>
                    @endif

                    @if(is_array($item['category']) && in_array('麺', $item['category']))
                        <div class="mt-2">
                            <label class="font-semibold">麺オプション:</label>
                            <select
                                wire:change="updateOption({{ $index }}, $event.target.value)"
                                class="border rounded px-2 py-1 text-sm"
                            >
                                <option value="">選択しない</option>
                                <option value="noodle-big">麺大盛り(+60円)</option>
                            </select>
                        </div>
                    @endif
                </div>
                <div>
                    {{-- 商品画像 --}}
                    <x-ordering::image :src="$item['image'] ?? config('ordering.menu.no_image')"/>
                </div>
            </div>

            <div class="mt-3">
                <x-ordering::button wire:click="deleteCart({{ $index }})">
                    削除
                </x-ordering::button>
            </div>
        </div>
    @endforeach

    {{-- メモ欄や支払い選択など、既存UI --}}
    <div class="p-3">
        <label>メモ</label>
        <textarea wire:model.defer="memo" class="border rounded w-full h-20"></textarea>
    </div>

    {{-- フッター (合計や支払いボタン) --}}
    @include('ordering::prepare.footer')
</div>
