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
        'montant',
        'montant_restant',
        'mode',
        'date_paiement',
        'notes',
        'est_solde',
        'user_id',
        'statut'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
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
}
