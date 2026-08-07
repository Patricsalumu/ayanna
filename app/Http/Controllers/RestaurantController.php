<?php

namespace App\Http\Controllers;

use App\Models\PointDeVente;
use App\Models\TableResto;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    public function __construct(protected PermissionService $permissionService)
    {
    }

    public function home(Request $request)
    {
        $user = Auth::user();
        $pointDeVente = PointDeVente::query()->first();

        if (!$pointDeVente) {
            return redirect()->route('dashboard');
        }

        $salle = $pointDeVente->salles()->first();

        if (!$salle) {
            return redirect()->route('dashboard');
        }

        if ($this->permissionService->isWaitress($user) || $this->permissionService->isCashier($user)) {
            return redirect()->route('salle.plan.vente', [
                'entreprise' => optional($pointDeVente->entreprise)->id ?? $pointDeVente->entreprise_id,
                'salle' => $salle->id,
                'point_de_vente_id' => $pointDeVente->id,
            ]);
        }

        return redirect()->route('dashboard');
    }
}
