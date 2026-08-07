<?php

namespace App\Http\Controllers;

use App\Models\TableResto;
use Illuminate\Http\Request;

class TableRestoController extends Controller
{
    /**
     * Met à jour une table du restaurant.
     */
    public function update(Request $request, TableResto $table)
    {
        $rules = [
            'numero' => 'nullable|integer|min:1',
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
        $validated = $request->validate([
            'salle_id' => 'required|exists:salles,id',
            'numero' => 'required|integer',
            'forme' => 'required|string',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'serveuse_id' => 'nullable|exists:users,id',
        ]);

        $table = TableResto::create($validated);

        return response()->json(['success' => true, 'table' => $table]);
    }

    /**
     * Supprime une table du restaurant.
     */
    public function destroy(TableResto $table)
    {
        $table->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Récupère les tables d'une salle spécifique.
     */
    public function getTablesBySalle($salleId)
    {
        $tables = TableResto::where('salle_id', $salleId)->get();

        return response()->json($tables);
    }
}
