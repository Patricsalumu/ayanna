<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'compte_id',
        'commande_id',
        'panier_id',        // Ajout du lien avec le panier
        'montant',
        'montant_recu',     // Montant reçu du client
        'monnaie',          // Monnaie rendue au client
        'montant_restant',
        'mode',
        'mode_paiement',    // Mode de paiement spécifique pour le panier
        'date_paiement',
        'notes',
        'est_solde',
        'user_id',
        'statut'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_recu' => 'decimal:2',
        'monnaie' => 'decimal:2',
        'montant_restant' => 'decimal:2',
        'date_paiement' => 'date',
        'est_solde' => 'boolean'
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }
}
