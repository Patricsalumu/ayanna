<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\PointDeVente;
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
        if ($user && in_array(strtolower((string) $user->role), ['serveuse', 'caissier', 'caissier1', 'caissier2', 'comptoiriste', 'administrateur', 'admin', 'super_admin'], true)) {
            if (strtolower((string) $user->role) === 'serveuse') {
                return redirect()->intended($this->getServeusePlanVenteRoute());
            }

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
            return redirect()->intended($this->getServeusePlanVenteRoute());
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('serveuse.login')->withErrors(['password' => 'Accès réservé aux serveuses.']);
    }

    protected function getServeusePlanVenteRoute(): string
    {
        $user = Auth::user();
        $query = PointDeVente::query()->where('entreprise_id', $user?->entreprise_id);
        $assignedIds = $user?->pointsDeVente()->pluck('points_de_vente.id') ?? collect();

        if ($assignedIds->isNotEmpty()) {
            $query->whereIn('id', $assignedIds);
        }

        $pointDeVente = $query->orderBy('nom')->first();

        if (!$pointDeVente) {
            return route('dashboard', absolute: false);
        }

        $salle = $pointDeVente->salles()->first();

        if (!$salle) {
            return route('dashboard', absolute: false);
        }

        return route('salle.plan.vente', [
            'entreprise' => optional($pointDeVente->entreprise)->id ?? $pointDeVente->entreprise_id,
            'salle' => $salle->id,
            'point_de_vente_id' => $pointDeVente->id,
        ], false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->boolean('serveuse_logout')) {
            return redirect()->route('serveuse.login');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
