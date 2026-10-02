<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'entreprise_id',
        'compte_vente_id',
        'compte_stock_id',
        'compte_variation_stock_id',
    ];

    public function compteVente()
    {
        return $this->belongsTo(Compte::class, 'compte_vente_id');
    }

    public function compteStock()
    {
        return $this->belongsTo(Compte::class, 'compte_stock_id');
    }

    public function compteVariationStock()
    {
        return $this->belongsTo(Compte::class, 'compte_variation_stock_id');
    }

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