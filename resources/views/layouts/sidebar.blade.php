<!-- Sidebar Component -->
<aside id="main-sidebar"
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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </x-slot>
                Main Dashboard
            </x-sidebar.nav-item>
        </div>

        {{-- <!-- Employee Management Menu -->
        <div class="mb-6">
            <x-sidebar.nav-item href="{{ route('employees.index') }}" :active="request()->routeIs('employees.index')" wire:navigate>
                <x-slot name="icon">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </x-slot>
                Employee Management
            </x-sidebar.nav-item>
        </div> --}}

        <!-- System Categories Menu -->
        <nav class="space-y-4">

            <!-- Organization Setting -->
            <div x-data="{ orgOpen: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Organization Setting
                </h3>

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button @click="orgOpen = !orgOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="orgOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="orgOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span>Organization</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="orgOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        {{-- Chevron --}}
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="orgOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        {{-- Organisation Profile --}}
                        @can('view organization profile')
                            <x-sidebar.nav-item href="{{ route('settings.organisation') }}" :active="request()->routeIs('settings.organisation')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                </x-slot>
                                Profile
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view work location')
                            {{-- Work Locations --}}
                            <x-sidebar.nav-item href="{{ route('settings.work-locations') }}" :active="request()->routeIs('settings.work-locations')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </x-slot>
                                Work Locations
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view department')
                            {{-- Departments --}}
                            <x-sidebar.nav-item href="{{ route('settings.departments') }}" :active="request()->routeIs('settings.departments')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                </x-slot>
                                Departments
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view designation')
                            {{-- Designations --}}
                            <x-sidebar.nav-item href="{{ route('settings.designations') }}" :active="request()->routeIs('settings.designations')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </x-slot>
                                Designations
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view tax')
                            {{-- Tax Slabs --}}
                            <x-sidebar.nav-item href="{{ route('settings.tax-slabs') }}" :active="request()->routeIs('settings.tax-slabs')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </x-slot>
                                Taxes
                            </x-sidebar.nav-item>
                        @endcan

                    </div>
                </div>
            </div>

            {{-- Setup & Configuration Dropdown --}}
            <div x-data="{ setupOpen: {{ request()->routeIs('configurations.*') ? 'true' : 'false' }} }">

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button @click="setupOpen = !setupOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="setupOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="setupOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span>Setup & Configuration</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="setupOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="setupOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        @can('view currency')
                            {{-- Currency Setup --}}
                            <x-sidebar.nav-item href="{{ route('configurations.currencies') }}" :active="request()->routeIs('configurations.currencies')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </x-slot>
                                Setup Currency
                            </x-sidebar.nav-item>
                        @endcan


                        @can('view salary component')
                            {{-- Salary Components --}}
                            <x-sidebar.nav-item href="{{ route('configurations.salary-components') }}" :active="request()->routeIs('configurations.salary-components')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </x-slot>
                                Salary Components
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view bank management')
                            {{-- Bank Management --}}
                            <x-sidebar.nav-item href="{{ route('configurations.banks.index') }}" :active="request()->routeIs('configurations.banks.*')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                </x-slot>
                                Bank Management
                            </x-sidebar.nav-item>
                        @endcan

                        @can('view statutory component')
                            {{-- EPF / ETF --}}
                            <x-sidebar.nav-item href="{{ route('configurations.epf-etf') }}" :active="request()->routeIs('configurations.epf-etf')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                    </svg>
                                </x-slot>
                                Statutory Components
                            </x-sidebar.nav-item>
                        @endcan

                    </div>
                </div>
            </div>

            <div x-data="{ attendanceOpen: {{ request()->routeIs('attendance.*') ? 'true' : 'false' }} }" class="mt-4">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Attendance Management
                </h3>

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button @click="attendanceOpen = !attendanceOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="attendanceOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            {{-- Fingerprint / Clock Icon --}}
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="attendanceOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Attendance</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="attendanceOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="attendanceOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        {{-- 1. Mark Attendance --}}
                        <x-sidebar.nav-item href="{{ route('attendance.mark') }}" :active="request()->routeIs('attendance.mark')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </x-slot>
                            Mark Attendance
                        </x-sidebar.nav-item>

                        {{-- 1. Shift & Policy Settings --}}
                        <x-sidebar.nav-item href="{{ route('attendance.settings') }}" :active="request()->routeIs('attendance.settings')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </x-slot>
                            Shift & Rules
                        </x-sidebar.nav-item>

                        {{-- Attendance Approvals --}}
                        <x-sidebar.nav-item href="{{ route('admin.attendance.approvals') }}" :active="request()->routeIs('admin.attendance.approvals')"
                            wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </x-slot>
                            Attendance Approvals
                        </x-sidebar.nav-item>

                        {{-- All Attendance Logs --}}
                        <x-sidebar.nav-item href="{{ route('attendance.all') }}" :active="request()->routeIs('attendance.all')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </x-slot>
                            All Attendance Logs
                        </x-sidebar.nav-item>

                    </div>
                </div>
            </div>

            <div x-data="{ leaveOpen: {{ request()->routeIs('leave.*') ? 'true' : 'false' }} }" class="mt-4">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Leave Management
                </h3>

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button @click="leaveOpen = !leaveOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="leaveOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="leaveOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                            </svg>
                            <span>Leave Portal</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="leaveOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="leaveOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        <x-sidebar.nav-item href="{{ route('leave.holiday-categories') }}" :active="request()->routeIs('leave.holiday-categories')"
                            wire:navigate>
                            <x-slot name="icon">
                                <!-- Tag / Category Icon -->
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M6 6h.008v.008H6V6Z" />
                                </svg>
                            </x-slot>
                            Holiday Categories
                        </x-sidebar.nav-item>

                        {{-- Holiday Management Sub-menu --}}
                        @can('view holiday')
                            <x-sidebar.nav-item href="{{ route('leave.holidays') }}" :active="request()->routeIs('leave.holidays')" wire:navigate>
                                <x-slot name="icon">
                                    <!-- Calendar Icon -->
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                </x-slot>
                                Public Holidays
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Leave Type Settings Configuration Menu Navigation Control Element --}}
                        @can('view leave type')
                            <x-sidebar.nav-item href="{{ route('leave.leave-types') }}" :active="request()->routeIs('leave.leave-types')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </x-slot>
                                Leave Type Settings
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Employee Leave Balance Management Navigation Link Component --}}
                        @can('view leave balance')
                            <x-sidebar.nav-item href="{{ route('leave.leave-balances') }}" :active="request()->routeIs('leave.leave-balances')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </x-slot>
                                Manage Leave Balances
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Leave Apply Form Sub-menu --}}
                        <x-sidebar.nav-item href="{{ route('leave.apply') }}" :active="request()->routeIs('leave.apply')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </x-slot>
                            Apply for Leave
                        </x-sidebar.nav-item>

                        {{-- Leave Approval Sub-menu --}}
                        @can('view leave approval')
                            <x-sidebar.nav-item href="{{ route('leave.approval-panel') }}" :active="request()->routeIs('leave.approval-panel')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </x-slot>
                                Leave Requests
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Department Absence Tracker Sub-menu --}}
                        @can('view department absence tracker')
                            <x-sidebar.nav-item href="{{ route('leave.absence-tracker') }}" :active="request()->routeIs('leave.absence-tracker')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                                    </svg>
                                </x-slot>
                                Absence Tracker
                            </x-sidebar.nav-item>
                        @endcan

                    </div>
                </div>
            </div>


            <!-- Employee Management -->
            <div x-data="{ orgOpen: {{ request()->routeIs('employees.*') ? 'true' : 'false' }} }">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Employee Management
                </h3>

                <div class="space-y-1">

                    {{-- Dropdown Trigger Button --}}
                    <button @click="orgOpen = !orgOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="orgOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="orgOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15.75 6a3.75 3.75 0 1 1-7 0 3.75 3.75 0 0 1 7 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>Employee</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="orgOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="orgOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        @can('view employee')
                            {{-- Add Employee --}}
                            <x-sidebar.nav-item href="{{ route('employees.manage') }}" :active="request()->routeIs('employees.manage')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </x-slot>
                                Add Employee
                            </x-sidebar.nav-item>
                        @endcan


                        {{-- Add Document Category --}}
                        @can('view document category')
                            <x-sidebar.nav-item href="{{ route('employees.document-categories') }}" :active="request()->routeIs('employees.document-categories')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                </x-slot>
                                Document Category
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Manage Employee Documents --}}
                        @can('open employee document')
                            <x-sidebar.nav-item href="{{ route('employees.employee.documents') }}" :active="request()->routeIs('employees.employee.documents')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                </x-slot>
                                Employee Documents
                            </x-sidebar.nav-item>
                        @endcan

                    </div>
                </div>
            </div>

            <div x-data="{ orgOpen: {{ request()->routeIs('payroll.*') ? 'true' : 'false' }} }">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Payroll Management
                </h3>

                <div class="space-y-1">
                    {{-- Dropdown Trigger Button --}}
                    <button @click="orgOpen = !orgOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="orgOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="orgOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span>Payroll</span>
                        </div>

                        {{-- Chevron --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="orgOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items --}}
                    <div x-show="orgOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        {{-- 2. All Employees List --}}
                        {{-- @can('view employee')
                            <x-sidebar.nav-item href="{{ route('employees.index') }}" :active="request()->routeIs('employees.index')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                </x-slot>
                                All Employees
                            </x-sidebar.nav-item>
                        @endcan --}}

                        <x-sidebar.nav-item href="{{ route('payroll.categories') }}" :active="request()->routeIs('payroll.categories')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.999-3.199a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            </x-slot>
                            Employee Categories
                        </x-sidebar.nav-item>

                         <x-sidebar.nav-item href="{{ route('payroll.salary-assignments') }}" :active="request()->routeIs('payroll.salary-assignments')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </x-slot>
                            Salary Assignments
                        </x-sidebar.nav-item>

                         {{-- Overtime Management --}}
                        <x-sidebar.nav-item href="{{ route('payroll.overtimes') }}" :active="request()->routeIs('payroll.overtimes')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </x-slot>
                            Overtime Management
                        </x-sidebar.nav-item>

                        {{-- Monthly Payroll Processing --}}
                        <x-sidebar.nav-item href="{{ route('payroll.process') }}" :active="request()->routeIs('payroll.process')" wire:navigate>
                            <x-slot name="icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v10.5m0 0H3a.75.75 0 0 1 .75.75v.75m0-12h16.5m0 0h.75A.75.75 0 0 1 21 6v10.5a.75.75 0 0 1-.75.75h-.75m-16.5 0v.75A.75.75 0 0 0 3 18.75h.75m16.5 0v.75a.75.75 0 0 1-.75.75H18.75m-15-1.5h15" />
                                </svg>
                            </x-slot>
                            Payroll Processing
                        </x-sidebar.nav-item>
                    </div>
                </div>
            </div>
        </nav>

        <!-- System Categories Menu -->
        <nav class="space-y-4">

            <!-- Settings (Dropdown Menu) -->
            {{-- Alpine.js variable to keep dropdown open if current route matches profile or settings --}}
            <div x-data="{ settingsOpen: {{ request()->routeIs('profile*') ? 'true' : 'false' }} }">

                <h3 class="px-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Settings</h3>

                <div class="space-y-1">
                    {{-- Dropdown Trigger Button --}}
                    <button @click="settingsOpen = !settingsOpen"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors group"
                        :class="settingsOpen ? 'bg-gray-100 text-gray-900' : ''">

                        <div class="flex items-center gap-3">
                            {{-- Gear/Settings Icon --}}
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors"
                                :class="settingsOpen ? 'text-gray-600' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127c.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.767c-.293.225-.438.589-.411.956c.004.053.006.107.006.16c0 .054-.002.108-.006.16c-.027.367.118.73.411.956l1.003.767a1.125 1.125 0 0 1 .26 1.43l-1.296 2.247a1.125 1.125 0 0 1-1.37.49l-1.216-.456c-.356-.133-.751-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.767c.292-.225.437-.589.41-.956a4.836 4.836 0 0 1-.006-.16c0-.054.002-.108.006-.16c.027-.367-.118-.73-.41-.956l-1.004-.767a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.49l1.216.456c.356.133.751.072 1.076-.124c.072-.044.146-.087.22-.128c.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Settings</span>
                        </div>

                        {{-- Chevron Arrow (Rotates when open) --}}
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            :class="settingsOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Items Container --}}
                    <div x-show="settingsOpen" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="ml-4 pl-3 border-l-2 border-gray-100 space-y-1 mt-1">

                        {{-- Item 1: User Profile --}}
                        @can('view user profile')
                            <x-sidebar.nav-item href="{{ route('settings.profile') }}" :active="request()->routeIs('settings.profile')" wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </x-slot>
                                User Profile
                            </x-sidebar.nav-item>
                        @endcan

                        {{-- Item 2: Access Control --}}
                        @can('view access control')
                            <x-sidebar.nav-item href="{{ route('settings.access-control.index') }}" :active="request()->routeIs('settings.access-control*')"
                                wire:navigate>
                                <x-slot name="icon">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.011 5.891L9.62 17.26a1.5 1.5 0 0 1-1.06.44H6.75a.75.75 0 0 1-.75-.75v-1.81l.22-.22a1.5 1.5 0 0 1 1.06-.44h1.06A1.5 1.5 0 0 0 9.75 13.5v-1.06a1.5 1.5 0 0 1 .44-1.06l1.242-1.242A6 6 0 0 1 18 5.25a3 3 0 0 1 3 3Z" />
                                    </svg>
                                </x-slot>
                                Access Control
                            </x-sidebar.nav-item>
                        @endcan

                    </div>
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
