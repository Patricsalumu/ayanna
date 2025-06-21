<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom','image', 'description',
        'prix_achat', 'prix_vente',
        'categorie_id',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }
    public function paniers()
    {
        return $this->belongsToMany(Panier::class, 'panier_produit')
            ->withPivot('quantite')
            ->withTimestamps();
    }
}
?>