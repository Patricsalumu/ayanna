<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    protected $fillable = [
        'numero', 
        'nom', 
        'type', 
        'description', 
        'entreprise_id', 
        'user_id',
        'classe_comptable_id',
        'solde_debit',
        'solde_credit'
    ];

    protected $casts = [
        'solde_debit' => 'decimal:2',
        'solde_credit' => 'decimal:2',
        'solde' => 'decimal:2'
    ];

    /**
     * Relation avec la classe comptable
     */
    public function classeComptable()
    {
        return $this->belongsTo(ClasseComptable::class);
    }

    public function entreesSorties()
    {
        return $this->hasMany(EntreeSortie::class);
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class);
    }

    /**
     * Relations pour les écritures au débit et au crédit
     */
    public function ecrituresDebit()
    {
        return $this->hasMany(EcritureComptable::class)->where('debit', '>', 0);
    }

    public function ecrituresCredit()
    {
        return $this->hasMany(EcritureComptable::class)->where('credit', '>', 0);
    }

    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Relations pour les points de vente
    public function pointsDeVenteCaisse()
    {
        return $this->hasMany(PointDeVente::class, 'compte_caisse_id');
    }

    public function pointsDeVenteVente()
    {
        return $this->hasMany(PointDeVente::class, 'compte_vente_id');
    }

    public function pointsDeVenteClient()
    {
        return $this->hasMany(PointDeVente::class, 'compte_client_id');
    }

    /**
     * Calcule le solde du compte (solde initial + mouvements)
     */
    public function getSoldeAttribute()
    {
        $mouvementsDebit = $this->ecritures()->sum('debit');
        $mouvementsCredit = $this->ecritures()->sum('credit');
        
        // Pour les comptes d'actif, le solde est : solde initial + débits - crédits
        // Pour les comptes de passif, le solde est : solde initial + crédits - débits
        if ($this->type === 'actif') {
            return $this->solde_initial + $mouvementsDebit - $mouvementsCredit;
        } else {
            return $this->solde_initial + $mouvementsCredit - $mouvementsDebit;
        }
    }

    /**
     * Calcule le solde à une date donnée
     */
    public function getSoldeAuAttribute($date)
    {
        $mouvementsDebit = $this->ecritures()
            ->whereHas('journal', function($q) use ($date) {
                $q->where('date_ecriture', '<=', $date);
            })
            ->sum('debit');
            
        $mouvementsCredit = $this->ecritures()
            ->whereHas('journal', function($q) use ($date) {
                $q->where('date_ecriture', '<=', $date);
            })
            ->sum('credit');

        if ($this->type === 'actif') {
            return $this->solde_initial + $mouvementsDebit - $mouvementsCredit;
        } else {
            return $this->solde_initial + $mouvementsCredit - $mouvementsDebit;
        }
    }

    /**
     * Scope pour les comptes d'une classe donnée
     */
    public function scopeClasse($query, $classe)
    {
        return $query->where('classe_comptable', $classe);
    }

    /**
     * Scope pour les comptes collectifs
     */
    public function scopeCollectifs($query)
    {
        return $query->where('est_collectif', true);
    }
}
