<x-layout.app title="VolunteerHub - Organization Portal">
    <div class="flex-grow flex flex-col min-h-screen">
        <!-- JCI Corporate Style Top Header -->
        <header class="bg-jci-blue text-white shadow-md border-b-4 border-jci-accent sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 md:px-8 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Brand Section -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white rounded-lg text-jci-blue"><i class="fa-solid fa-building text-lg"></i></div>
                    <div>
                        <span class="bg-jci-accent text-jci-dark text-[9px] font-black uppercase px-2 py-0.5 rounded">Active Partner Org</span>
                        <h2 class="font-extrabold text-base tracking-tight">{{ Auth::user()->name }}</h2>
                    </div>
                </div>
                
                <!-- Navigation / Actions -->
                <div class="flex items-center gap-4">
                    <span class="text-xs text-blue-200 font-semibold"><i class="fa-solid fa-globe"></i> Surigao City Division</span>
                    <div class="h-6 w-px bg-blue-700"></div>
                    <x-ui.notifications-bell color="white" />
                    <div class="h-6 w-px bg-blue-700"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-red-500/20 hover:bg-red-500/80 text-white border border-red-500/30 text-[10px] font-bold px-3 py-1.5 rounded-lg transition duration-200">
                            <i class="fa-solid fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Org Main Dashboard Canvas -->
        <main class="max-w-7xl mx-auto px-4 md:px-8 py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 flex-grow w-full">
            {{ $slot }}
        </main>
    </div>
</x-layout.app>
