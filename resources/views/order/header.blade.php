<header class="p-10 text-white text-center bg-black bg-gradient-to-r from-primary-200 to-primary-500">
    <h1 class="text-3xl">{{ config('app.name', 'Laravel') }}</h1>
    @include('ordering::order.info')

    <div class="mt-3 flex justify-center items-center space-x-6">
        {{-- 既存のログイン／履歴リンク --}}
        @auth('customer')
            <span>ようこそ、{{ Auth::guard('customer')->user()->name }}さん</span>
            <form method="POST" action="{{ route('customer.logout') }}">
                @csrf
                <button type="submit" class="text-white font-bold underline hover:text-gray-300">
                    ログアウト
                </button>
            </form>
        @else
            <a href="{{ route('customer.login.line') }}" class="text-white font-bold underline hover:text-gray-300">
                LINEログイン
            </a>
        @endauth

        <a href="{{ route('customer.history') }}"
           class="text-white font-bold underline hover:text-gray-300">
            {{ __('注文履歴') }}
        </a>
    </div>

    {{-- ここから友だち追加促進バナー --}}
    @auth('customer')
    <div class="mt-6 p-4 bg-green-100 border border-green-300 rounded-lg shadow-md flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0 sm:space-x-4">
        <div class="text-center sm:text-left">
            <p class="text-green-900 font-semibold text-lg">
                注文通知を受け取るにはLINEで友だち追加してください 📲
            </p>
            <p class="text-sm text-green-700">お料理の準備ができたらLINEで通知します！</p>
        </div>
        <a href="https://line.me/R/ti/p/@834kwsff" target="_blank"
           class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-200 shadow">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.665 3.337A10.938 10.938 0 0 0 12 0C5.373 0 0 4.805 0 10.731c0 3.376 1.816 6.368 4.693 8.345-.2.747-1.296 4.843-1.342 5.062 0 0-.026.23.121.318.146.087.331.06.331.06.437-.061 5.07-3.307 5.457-3.561A11.608 11.608 0 0 0 12 21.462c6.627 0 12-4.805 12-10.731 0-2.863-1.232-5.477-4.335-7.394z"/>
            </svg>
            友だち追加
        </a>
    </div>
@endauth

</header>