<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntreSortie extends Model
{
    use HasFactory;

    protected $table = 'entrees_sorties';

    protected $fillable = [
        'point_de_vente_id',
        'type', // 'actif' ou 'passif'
        'montant',
        'motif',
        'created_at',
        'updated_at',
    ];

    // Relation avec le point de vente
    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    // Relation avec le compte
    public function compte()
    {
        return $this->belongsTo(\App\Models\Compte::class, 'compte_id');
    }
}
