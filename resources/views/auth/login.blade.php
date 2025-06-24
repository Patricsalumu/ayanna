<x-layouts.guest-login>
    <!-- Lien de création de compte -->
    <div class="mb-4 text-center">
        <span class="text-[#3e2f24]">Vous n'avez pas de compte ?</span>
        <a href="{{ route('register') }}" class="ml-2 text-[#7a6657] hover:underline font-semibold">Créer un compte</a>
    </div>

    <!-- Message de Session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Formulaire de connexion -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-[#3e2f24]" />
            <x-text-input id="email" 
                           class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                           type="email" 
                           name="email" 
                           :value="old('email')" 
                           required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" class="text-[#3e2f24]" />
            <x-text-input id="password" 
                           class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                           type="password" 
                           name="password" 
                           required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Se souvenir de moi -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" 
                       type="checkbox" 
                       class="rounded border-[#d8c1a8] text-[#3e2f24] focus:ring-[#d8c1a8]"
                       name="remember">
                <span class="ml-2 text-sm text-[#7a6657]">{{ __('Se souvenir de moi') }}</span>
            </label>
        </div>
        <!-- Liens + Bouton de connexion -->
        <div class="flex items-center justify-end mt-4 space-x-3">
            @if (Route::has('password.request'))
                <a class="text-sm text-[#7a6657] hover:underline" 
                   href="#">
                    {{ __('Mot de passe oublié ?') }}
                </a>
            @endif
            <x-primary-button class="bg-[#d8c1a8] hover:bg-[#c7ae93] text-[#3e2f24] font-bold rounded px-4 py-2">
                {{ __('Se Connecter') }}
            </x-primary-button>
        </div>
    </form>
</x-layouts.guest-login>
