<x-layouts.guest-login>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-[#3e2f24]">Connexion Serveuse</h2>
        <p class="text-sm text-[#7a6657] mt-2">Saisissez votre code PIN à 4 chiffres</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('serveuse.login') }}" class="space-y-4" id="serveuse-login-form">
        @csrf
        <input type="hidden" name="serveuse_login" value="1">

        <div class="text-center">
            <input id="password"
                   type="password"
                   name="password"
                   inputmode="numeric"
                   autocomplete="one-time-code"
                   maxlength="4"
                   pattern="\d{4}"
                   required
                   class="w-full text-center text-3xl font-bold tracking-[0.4em] rounded-xl border border-[#d8c1a8] px-4 py-4 focus:outline-none focus:ring-2 focus:ring-[#d8c1a8]"
                   placeholder="••••"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="grid grid-cols-3 gap-3 mt-6">
            @foreach([1,2,3,4,5,6,7,8,9] as $digit)
                <button type="button" data-digit="{{ $digit }}" class="digit-btn h-14 rounded-xl bg-[#f7efe7] text-2xl font-bold text-[#3e2f24] shadow-sm hover:bg-[#efe1cf]">{{ $digit }}</button>
            @endforeach
            <button type="button" data-action="clear" class="h-14 rounded-xl bg-[#e9d8c4] text-sm font-semibold text-[#3e2f24]">Effacer</button>
            <button type="button" data-digit="0" class="digit-btn h-14 rounded-xl bg-[#f7efe7] text-2xl font-bold text-[#3e2f24] shadow-sm hover:bg-[#efe1cf]">0</button>
            <button type="submit" class="h-14 rounded-xl bg-[#3e2f24] text-white text-sm font-semibold">Entrer</button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('password');
            const form = document.getElementById('serveuse-login-form');
            document.querySelectorAll('.digit-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!input) return;
                    const value = input.value || '';
                    if (value.length >= 4) return;
                    input.value = value + (btn.getAttribute('data-digit') || '');
                    if (input.value.length === 4) {
                        form.submit();
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
