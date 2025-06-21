<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historiquepdv extends Model
{
    protected $table = 'historiquepdv';
    public $timestamps = true;

    protected $fillable = [
        'point_de_vente_id',
        'user_id',
        'etat',
        'solde',
        'opened_at',
        'closed_at',
        'opened_by',
        'closed_by',
    ];

    public function pointDeVente()
    {
        return $this->belongsTo(\App\Models\PointDeVente::class, 'point_de_vente_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
