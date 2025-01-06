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

    @if ($isConfirmationModalVisible)
<div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
        <h2 class="text-xl font-bold mb-4">受け取り確認</h2>
        @if(isset(session('confirmation_data')['error']))
            <p class="text-red-500">{{ session('confirmation_data')['error'] }}</p>
        @else
            <p class="mb-2">以下の商品を受け取りました：</p>
            <ul class="text-left mb-4">
                @foreach(session('confirmation_data')['items'] as $item)
                    <li class="font-bold">{{ $item['name'] }}</li>
                @endforeach
            </ul>
            <p class="text-sm">購入日時: <strong>{{ session('confirmation_data')['purchase_time'] }}</strong></p>
            <p class="text-sm">現在時刻: <strong>{{ $currentTime }}</strong></p>
        @endif
        <button wire:click="closeConfirmationModal" class="bg-blue-500 text-white py-2 px-4 rounded mt-4">
            閉じる
        </button>
    </div>
</div>
@endif


    @include('ordering::history.footer')
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const currentTimeElement = document.getElementById('current-time');
    if (currentTimeElement) {
        setInterval(() => {
            const now = new Date();
            currentTimeElement.textContent = now.toLocaleString('ja-JP', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }, 1000);
    }
});
document.addEventListener('livewire:load', () => {
    Livewire.on('hideConfirmationModal', () => {
        const modal = document.querySelector('.fixed.inset-0');
        if (modal) {
            modal.remove(); // モーダルを閉じる
        }
    });
});

</script>