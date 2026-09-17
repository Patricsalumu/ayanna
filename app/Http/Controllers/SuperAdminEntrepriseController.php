<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminEntrepriseController extends Controller
{
    public function index()
    {
        abort_unless($this->isSuperAdmin(), 403);

        $entreprises = Entreprise::withCount('users')->orderBy('nom')->get();

        return view('super-admin.entreprises.index', compact('entreprises'));
    }

    public function updateBlocked(Request $request, Entreprise $entreprise)
    {
        abort_unless($this->isSuperAdmin(), 403);

        $blocked = $request->boolean('blocked');
        $entreprise->update(['blocked' => $blocked]);

        if ($blocked) {
            DB::table('sessions')
                ->whereIn('user_id', $entreprise->users()->pluck('id'))
                ->delete();
        }

        return redirect()->route('super-admin.entreprises.index')
            ->with('success', $blocked
                ? "L’entreprise {$entreprise->nom} a été bloquée et ses sessions ont été déconnectées."
                : "L’entreprise {$entreprise->nom} a été débloquée.");
    }

    private function isSuperAdmin(): bool
    {
        return strtolower(trim((string) auth()->user()?->role)) === 'super_admin';
    }
}
