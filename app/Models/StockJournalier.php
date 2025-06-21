<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockJournalier extends Model
{
    use HasFactory;

    protected $table = 'stock_journalier';

    protected $fillable = [
        'produit_id',
        'point_de_vente_id',
        'date',
        'quantite_initiale',
        'quantite_ajoutee',
        'quantite_vendue',
        'quantite_reste',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class);
    }
}
