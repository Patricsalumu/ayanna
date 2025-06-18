<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'commandes';

    public $timestamps = false; // Désactive la gestion automatique des timestamps

    protected $fillable = [
        'panier_id', 'mode_paiement', 'statut', 'created_at'
    ];

    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }
}
