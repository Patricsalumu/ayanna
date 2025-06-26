<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcritureComptable extends Model
{
    use HasFactory;

    protected $table = 'ecritures_comptables';

    protected $fillable = [
        'journal_id',
        'compte_id',
        'libelle',
        'debit',
        'credit',
        'client_id',
        'produit_id',
        'ordre',
        'notes'
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2'
    ];

    // Relations
    public function journal()
    {
        return $this->belongsTo(JournalComptable::class);
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    // Méthodes utilitaires
    public function getMontantAttribute()
    {
        return $this->debit > 0 ? $this->debit : $this->credit;
    }

    public function getSensAttribute()
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }

    // Scopes
    public function scopeDebit($query)
    {
        return $query->where('debit', '>', 0);
    }

    public function scopeCredit($query)
    {
        return $query->where('credit', '>', 0);
    }

    public function scopeParCompte($query, $compteId)
    {
        return $query->where('compte_id', $compteId);
    }
}
