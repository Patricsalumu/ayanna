<?php

namespace App\Http\Controllers;

use App\Models\TableResto;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableRestoController extends Controller
{
    public function __construct(protected PermissionService $permissionService)
    {
    }

    /**
     * Met à jour une table du restaurant.
     */
    public function update(Request $request, TableResto $table)
    {
        abort_unless(
            $this->permissionService->isAdmin(Auth::user())
                || $this->permissionService->canAccessTable(Auth::user(), $table),
            403,
            'Cette table ne vous est pas affectée.'
        );

        $rules = [
            'numero' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('table_restos', 'numero')->where(fn ($query) => $query->where('salle_id', $table->salle_id))->ignore($table->id),
            ],
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric|min:1',
            'height' => 'nullable|numeric|min:1',
            'forme' => 'nullable|string',
            'serveuse_id' => 'nullable|exists:users,id',
        ];

        $validated = $request->validate($rules);
        $table->fill($validated);
        $table->save();

        return response()->json(['success' => true, 'table' => $table->fresh()]);
    }

    /**
     * Crée une nouvelle table du restaurant.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'salle_id' => 'required|exists:salles,id',
            'numero' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('table_restos', 'numero')->where(function ($query) use ($request) {
                    return $query->where('salle_id', $request->input('salle_id'));
                }),
            ],
            'forme' => 'required|string',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'serveuse_id' => 'nullable|exists:users,id',
        ]);

        if (empty($validated['numero'])) {
            $maxNumero = TableResto::where('salle_id', $validated['salle_id'])->max('numero');
            $validated['numero'] = ((int) $maxNumero) + 1;
        }

        if (empty($validated['width'])) {
            $validated['width'] = 80;
        }

        if (empty($validated['height'])) {
            $validated['height'] = 80;
        }

        if (!$this->permissionService->isAdmin($user)) {
            $validated['serveuse_id'] = $user->id;
        }

        $table = TableResto::create($validated);

        return response()->json(['success' => true, 'table' => $table]);
    }

    /**
     * Supprime une table du restaurant.
     */
    public function destroy(TableResto $table)
    {
        abort_unless(
            $this->permissionService->isAdmin(Auth::user())
                || $this->permissionService->canAccessTable(Auth::user(), $table),
            403,
            'Cette table ne vous est pas affectée.'
        );

        $table->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Récupère les tables d'une salle spécifique.
     */
    public function getTablesBySalle($salleId)
    {
        $query = TableResto::where('salle_id', $salleId);
        if (!$this->permissionService->isAdmin(Auth::user())) {
            $query->where('serveuse_id', Auth::id());
        }
        $tables = $query->get();

        return response()->json($tables);
    }
}
