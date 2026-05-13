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

            <!-- Organization Setting -->
            <div x-data="{ orgOpen: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Organization Setting</h3>

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button
                        @click="orgOpen = !orgOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="orgOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="orgOpen ? 'text-gray-600' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            <span>Organization</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="orgOpen ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div
                        x-show="orgOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        {{-- Organisation Profile --}}
                        <x-sidebar.nav-item
                            href="{{ route('settings.organisation') }}"
                            :active="request()->routeIs('settings.organisation')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/>
                                </svg>
                            </x-slot>
                            Profile
                        </x-sidebar.nav-item>

                        {{-- Work Locations --}}
                        <x-sidebar.nav-item
                            href="{{ route('settings.work-locations') }}"
                            :active="request()->routeIs('settings.work-locations')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                            </x-slot>
                            Work Locations
                        </x-sidebar.nav-item>

                        {{-- Departments --}}
                        <x-sidebar.nav-item
                            href="{{ route('settings.departments') }}"
                            :active="request()->routeIs('settings.departments')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                                </svg>
                            </x-slot>
                            Departments
                        </x-sidebar.nav-item>

                        {{-- Designations --}}
                        <x-sidebar.nav-item
                            href="{{ route('settings.designations') }}"
                            :active="request()->routeIs('settings.designations')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </x-slot>
                            Designations
                        </x-sidebar.nav-item>

                    </div>
                </div>
            </div>

        </nav>

        <!-- System Categories Menu -->
        <nav class="space-y-4">

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
