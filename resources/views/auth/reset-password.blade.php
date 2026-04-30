<x-login-layout>
    <x-slot:page>パスワード再設定</x-slot:page>
    <div class="msg err">
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full login-input" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
        </div>
        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full login-input" type="password" name="password" required autocomplete="new-password" />
        </div>
        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full login-input"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

        </div>

        <div class="flex items-center justify-end mt-4 password-send">
            <x-primary-button class="login-button">
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-login-layout>