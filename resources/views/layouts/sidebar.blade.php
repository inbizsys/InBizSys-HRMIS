<!-- Sidebar Component -->
<aside 
    id="main-sidebar"
    :class="{
        'w-64 translate-x-0': sidebarOpen,
        'w-0 -translate-x-full lg:w-0 lg:translate-x-0': !sidebarOpen,
        'fixed inset-y-0 left-0 z-50': isMobile,
        'static flex-shrink-0': !isMobile
    }"
    class="flex flex-col bg-white border-r border-gray-200 transition-all duration-300 ease-in-out overflow-hidden"
    x-cloak>
    <!-- Logo Section -->
    <div class="p-6 flex-shrink-0">
                <img src="{{ asset('src/images/logo/nav-logo.png') }}" alt="Logo"
                    class="h-auto w-full mx-auto transition-opacity duration-300"
                    :class="sidebarOpen ? 'opacity-100' : 'opacity-0'" />
            </div>

    <!-- Navigation List -->
    <div class="flex-1 px-3 py-4 overflow-y-auto custom-scrollbar">
        <!-- Main Route -->
        <div class="mb-6">
            <x-sidebar.nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" wire:navigate>
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </x-slot>
                Main Dashboard
            </x-sidebar.nav-item>
        </div>

        <!-- System Categories Menu -->
        <nav class="space-y-4">
            <!-- Add future categories here -->


            <!-- Settings -->
            <div>
                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Settings</h3>
                <div class="space-y-1">
                    <x-sidebar.nav-item href="{{ route('profile') }}" :active="request()->routeIs('profile')" wire:navigate>
                        <x-slot name="icon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </x-slot>
                        User Profile
                    </x-sidebar.nav-item>
                </div>
            </div>
        </nav>
    </div>
</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>
