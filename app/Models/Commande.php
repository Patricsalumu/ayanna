<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'commandes';

    public $timestamps = false; // Désactive la gestion automatique des timestamps

    protected $fillable = [
        'panier_id', 
        'mode_paiement', 
        'statut', 
        'created_at'
    ];

    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function getTotalPayeAttribute()
    {
        return $this->paiements->sum('montant');
    }

    public function getMontantRestantAttribute()
    {
        $montantTotal = $this->montant ?? ($this->panier && $this->panier->produits ? 
            $this->panier->produits->sum(fn($p) => $p->pivot->quantite * $p->prix_vente) : 0);
        return $montantTotal - $this->total_paye;
    }

    public function getEstSoldeeAttribute()
    {
        return $this->montant_restant <= 0;
    }
}
