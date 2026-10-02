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
        'session',
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

    public function recalculerQuantiteReste(): int
    {
        $quantiteInitiale = (int) ($this->quantite_initiale ?? 0);
        $quantiteAjoutee = (int) ($this->quantite_ajoutee ?? 0);
        $quantiteVendue = (int) ($this->quantite_vendue ?? 0);

        $this->quantite_reste = $quantiteInitiale + $quantiteAjoutee - $quantiteVendue;
        $this->save();

        return (int) $this->quantite_reste;
    }

    public static function recalculerQuantiteRestePourSession(int $pointDeVenteId, string $date, string $session): int
    {
        $rows = static::where('point_de_vente_id', $pointDeVenteId)
            ->where('date', $date)
            ->where('session', $session)
            ->get();

        $updatedRows = 0;
        foreach ($rows as $row) {
            $row->recalculerQuantiteReste();
            $updatedRows++;
        }

        return $updatedRows;
    }
}
