<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function createServeuse(): View
    {
        return view('auth.serveuse-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && in_array(strtolower((string) $user->role), ['serveuse', 'caissier', 'administrateur', 'admin', 'super_admin'], true)) {
            return redirect()->intended(route('restaurant.staff.home', absolute: false));
        }

        return redirect()->intended(route('entreprises.show', $user->entreprise_id ?? 0, absolute: false));
    }

    public function storeServeuse(LoginRequest $request): RedirectResponse
    {
        $request->merge(['serveuse_login' => true]);
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && strtolower((string) $user->role) === 'serveuse') {
            return redirect()->intended(route('restaurant.staff.home', absolute: false));
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('serveuse.login')->withErrors(['password' => 'Accès réservé aux serveuses.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('serveuse.login');
    }
}
