<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-6 max-w-4xl w-full mx-auto" enctype="multipart/form-data">
        @csrf
        <div class="mb-4 text-center">
            <span class="text-[#3e2f24]">Vous avez un compte ?</span>
            <a  href="{{ route('login') }}" class="ml-2 text-[#7a6657] hover:underline font-semibold">
                {{ __('Connectez-vous ?') }}
            </a>
        </div>
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            <div class="space-y-4">
                <!-- Prénom & Nom -->
                <div>
                    <x-input-label for="name" :value="__('Prénom & Nom')" class="text-[#3e2f24]" />
                    <x-text-input id="name"
                                   class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                                   type="text"
                                   name="name"
                                   :value="old('name')"
                                   required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Mot de passe -->
                <div>
                    <x-input-label for="password" :value="__('Mot de passe')" class="text-[#3e2f24]" />
                    <x-text-input id="password"
                                   class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirmer le mot de passe -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirmer Mot de passe')" class="text-[#3e2f24]" />
                    <x-text-input id="password_confirmation"
                                   class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                                   type="password"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            <div class="space-y-4">
                <!-- Nom de l'entreprise -->
                <div>
                    <x-input-label for="entreprise_nom" :value="__('Nom Entreprise')" class="text-[#3e2f24] font-semibold" />
                    <x-text-input id="entreprise_nom"
                                   class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8] bg-white"
                                   type="text"
                                   name="entreprise_nom"
                                   :value="old('entreprise_nom')"
                                   required />
                    <x-input-error :messages="$errors->get('entreprise_nom')" class="mt-2" />
                </div>

                <!-- Logo de l'entreprise (optionnel) -->
                <div>
                    <x-input-label for="entreprise_logo" :value="__('Logo Entreprise (optionnel)')" class="text-[#3e2f24] font-semibold" />
                    <input id="entreprise_logo" type="file" name="entreprise_logo" accept="image/*"
                           class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8] bg-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-[#f3ede7] file:text-[#3e2f24] hover:file:bg-[#e5dbce]" />
                    <x-input-error :messages="$errors->get('entreprise_logo')" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="text-[#3e2f24]" />
                    <x-text-input id="email"
                                   class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                                   type="email"
                                   name="email"
                                   :value="old('email')"
                                   required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Téléphone avec Sélection du pays -->
                <div>
                    <x-input-label for="phone" :value="__('Téléphone (WhatsApp)')" class="text-[#3e2f24]" />
                    <div class="flex">
                        <select id="country_code" name="country_code"
                                class="rounded-l-md border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8] bg-[#faf8f5] pr-2">
                            <option value="+243" selected>&#x1F1E8;&#x1F1E9; +243</option>
                            <option value="+33">&#x1F1EB;&#x1F1F7; +33</option>
                            <option value="+32">&#x1F1E7;&#x1F1EA; +32</option>
                            <option value="+225">&#x1F1E8;&#x1F1EE; +225</option>
                        </select>
                        <x-text-input id="phone"
                                       class="block w-full rounded-r-md border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                                       type="text"
                                       name="phone"
                                       :value="old('phone')"
                                       required
                                       autocomplete="tel"
                                       placeholder="ex : 0997554905" />
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    <p class="text-xs text-[#7a6657] mt-1">Veuillez utiliser un numéro WhatsApp si possible.</p>
                </div>
            </div>
        </div>

        <!-- Lien de connexion + bouton -->
        <div class="flex items-center justify-center mt-8 space-x-3">
            <x-primary-button class="bg-[#d8c1a8] hover:bg-[#c7ae93] text-[#3e2f24] font-bold rounded px-4 py-2">
                {{ __('Créer le compte') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
