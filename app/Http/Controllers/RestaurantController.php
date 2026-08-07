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

        if ($this->permissionService->isWaitress($user)) {
            $tables = TableResto::query()
                ->where('serveuse_id', $user->id)
                ->orderBy('numero')
                ->get();

            return view('restaurant.home', compact('user', 'tables', 'pointDeVente'));
        }

        if ($this->permissionService->isCashier($user)) {
            $tables = TableResto::query()->orderBy('numero')->get();

            return view('restaurant.home', compact('user', 'tables', 'pointDeVente'));
        }

        return redirect()->route('dashboard');
    }
}
