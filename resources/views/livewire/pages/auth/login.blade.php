<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth-split')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex justify-center min-h-screen bg-white">
    <!-- Left Half - Form Section (50%) -->
    <div class="bg-white w-full lg:w-1/2 flex items-center justify-center h-screen">
        <div class="w-full max-w-md px-8 xl:px-12">
            <!-- Logo -->
            <div class="mb-12 flex justify-center lg:justify-start">
                <img src="{{ asset('src/images/logo/client-logo.png') }}" alt="Logo" width="200" class="h-auto"/>
            </div>

            <!-- Form Header  -->
            <div class="mb-8">
               <!--  <h1 class="mb-2 text-[#0F172A] text-3xl font-bold sm:text-title-md">
                    Sign In
                </h1> -->
                <p class="text-[15px] text-[#94A3B8]">
                    Enter your email and password to sign in!
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login">
                <!-- Email Address -->
                <div class="mt-4 space-y-1.5">
                    <label for="email" class="block text-[15px] font-medium text-[#475569]">{{ __('Email') }}</label>
                    <input id="email" class="block w-full px-4 py-2.5 text-gray-900 border border-[#CBD5E1] rounded-md focus:ring-blue-500 focus:border-blue-500 transition-colors" type="email" wire:model="form.email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mt-5 space-y-1.5">
                    <label for="password" class="block text-[15px] font-medium text-[#475569]">{{ __('Password') }}</label>
                    <input id="password" class="block w-full px-4 py-2.5 text-gray-900 border border-[#CBD5E1] rounded-md focus:ring-blue-500 focus:border-blue-500 transition-colors" type="password" wire:model="form.password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
                </div>

                <!-- Remember Me -->
                <div class="block mt-5">
                    <label for="remember" class="flex items-center cursor-pointer max-w-max">
                        <input wire:model="form.remember" id="remember" type="checkbox" class="w-4 h-4 rounded border-[#CBD5E1] text-[#0F172A] focus:ring-[#0F172A]" name="remember">
                        <span class="ms-2 text-[15px] text-[#94A3B8]">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    @if (Route::has('password.request'))
                        <a class="text-[15px] text-[#94A3B8] hover:text-[#475569] underline focus:outline-none transition-colors" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif

                    <button type="submit" class="px-8 py-2.5 bg-[#0F172A] text-white rounded-[6px] hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-[#0F172A] focus:ring-offset-2 font-medium text-base">
                        {{ __('Log in') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Half - Background/Logo Section (50%) -->
    <div class="bg-blue-800 relative w-full lg:w-1/2 h-screen hidden lg:flex items-center justify-center overflow-hidden" style="background-color: #161950;">
        <div class="absolute right-0 top-0 -z-1 w-[250px] xl:w-[450px]">
            <img src="{{ asset('src/images/shape/grid-01.svg') }}" alt="grid" class="w-full h-auto opacity-70" />
        </div>
        <div class="absolute bottom-0 left-0 -z-1 w-full max-w-[250px] rotate-180 xl:max-w-[450px]">
            <img src="{{ asset('src/images/shape/grid-01.svg') }}" alt="grid" class="w-full h-auto opacity-70" />
        </div>

        <div class="flex flex-col items-center max-w-md z-10 p-8">
            <a href="#" class="block mb-4 transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('src/images/logo/auth-logo.png') }}" alt="Logo" class="max-w-full drop-shadow-xl" />
            </a>
        </div>
    </div>
</div>
