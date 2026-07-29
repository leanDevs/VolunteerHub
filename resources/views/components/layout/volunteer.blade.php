<x-layout.app title="VolunteerHub - Volunteer Portal">
    <div class="flex-grow flex flex-col min-h-screen bg-slate-50">
        <!-- Custom volunteer top nav -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50 shadow-sm">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-jci-blue text-white flex items-center justify-center font-bold text-sm">
                        @php
                            $initials = collect(explode(' ', Auth::user()->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                        @endphp
                        {{ strtoupper($initials) }}
                    </div>
                    <div>
                        <h3 class="font-black text-sm text-slate-800">{{ Auth::user()->name }}</h3>
                        <p class="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active Volunteer
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <x-ui.notifications-bell />
                    <div class="h-6 w-px bg-slate-200"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-slate-600 text-xs font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 md:px-8 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 w-full flex-grow">
            {{ $slot }}
        </main>
    </div>
</x-layout.app>
