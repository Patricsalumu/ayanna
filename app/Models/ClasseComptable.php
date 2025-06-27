<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClasseComptable extends Model
{
    use HasFactory;

    protected $table = 'classes_comptables';

    protected $fillable = [
        'numero',
        'nom',
        'description',
        'type_document',
        'type_nature',
        'est_principale',
        'classe_parent',
        'ordre_affichage',
        'entreprise_id'
    ];

    protected $casts = [
        'est_principale' => 'boolean',
        'ordre_affichage' => 'integer'
    ];

    /**
     * Relation avec les comptes
     */
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }

    /**
     * Classe parent (pour les sous-classes)
     */
    public function parent()
    {
        return $this->belongsTo(ClasseComptable::class, 'classe_parent', 'numero');
    }

    /**
     * Sous-classes
     */
    public function sousClasses()
    {
        return $this->hasMany(ClasseComptable::class, 'classe_parent', 'numero');
    }

    /**
     * Scope pour les classes principales (1-7)
     */
    public function scopePrincipales($query)
    {
        return $query->where('est_principale', true)->orderBy('ordre_affichage');
    }

    /**
     * Scope pour les classes du bilan
     */
    public function scopeBilan($query)
    {
        return $query->where('type_document', 'bilan');
    }

    /**
     * Scope pour les classes du compte de résultat
     */
    public function scopeResultat($query)
    {
        return $query->where('type_document', 'resultat');
    }

    /**
     * Scope pour les charges
     */
    public function scopeCharges($query)
    {
        return $query->where('type_nature', 'charge');
    }

    /**
     * Scope pour les produits
     */
    public function scopeProduits($query)
    {
        return $query->where('type_nature', 'produit');
    }

    /**
     * Vérifier si c'est une classe de charge
     */
    public function estCharge()
    {
        return $this->type_nature === 'charge';
    }

    /**
     * Vérifier si c'est une classe de produit
     */
    public function estProduit()
    {
        return $this->type_nature === 'produit';
    }

    /**
     * Vérifier si c'est une classe d'actif
     */
    public function estActif()
    {
        return $this->type_nature === 'actif';
    }

    /**
     * Vérifier si c'est une classe de passif
     */
    public function estPassif()
    {
        return $this->type_nature === 'passif';
    }
}
