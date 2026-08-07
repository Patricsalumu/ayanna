<?php

namespace App\Services;

use App\Models\TableResto;
use Illuminate\Database\Eloquent\Collection;

class TableService
{
    public function __construct(protected PermissionService $permissionService)
    {
    }

    public function getVisibleTablesForUser(?object $user, ?int $salleId = null): Collection
    {
        $query = TableResto::query();

        if ($salleId) {
            $query->where('salle_id', $salleId);
        }

        if ($this->permissionService->isWaitress($user)) {
            $query->where('serveuse_id', $user->id ?? 0);
        }

        return $query->orderBy('numero')->get();
    }

    public function assignTableToWaitress(TableResto $table, int $serveuseId): TableResto
    {
        $table->forceFill(['serveuse_id' => $serveuseId])->save();

        return $table;
    }
}
