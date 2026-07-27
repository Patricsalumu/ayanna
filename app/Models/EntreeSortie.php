<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Casts\Attribute;
class EntreeSortie extends Model
{
    protected $table = 'entrees_sorties';
    protected $fillable = [
        'compte_id', 
        'montant', 
        'libele',
        'type',
        'annule',
        'user_id', 
        'point_de_vente_id',
        'journal_id',
        'comptabilise'
    ];

    protected $casts = [
        'comptabilise' => 'boolean',
        'annule' => 'boolean',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class);
    }

    public function journal()
    {
        return $this->belongsTo(JournalComptable::class);
    }
}
