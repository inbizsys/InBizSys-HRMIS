<div
    aria-live="polite"
    aria-atomic="false"
    style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"
    class="flex flex-col gap-3 w-96 pointer-events-none"
>
    @foreach ($toasts as $toast)
        @php
            $styles = match($toast['type']) {
                'success' => [
                    'bg'          => 'bg-emerald-500',
                    'accent'      => 'bg-emerald-300',
                    'icon_bg'     => 'bg-emerald-400/40',
                    'progress_bg' => 'bg-emerald-600/40',
                ],
                'error' => [
                    'bg'          => 'bg-red-500',
                    'accent'      => 'bg-red-300',
                    'icon_bg'     => 'bg-red-400/40',
                    'progress_bg' => 'bg-red-600/40',
                ],
                'warning' => [
                    'bg'          => 'bg-amber-500',
                    'accent'      => 'bg-amber-300',
                    'icon_bg'     => 'bg-amber-400/40',
                    'progress_bg' => 'bg-amber-600/40',
                ],
                default => [
                    'bg'          => 'bg-blue-500',
                    'accent'      => 'bg-blue-300',
                    'icon_bg'     => 'bg-blue-400/40',
                    'progress_bg' => 'bg-blue-600/40',
                ],
            };
        @endphp

        <div
            wire:key="toast-{{ $toast['id'] }}"
            x-data="{
                show: false,
                progress: 100,
                duration: 5000,
                interval: null,
                init() {
                    this.$nextTick(() => { this.show = true });
                    this.startTimer();
                },
                startTimer() {
                    const step = 50;
                    const decrement = (step / this.duration) * 100;
                    this.interval = setInterval(() => {
                        this.progress -= decrement;
                        if (this.progress <= 0) {
                            this.close();
                        }
                    }, step);
                },
                pauseTimer() {
                    clearInterval(this.interval);
                },
                resumeTimer() {
                    this.startTimer();
                },
                close() {
                    clearInterval(this.interval);
                    this.show = false;
                    setTimeout(() => $wire.dismiss({{ $toast['id'] }}), 350);
                }
            }"
            x-init="init()"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            @mouseenter="pauseTimer()"
            @mouseleave="resumeTimer()"
            class="pointer-events-auto relative overflow-hidden rounded-xl shadow-lg {{ $styles['bg'] }}"
        >
            {{-- Left accent strip --}}
            <div class="absolute left-0 top-0 bottom-0 w-1 {{ $styles['accent'] }}"></div>

            {{-- Main content row --}}
            <div class="flex items-start gap-3 pl-5 pr-4 py-4">

                {{-- Icon --}}
                <div class="shrink-0 mt-0.5">
                    <div class="w-8 h-8 rounded-full {{ $styles['icon_bg'] }} flex items-center justify-center">
                        @if ($toast['type'] === 'success')
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @elseif ($toast['type'] === 'error')
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @elseif ($toast['type'] === 'warning')
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Text --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-white">
                        {{ ucfirst($toast['type']) }}
                    </p>
                    <p class="text-sm text-white/85 mt-0.5 break-words leading-relaxed">
                        {{ $toast['message'] }}
                    </p>
                </div>

                {{-- Close button --}}
                <button
                    @click="close()"
                    class="shrink-0 ml-1 text-white/60 hover:text-white transition-colors mt-0.5"
                    aria-label="Dismiss notification"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Progress bar --}}
            <div class="absolute bottom-0 left-0 right-0 h-1 {{ $styles['progress_bg'] }}">
                <div
                    :style="'width: ' + progress + '%'"
                    class="h-full transition-all duration-75 ease-linear bg-white/50"
                ></div>
            </div>
        </div>
    @endforeach
</div>
