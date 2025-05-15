@php
    use Illuminate\Support\Arr;
@endphp

<div class="mx-auto pb-40" wire:poll.5000ms="loadHistories">
    @include('ordering::order.header')

    <div class="p-3 m-6 text-center">
        <h2 class="text-3xl">{{ __('注文履歴') }}</h2>
        <div class="text-lg font-bold text-primary-500">
            {{ __('合計金額：') }}{{ $this->histories->sum('price') }}{{ __('円') }}
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

    {{-- 履歴をループ表示 --}}
    @foreach($this->histories as $history)
        <div class="border p-3 mb-3 rounded">
            <h3>注文番号: {{ $history['order_id'] }}</h3>
            <p>日時: {{ $history['purchase_time'] }}</p>

            <div class="my-2 p-2 border-b">
                <strong>{{ $history['item_name'] }}</strong>（{{ $history['price'] }}円）
                @if(!empty($history['selected_option']))
                    ({{ $history['selected_option'] }})
                @endif

                {{-- 商品画像表示 --}}
                @if(!empty($history['image']))
                    <div class="mt-1">
                        <x-ordering::image :src="$history['image'] ?? config('ordering.menu.no_image')" />
                    </div>
                @endif

                {{-- ステータス表示 --}}
                <p class="font-bold text-sm mt-2
                    @if($history['status'] === '未準備') text-red-600
                    @elseif($history['status'] === '準備完了') text-yellow-500
                    @elseif($history['status'] === '受け渡し完了') text-gray-500
                    @endif">
                    ステータス: {{ $history['status'] }}
                </p>
            </div>
        </div>
    @endforeach

    @include('ordering::history.footer')
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('random-symbols');
        if (container) {
            const symbols = ['★', '●', '▲', '■'];
            setInterval(() => {
                container.textContent = symbols[Math.floor(Math.random() * symbols.length)];
            }, 500);
        }

        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            setInterval(() => {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('ja-JP');
            }, 1000);
        }
    });
</script>