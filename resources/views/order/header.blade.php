<header class="p-10 text-white text-center bg-black bg-gradient-to-r from-primary-200 to-primary-500">
    <h1 class="text-3xl">{{ config('app.name', 'Laravel') }}</h1>
    @include('ordering::order.info')
    
    <div class="mt-3">
        <a href="{{ route('history') }}" class="text-white font-bold underline hover:text-gray-300">
            {{ __('注文履歴') }}
        </a>
    </div>
</header>
