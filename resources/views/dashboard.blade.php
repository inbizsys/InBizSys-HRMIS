<x-app-layout>
    <div class="dashboard-container" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
        {{-- Page Header --}}
        <div class="mb-8" x-show="loaded" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">
                        Dashboard
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Welcome back, <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span> — here's your system overview.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400 font-medium">
                        <svg class="w-3.5 h-3.5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ now()->format('D, M d · h:i A') }}
                    </span>
                </div>
            </div>
        </div>


    </div>

    <style>
        .dashboard-container {
            max-width: 100%;
        }

        .dashboard-card {
            background: white;
            border: 1px solid rgba(229, 231, 235, 0.8);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            border-color: rgba(199, 210, 254, 0.8);
            box-shadow: 0 4px 24px -4px rgba(99, 102, 241, 0.08), 0 0 0 1px rgba(99, 102, 241, 0.05);
        }

        /* Subtle gradient mesh background */
        .dashboard-container::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.03) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
        }

        .dashboard-container::after {
            content: '';
            position: fixed;
            bottom: -30%;
            left: -10%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.03) 0%, transparent 70%);
            pointer-events: none;
            z-index: -1;
        }
    </style>
</x-app-layout>
