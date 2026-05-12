<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Inbizsys') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-50" x-data="{ sidebarOpen: true, isMobile: window.innerWidth < 1024 }" @resize.window="isMobile = window.innerWidth < 1024; if (!isMobile) sidebarOpen = true;">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar Overlay for Mobile -->
            <div x-show="sidebarOpen && isMobile" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900 bg-opacity-50 lg:hidden" @click="sidebarOpen = false"></div>

            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main Content Area -->
            <div class="relative flex flex-col flex-1 w-full overflow-y-auto overflow-x-hidden min-w-0">
                <!-- Top Navigation -->
                <livewire:layout.navigation />

                <!-- Page Heading (optional, usually managed inside pages now) -->
                @if (isset($header))
                    <header class="bg-white shadow-sm border-b border-gray-100">
                        <div class="px-4 py-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1 w-full p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
