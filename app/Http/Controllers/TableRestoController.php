<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableResto;

class TableRestoController extends Controller
{
    /**
     * Met à jour une table du restaurant.
     */
    public function update(Request $request, TableResto $table)
    {
        // On valide uniquement les champs présents dans la requête
        $rules = [
            'numero' => 'integer|min:1',
            'position_x' => 'numeric',
            'position_y' => 'numeric',
            'width' => 'numeric|min:1',
            'height' => 'numeric|min:1',
            'forme' => 'string',
        ];
        $fields = array_intersect(array_keys($request->all()), array_keys($rules));
        $validated = $request->validate(array_intersect_key($rules, array_flip($fields)));
        $table->update($validated);
        return response()->json(['success' => true, 'table' => $table]);
    }
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
    ]);

    $table = TableResto::create($validated);

    return response()->json($table);
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
?>