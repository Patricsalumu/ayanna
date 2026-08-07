<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Panier;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function validatePayment(array $data, ?object $user): array
    {
        return DB::transaction(function () use ($data, $user) {
            $panier = Panier::where('table_id', $data['table_id'])->where('status', 'en_cours')->firstOrFail();
            $commande = Commande::create([
                'panier_id' => $panier->id,
                'mode_paiement' => $data['mode_paiement'],
                'statut' => 'validé',
            ]);

            $panier->forceFill(['status' => 'validé'])->save();

            return [
                'commande' => $commande,
                'panier' => $panier,
            ];
        });
    }
}
