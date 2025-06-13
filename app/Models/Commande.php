<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'commandes';

    protected $fillable = [
        'utilisateur_id', 'point_de_vente_id', 'client_id', 'table_id', 'date_commande', 'statut'
    ];

    public function pointDeVente()
    {
        return $this->belongsTo(\App\Models\PointDeVente::class, 'point_de_vente_id');
    }
}
