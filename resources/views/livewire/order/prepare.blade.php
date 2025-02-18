<div class="mx-auto pb-40">
    @include('ordering::order.header')

    <div class="p-3 m-6 text-center">
        <h2 class="text-3xl">{{ __('注文の確認') }}</h2>
        @unless(config('ordering.payment.enabled'))
            <div class="font-bold">{{ config('ordering.shop.disabled_pay_message') }}</div>
        @endunless
    </div>

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

    @if(config('ordering.payment.enabled'))
        @include('ordering::prepare.payments')
    @endif

    {{-- フッター (合計や支払いボタン) --}}
    @include('ordering::prepare.footer')
</div>
