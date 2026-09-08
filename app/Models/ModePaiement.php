<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';
    protected $fillable = [
        'nom', 'code', 'actif', 'est_systeme', 'ordre', 'entreprise_id'
    ];
    protected $casts = [
        'actif' => 'boolean',
        'est_systeme' => 'boolean',
        'ordre' => 'integer',
    ];
    public function entreprise() {
        return $this->belongsTo(Entreprise::class);
    }
}
