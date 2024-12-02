<div class="p-3 m-6 text-center rounded-md border-2 border-primary-500">
    <h3 class="text-2xl">{{ __('支払い方法') }}</h3>

    {{-- エラーメッセージを表示 --}}
    @if(session()->has('error'))
        <div class="text-red-500 font-bold">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col items-start">
        {{-- 支払い方法を一覧表示 --}}
        @foreach($payments as $method => $name)
            <label class="inline-flex items-center p-3">
                <input type="radio"
                       name="payment"
                       class="h-5 w-5 text-primary-500 focus:ring focus:ring-primary-300"
                       value="{{ $method }}"
                       wire:model.live="payment_method"/>
                <span class="ml-2">{{ $name }}</span>
            </label>
        @endforeach

        {{-- PayPayの場合の注意メッセージ --}}
        @if($payment_method === 'paypay')
            <div>
                {{ config('ordering.payment.paypay.prepare_message') }}
            </div>
        @endif
    </div>
</div>
