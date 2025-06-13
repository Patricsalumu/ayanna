<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Prenom & Nom')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Téléphone avec sélection du pays (RDC par défaut) -->
        <div class="mt-4">
            <x-input-label for="phone" :value="__('Téléphone (Whatsapp)')" />
            <div class="flex">
                <select id="country_code" name="country_code" class="block rounded-l-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100 pr-2">
                    <option value="+243" selected>&#x1F1E8;&#x1F1E9; +243 (RDC)</option>
                    <option value="+33">&#x1F1EB;&#x1F1F7; +33 (France)</option>
                    <option value="+32">&#x1F1E7;&#x1F1EA; +32 (Belgique)</option>
                    <option value="+225">&#x1F1E8;&#x1F1EE; +225 (Côte d’Ivoire)</option>
                    <!-- Ajoutez d'autres pays si besoin -->
                </select>
                <x-text-input id="phone" class="block w-full rounded-r-md" type="text" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="ex : 0997554905" />
            </div>
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            <p class="text-xs text-gray-500 mt-1">Veuillez utiliser un numéro WhatsApp si possible.</p>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer Mot de passe')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Vous avez dèja un compte?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Créez') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
