<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';
    protected $fillable = [
        'nom', 'actif', 'entreprise_id'
    ];
    public function entreprise() {
        return $this->belongsTo(Entreprise::class);
    }
}
