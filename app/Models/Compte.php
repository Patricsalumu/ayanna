<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    protected $fillable = ['numero', 'nom', 'type', 'description', 'entreprise_id', 'user_id'];

    public function entreesSorties()
    {
        return $this->hasMany(EntreeSortie::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
