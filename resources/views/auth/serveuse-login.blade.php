<x-layouts.guest-login>
    <style>
        @keyframes pin-shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-4px); }
            100% { transform: translateX(0); }
        }

        .pin-error-shake {
            animation: pin-shake 0.28s ease-in-out 1;
        }
    </style>

    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-[#3e2f24]">Connexion Serveuse / Superviseur</h2>
        <p class="text-sm text-[#7a6657] mt-2">Saisissez votre code PIN à 4 chiffres</p>
    </div>

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

    <form method="POST" action="{{ route('serveuse.login') }}" class="space-y-4" id="serveuse-login-form">
        @csrf
        <input type="hidden" name="serveuse_login" value="1">

        <div class="text-center">
            @php
                $hasPinError = $errors->has('password') || $errors->has('username');
            @endphp
            <input id="password"
                   type="password"
                   name="password"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   maxlength="4"
                   pattern="\d{4}"
                   required
                   class="w-full text-center text-3xl font-bold tracking-[0.4em] rounded-xl border px-4 py-4 focus:outline-none focus:ring-2 {{ $hasPinError ? 'border-red-500 focus:ring-red-300' : 'border-[#d8c1a8] focus:ring-[#d8c1a8]' }}"
                   placeholder="••••"
            />
            @unless(session('blocked_error'))
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            @endunless
        </div>

        <div class="grid grid-cols-3 gap-3 mt-6">
            @foreach([1,2,3,4,5,6,7,8,9] as $digit)
                <button type="button" data-digit="{{ $digit }}" class="digit-btn h-14 rounded-xl bg-[#f7efe7] text-2xl font-bold text-[#3e2f24] shadow-sm hover:bg-[#efe1cf]">{{ $digit }}</button>
            @endforeach
            <button type="button" data-action="clear" class="h-14 rounded-xl bg-[#e9d8c4] text-sm font-semibold text-[#3e2f24]">Effacer</button>
            <button type="button" data-digit="0" class="digit-btn h-14 rounded-xl bg-[#f7efe7] text-2xl font-bold text-[#3e2f24] shadow-sm hover:bg-[#efe1cf]">0</button>
        </div>

        <button type="submit" data-loading-text="Connexion..." class="w-full rounded-xl bg-[#d8c1a8] px-4 py-3 font-bold text-[#3e2f24] shadow-sm transition hover:bg-[#c7ae93]">
            Se connecter
        </button>
    </form>

    <div class="mt-4 pt-4 border-t border-[#eadbc9] text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full rounded-xl border border-[#d8c1a8] bg-white px-4 py-3 text-sm font-semibold text-[#3e2f24] hover:bg-[#f7efe7] transition">
            Se connecter en tant que caissier ou admin
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('password');
            const form = document.getElementById('serveuse-login-form');
            const hasPinError = @json($errors->has('password') || $errors->has('username'));

            if (input && hasPinError) {
                input.classList.add('pin-error-shake');
                input.focus();
            }

            document.querySelectorAll('.digit-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!input) return;
                    const value = input.value || '';
                    if (value.length >= 4) return;
                    input.value = value + (btn.getAttribute('data-digit') || '');
                    if (input.value.length === 4) {
                        form.requestSubmit();
                    }
                });
            });
            document.querySelectorAll('[data-action="clear"]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (input) input.value = '';
                });
            });
            if (form && input) {
                form.addEventListener('submit', function (e) {
                    if ((input.value || '').length !== 4) {
                        e.preventDefault();
                        input.focus();
                    }
                });
            }
        });
    </script>
</x-layouts.guest-login>
