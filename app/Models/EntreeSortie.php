<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntreeSortie extends Model
{
    protected $table = 'entrees_sorties';
    protected $fillable = ['compte_id', 'montant', 'libele','type','user_id', 'point_de_vente_id'];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
