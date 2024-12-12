<div class="mx-auto pb-40">
    @include('ordering::order.header')

    <div class="p-3 m-6 text-center">
        <h2 class="text-3xl">{{ __('注文履歴') }}</h2>
        <div class="text-lg font-bold text-primary-500">
            {{ __('合計金額：') }}{{ $this->histories->sum(fn ($history) => collect(Arr::get($history, 'items'))->sum('price')) }}{{ __('円') }}
        </div>
    </div>

    @if (session()->has('order_completed_message'))
        <div class="p-3 m-6 text-center text-white font-bold bg-primary-500 rounded-md">
            {{ session('order_completed_message') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="p-3 m-6 text-center text-white font-bold bg-red-500 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    @foreach($this->histories as $history)
        <div class="m-3 p-3 rounded-md border-2 border-primary-500">
            <div class="text-center">
                <h3 class="text-2xl p-1">{{ __('注文番号：') }}{{ Arr::get($history, 'order_id') }}</h3>
                <div class="text-xl p-1">{{ Arr::get($history, 'date') }}</div>
                <div class="p-3 font-bold">
                    {{ __('合計') }}{{ collect(Arr::get($history, 'items'))->sum('price') }}{{ __('円') }}
                </div>
                <div>{{ Arr::get($history, 'memo') }}</div>
            </div>
            @foreach(Arr::get($history, 'items', []) as $item)
                <x-ordering::item-card :item="$item" context="history"></x-ordering::item-card>
            @endforeach
        </div>
    @endforeach


    @if(config('ordering.history.delete', false))
        <div class="p-3 m-6 text-center">
            <x-ordering::secondary-button
                wire:click="confirmReceipt"
                wire:loading.attr="disabled">
                {{ __('受け取りを完了') }}
            </x-ordering::secondary-button>
        </div>
    @endif
    <div>
        <h4>デバッグ用</h4>
        <pre>{{ json_encode($selectedItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>

    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('confirmReceipt', ({ message }) => {
                if (confirm(message)) {
                    // イベント発火ではなく、サーバメソッドを直接呼び出し
                    @this.call('deleteSelectedItems');
                }
            });
        });
    </script>
    @include('ordering::history.footer')
</div>