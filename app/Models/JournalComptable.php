<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalComptable extends Model
{
    use HasFactory;

    protected $table = 'journal_comptable';

    protected $fillable = [
        'date_ecriture',
        'numero_piece',
        'libelle',
        'montant_total',
        'entreprise_id',
        'point_de_vente_id',
        'commande_id',
        'panier_id',
        'user_id',
        'type_operation',
        'statut',
        'notes'
    ];

    protected $casts = [
        'date_ecriture' => 'date',
        'montant_total' => 'decimal:2'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'journal_id')->orderBy('ordre');
    }

    // Méthodes utilitaires
    public function estEquilibre()
    {
        $totalDebit = $this->ecritures->sum('debit');
        $totalCredit = $this->ecritures->sum('credit');
        return abs($totalDebit - $totalCredit) < 0.01; // Tolérance de 1 centime
    }

    public static function genererNumeroPiece($typeOperation, $entrepriseId, $date = null)
    {
        $date = $date ?? now();
        $prefix = strtoupper(substr($typeOperation, 0, 3)); // VTE, PAI, DEP, etc.
        $dateStr = $date->format('Ymd');
        
        $dernier = self::where('entreprise_id', $entrepriseId)
            ->where('numero_piece', 'LIKE', "{$prefix}-{$dateStr}-%")
            ->orderByDesc('numero_piece')
            ->first();
            
        $numero = 1;
        if ($dernier) {
            $parts = explode('-', $dernier->numero_piece);
            $numero = intval(end($parts)) + 1;
        }
        
        return sprintf('%s-%s-%03d', $prefix, $dateStr, $numero);
    }

    // Scopes
    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_ecriture', [$dateDebut, $dateFin]);
    }

    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeParPointDeVente($query, $pointDeVenteId)
    {
        return $query->where('point_de_vente_id', $pointDeVenteId);
    }

    public function scopeParType($query, $typeOperation)
    {
        return $query->where('type_operation', $typeOperation);
    }
}
