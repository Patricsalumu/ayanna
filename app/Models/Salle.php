<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'entreprise_id'];

    /**
     * Relation avec les points de vente liés à cette salle (table pivot salle_point_de_vente)
     */
    public function pointDeVentes()
    {
        return $this->belongsToMany(\App\Models\PointDeVente::class, 'salle_point_de_vente');
    }

    public function pointsDeVente()
    {
        return $this->belongsToMany(\App\Models\PointDeVente::class, 'salle_point_de_vente');
    }
    
    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }
    public function tables()
    {
        return $this->hasMany(\App\Models\TableResto::class);
    }
}