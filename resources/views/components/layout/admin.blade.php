@props(['activeTab' => 'dashboard'])

<x-layout.app title="VolunteerHub - Admin Portal">
    <div class="flex-grow flex flex-col md:flex-row min-h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 bg-slate-900 text-white flex flex-col justify-between border-r border-slate-800 shrink-0">
            <div>
                <!-- Sidebar Header branding -->
                <div class="p-6 bg-slate-950 border-b border-slate-800 flex items-center gap-3">
                    <i class="fa-solid fa-shield-halved text-jci-accent text-xl"></i>
                    <div>
                        <h2 class="font-extrabold text-sm tracking-wide">VolunteerHub</h2>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Admin Panel</p>
                    </div>
                </div>
                
                <!-- Sidebar Links -->
                <nav class="p-4 space-y-1">
                    <a href="{{ route('admin.dashboard', ['tab' => 'dashboard']) }}" 
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'dashboard' ? 'text-white bg-jci-blue shadow-lg shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i class="fa-solid fa-chart-line text-sm w-4"></i> Dashboard
                    </a>
                    
                    <a href="{{ route('admin.dashboard', ['tab' => 'audits']) }}" 
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'audits' ? 'text-white bg-jci-blue shadow-lg shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i class="fa-solid fa-clipboard-check text-sm w-4"></i> Org Registrations
                        @php
                            $pendingCount = \App\Models\User::where('role', 'organization')->where('status', 'pending')->count();
                        @endphp
                        @if ($pendingCount > 0)
                            <span class="ml-auto bg-amber-500 text-jci-dark text-[9px] px-2 py-0.5 rounded-full font-black animate-pulse">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    
                    <a href="{{ route('admin.dashboard', ['tab' => 'chatbot']) }}" 
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'chatbot' ? 'text-white bg-jci-blue shadow-lg shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i class="fa-solid fa-robot text-sm w-4"></i> Chatbot Setup
                    </a>
                    
                    <a href="{{ route('admin.dashboard', ['tab' => 'broadcast']) }}" 
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'broadcast' ? 'text-white bg-jci-blue shadow-lg shadow-blue-900/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                        <i class="fa-solid fa-tower-broadcast text-sm w-4"></i> Broadcaster
                    </a>
                </nav>
            </div>

            <!-- Sidebar Footer Info / Logout -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 rounded-full bg-jci-accent text-jci-dark flex items-center justify-center font-bold text-xs">AH</div>
                    <div>
                        <h4 class="text-xs font-bold text-white">{{ Auth::user()->name }}</h4>
                        <p class="text-[10px] text-slate-500">System Controller</p>
                    </div>
                </div>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-rose-600/20 hover:bg-rose-600 text-rose-500 hover:text-white text-[11px] font-bold py-2 rounded-lg transition duration-200 flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-power-off text-xs"></i> Log Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <main class="flex-grow flex flex-col bg-slate-100">
            <!-- Topbar indicator -->
            <header class="bg-white border-b border-slate-200 py-4 px-6 md:px-8 flex items-center justify-between sticky top-0 z-50 shadow-sm">
                <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Active Administrative Node
                </h3>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-400 font-semibold">Region XIII Surigao</span>
                    <div class="h-6 w-px bg-slate-200"></div>
                    <x-ui.notifications-bell />
                </div>
            </header>

            <!-- Workspace Content -->
            <div class="p-6 md:p-8 space-y-6 flex-grow overflow-y-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</x-layout.app>
