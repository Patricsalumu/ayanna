<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panier extends Model
{
    use HasFactory;

    protected $table = 'paniers';

    protected $fillable = [
        'table_id',
        'point_de_vente_id',
        'client_id',
        'serveuse_id',
        'opened_by',
        'last_modified_by',
        'produits_json',
        'status', // Ajouté pour permettre la modification
    ];

    protected $casts = [
        'produits_json' => 'array',
    ];

    // Relations
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serveuse()
    {
        return $this->belongsTo(User::class, 'serveuse_id');
    }

    public function tableResto()
    {
        return $this->belongsTo(TableResto::class, 'table_id');
    }

    public function pointDeVente()
    {
        return $this->belongsTo(PointDeVente::class, 'point_de_vente_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'panier_produit')
            ->withPivot('quantite')
            ->withTimestamps();
    }
}
