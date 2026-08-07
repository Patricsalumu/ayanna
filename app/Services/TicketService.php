<?php

namespace App\Services;

use App\Models\BonCommande;
use Illuminate\Support\Carbon;

class TicketService
{
    public function createTicket(array $data): BonCommande
    {
        return BonCommande::create([
            'numero_bon' => $this->nextNumber(),
            'panier_id' => $data['panier_id'],
            'serveuse_id' => $data['serveuse_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'utilisateur_id' => $data['utilisateur_id'] ?? null,
            'produits_json' => $data['produits_json'] ?? [],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    public function nextNumber(): int
    {
        $today = Carbon::now()->startOfDay();
        $last = BonCommande::whereBetween('created_at', [$today, Carbon::now()->endOfDay()])
            ->orderByDesc('numero_bon')->first();

        return $last ? $last->numero_bon + 1 : 1;
    }
}
