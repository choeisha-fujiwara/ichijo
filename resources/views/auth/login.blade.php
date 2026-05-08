<x-login-layout>
    <x-slot:page>ログイン</x-slot:page>
    <div class="msg err">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full login-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full login-input"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
        </div>

        <div class="mt-9 login-buttons">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
            <x-primary-button class="ms-3 login-button">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
</x-login-layout>