<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function stockPourPointDeVente($pointDeVenteId): int
    {
        if (!$pointDeVenteId) {
            return 0;
        }

        $stock = StockJournalier::where('produit_id', $this->id)
            ->where('point_de_vente_id', $pointDeVenteId)
            ->orderByDesc('date')
            ->orderByDesc('session')
            ->orderByDesc('id')
            ->first();

        if (!$stock) {
            return 0;
        }

        return (int) ((int) ($stock->quantite_initiale ?? 0)
            + (int) ($stock->quantite_ajoutee ?? 0)
            - (int) ($stock->quantite_vendue ?? 0));
    }

    public function quantiteReserveeDansPaniersEnCours($pointDeVenteId, $excludePanierId = null): int
    {
        if (!$pointDeVenteId) {
            return 0;
        }

        return (int) DB::table('panier_produit')
            ->join('paniers', 'paniers.id', '=', 'panier_produit.panier_id')
            ->where('paniers.point_de_vente_id', $pointDeVenteId)
            ->where('paniers.status', 'en_cours')
            ->where('panier_produit.produit_id', $this->id)
            ->when($excludePanierId, fn ($query) => $query->where('paniers.id', '!=', $excludePanierId))
            ->sum('panier_produit.quantite');
    }

    public function stockDisponiblePourPointDeVente($pointDeVenteId, $excludePanierId = null): int
    {
        return max(0, $this->stockPourPointDeVente($pointDeVenteId)
            - $this->quantiteReserveeDansPaniersEnCours($pointDeVenteId, $excludePanierId));
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