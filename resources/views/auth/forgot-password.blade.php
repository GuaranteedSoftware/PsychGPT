<x-guest-layout>
    <div class="forgot-password-container">
        <div class="image-container">
            <img src="/images/psych-gpt-white-logo.svg" class="logo-container">
        </div>
        <div class="form-container">
            <x-authentication-card>
                <div class="mb-4 font-bold text-gray">
                    <h1 class="form-image-main-title font-bold">Reset Your Password</h1>
                </div>
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </div>

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <x-validation-errors class="mb-4" />
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="block form-item">
                        <span class="text-gray-900 font-bold text-base">Email</span>
                        <label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-button class="main-button">
                            {{ __('Email Password Reset Link') }}
                        </x-button>
                    </div>
                </form>
            </x-authentication-card>
        </div>
    </div>
</x-guest-layout>
