<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-gray-200 px-4 h-16 flex items-center justify-between">
    <button type="button" class="lg:hidden -ms-2 p-2 rounded-lg text-gray-500 hover:text-gray-700" onclick="document.getElementById('sidebar').classList.toggle('hidden');document.getElementById('sidebar').classList.toggle('flex');">
        <svg class="w-25px h-25px" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <h1 class="text-base font-semibold text-gray-800 truncate">@yield('pageTitle', 'Akreditasi')</h1>

    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500 hidden">{{ auth()->user()->name ?? '' }}</span>
        <div class="flex w-30px h-30px items-center justify-center rounded-full btn btn-primary text-white text-xs font-bold">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
    </div>
</header>
