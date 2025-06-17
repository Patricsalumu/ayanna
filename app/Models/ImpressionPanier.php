<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImpressionPanier extends Model
{
    protected $table = 'impressions_paniers';
    protected $fillable = [
        'panier_id',
        'user_id',
        'printed_at',
        'total',
        'produits',
    ];
    protected $casts = [
        'produits' => 'array',
        'printed_at' => 'datetime',
    ];

    public function panier() {
        return $this->belongsTo(Panier::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
