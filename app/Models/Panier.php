<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;

    protected $table = 'paniers';

    protected $fillable = [
        'table_id',
        'point_de_vente_id',
        'client_id',
        'serveuse_id',
        'opened_by',
        'last_modified_by',
        'produits_json',
        'mode_paiement',
        'status', // Ajouté pour permettre la modification
    ];

    protected $casts = [
        'produits_json' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($panier) {
            if (($panier->status ?? 'en_cours') === 'en_cours' && empty($panier->mode_paiement)) {
                $panier->mode_paiement = 'compte_client';
            }
        });
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serveuse()
    {
        return $this->belongsTo(User::class, 'serveuse_id');
    }

    public function tableResto()
    {
        return $this->belongsTo(TableResto::class, 'table_id');
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function commande()
    {
        return $this->hasOne(Commande::class, 'panier_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'panier_produit')
            ->withPivot('quantite')
            ->withTimestamps();
    }

    public function getModePaiementEffectiveAttribute(): string
    {
        return $this->commande?->mode_paiement ?? $this->mode_paiement ?? 'compte_client';
    }

    public function getModePaiementLabelAttribute(): string
    {
        $mode = strtolower(str_replace(['_', '-', 'é', 'è', 'ê', 'à'], [' ', ' ', 'e', 'e', 'e', 'a'], $this->mode_paiement_effective));

        if (in_array($mode, ['compte client', 'credit'], true)) {
            return 'Crédit';
        }
        if ($mode === 'especes' || $mode === 'espèces' || $mode === 'espace') {
            return 'Espèces';
        }
        if ($mode === 'mobile money' || $mode === 'mobile_money') {
            return 'Mobile Money';
        }

        return ucfirst($mode);
    }
}
