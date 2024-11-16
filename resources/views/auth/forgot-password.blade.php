<x-guest-layout>
    <x-slot:page>Login</x-slot:page>  
    <div class="mb-4 text-sm text-gray-600 forgot-text">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>        
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full login-input" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4 password-send">
            <x-primary-button class="login-button">
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
</x-guest-layout>
