<x-guest-layout>
    <div class="login-container">
        <div class="image-container">
            <img src="/images/psych-gpt-white-logo.svg" class="logo-container">
            <h1 class="form-image-main-title font-bold">Log in to your Account</h1>
        </div>
        <div class="form-container">
            <x-authentication-card>
                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="form-wrapper">
                    @csrf

                    <div class="mb-4 font-bold text-gray">
                        <h3>Sign In</h3>
                    </div>

                    <div class="form-item">
                        <span class="text-gray-900 font-bold text-base">Email</span>
                        <label for="email" value="{{ __('Email') }}" />
                        <x-input id="email" class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    </div>

                    <div class="mt-4 form-item">
                        <span class="text-gray-900 font-bold text-base">Password</span>
                        <label for="password" value="{{ __('Password') }}" />
                        <x-input id="password" class="w-full mt-1 text-sm focus:border-purple-400 focus:outline-none focus:shadow-outline-purple form-field" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="block mt-4 form-group">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" name="remember" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Create account') }}
                            </a>
                        @endif

                    </div>

                    <div class="flex items-start justify-start flex-nowrap flex-col gap-6 mt-4">
                        <x-button class="main-button">
                            {{ __('Log in') }}
                        </x-button>
                    </div>
                </form>
            </x-authentication-card>
        </div>

    </div>
</x-guest-layout>
