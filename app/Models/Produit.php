<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $appends = ['prix_vente'];

    protected $fillable = [
        'nom','image', 'description',
        'prix_achat',
        'categorie_id',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function salles()
    {
        return $this->belongsToMany(\App\Models\Salle::class, 'produit_salle')
            ->withPivot('prix')
            ->withTimestamps();
    }

    public function paniers()
    {
        return $this->belongsToMany(Panier::class, 'panier_produit')
            ->withPivot('quantite', 'prix')
            ->withTimestamps();
    }

    public function stockJournalier()
    {
        return $this->hasOne(\App\Models\StockJournalier::class)->latestOfMany();
    }

    public function prixPourSalle($salleId)
    {
        if (!$salleId) {
            return $this->getDefaultPrix();
        }

        if ($this->relationLoaded('salles')) {
            $salle = $this->salles->first(fn($s) => $s->id === (int) $salleId);
            return $salle?->pivot?->prix ?? $this->getDefaultPrix();
        }

        $salle = $this->salles()->where('salle_id', $salleId)->first();
        return $salle?->pivot?->prix ?? $this->getDefaultPrix();
    }

    public function getPrixVenteAttribute()
    {
        return $this->getDefaultPrix();
    }

    protected function getDefaultPrix()
    {
        if ($this->relationLoaded('salles')) {
            return $this->salles->first()?->pivot?->prix ?? 0;
        }

        $salle = $this->salles()->first();
        return $salle?->pivot?->prix ?? 0;
    }
}
?>