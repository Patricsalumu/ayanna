<x-layouts.guest-login>
    <!-- Lien de création de compte -->
    <div class="mb-4 text-center">
        <span class="text-[#3e2f24]">Vous n'avez pas de compte ?</span>
        <a href="{{ route('register') }}" class="ml-2 text-[#7a6657] hover:underline font-semibold">Créer un compte</a>
    </div>

    <!-- Message de Session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('blocked_error'))
        <div id="blockedEnterpriseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 px-4 py-6" role="dialog" aria-modal="true" aria-labelledby="blockedEnterpriseTitle">
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center gap-3 bg-red-700 px-6 py-5 text-white">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/15 text-xl">!</div>
                    <div>
                        <h2 id="blockedEnterpriseTitle" class="text-lg font-bold">Accès suspendu</h2>
                        <p class="text-sm text-red-100">Votre compte ne peut pas continuer la connexion.</p>
                    </div>
                </div>
                <div class="space-y-5 px-6 py-6 text-sm text-slate-700">
                    <section>
                        <h3 class="mb-1 font-bold text-slate-900">Français</h3>
                        <p>Votre entreprise a été bloquée. Vous ne pourrez pas continuer à utiliser l’application Ayanna Web.</p>
                        <p class="mt-2 font-semibold">Contact Ayanna ERP : <a href="tel:+243997554905" class="text-red-700 hover:underline">+243 997 554 905</a></p>
                    </section>
                    <section class="border-t border-slate-200 pt-4">
                        <h3 class="mb-1 font-bold text-slate-900">English</h3>
                        <p>Your company has been blocked. You cannot continue using Ayanna Web.</p>
                        <p class="mt-2 font-semibold">Contact Ayanna ERP: <a href="tel:+243997554905" class="text-red-700 hover:underline">+243 997 554 905</a></p>
                    </section>
                    <div class="rounded-xl border border-red-300 bg-red-50 p-4 font-semibold leading-relaxed text-red-800">
                        Attention : toute utilisation non autorisée ou toute forme de piratage expose votre établissement à des poursuites judiciaires.<br>
                        Warning: unauthorized use or piracy may expose your establishment to legal proceedings.
                    </div>
                </div>
                <div class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="document.getElementById('blockedEnterpriseModal').remove()" class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire de connexion -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Identifiant -->
        <div>
            <x-input-label for="username" :value="__('Email ou nom d’utilisateur')" class="text-[#3e2f24]" />
            <x-text-input id="username"
                           class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                           type="text"
                           name="username"
                           :value="old('username')"
                           required autofocus autocomplete="username" />
            @unless(session('blocked_error'))
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            @endunless
        </div>

        <!-- Mot de passe -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Mot de passe')" class="text-[#3e2f24]" />
            <x-text-input id="password" 
                           class="block mt-1 w-full rounded border border-[#d8c1a8] focus:outline-none focus:ring focus:border-[#d8c1a8]"
                           type="password" 
                           name="password" 
                           required autocomplete="current-password" />
            @unless(session('blocked_error'))
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            @endunless
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
            <x-primary-button data-loading-text="Connexion..." class="bg-[#d8c1a8] hover:bg-[#c7ae93] text-[#3e2f24] font-bold rounded px-4 py-2">
                {{ __('Se Connecter') }}
            </x-primary-button>
        </div>

        <div class="mt-4 pt-4 border-t border-[#eadbc9] text-center">
            <a href="{{ route('serveuse.login') }}" class="inline-flex items-center justify-center w-full rounded-xl border border-[#d8c1a8] bg-white px-4 py-3 text-sm font-semibold text-[#3e2f24] hover:bg-[#f7efe7] transition">
                Se connecter en tant que serveuse ou superviseur
            </a>
        </div>
    </form>
</x-layouts.guest-login>
