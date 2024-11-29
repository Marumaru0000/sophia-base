@props(['item'])

<div {{ $attributes->merge(['class' => 'm-3 p-3 rounded shadow-lg flex justify-between dark:bg-gray-800']) }}>
    <div>
        <h4 class="font-bold">{{ $item['name'] ?? '名前がありません' }}</h4>
        <div>{{ $item['description'] ?? '説明がありません' }}</div>
        <span>{{ $item['price'] ?? 0 }}円</span>
        @if($item['is_available'] ?? false)
            <x-ordering::button wire:click="addCart('{{ $item['id'] }}')">
                {{ __('追加') }}
            </x-ordering::button>
        @else
            <x-ordering::button :disabled="true">
                {{ __('売り切れ') }}
            </x-ordering::button>
        @endif
        @if(isset($item['options']['rice']) && $item['options']['rice'])
            <div>ライスオプション: {{ $item['options']['rice'] }}</div>
        @endif
        @if(isset($item['options']['noodle']) && $item['options']['noodle'])
            <div>麺オプション: {{ $item['options']['noodle'] }}</div>
        @endif
    </div>
    <div>
    <x-ordering::image :src="$item['image'] ?? config('ordering.menu.no_image')"></x-ordering::image>

    </div>
</div>