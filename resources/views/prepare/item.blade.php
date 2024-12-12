@foreach($this->items as $index => $item)
    <x-ordering::item-card :item="$item" context="prepare">
        <x-ordering::button
            wire:click="deleteCart({{ $index }})">
            {{ __('削除') }}
        </x-ordering::button>
    </x-ordering::item-card>
@endforeach
