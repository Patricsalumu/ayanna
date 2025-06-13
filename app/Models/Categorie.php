<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'entreprise_id'];

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function pointsDeVente()
    {
        return $this->belongsToMany(\App\Models\PointDeVente::class, 'categorie_point_de_vente');
    }
    
    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }
}
?>