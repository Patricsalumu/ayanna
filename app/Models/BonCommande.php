<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BonCommande extends Model
{
    use HasFactory;

    protected $table = 'bon_commandes';

    protected $fillable = [
        'numero_bon',
        'panier_id',
        'serveuse_id',
        'client_id',
        'utilisateur_id',
        'produits_json',
    ];

    protected $casts = [
        'produits_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function panier()
    {
        return $this->belongsTo(Panier::class);
    }

    public function serveuse()
    {
        return $this->belongsTo(User::class, 'serveuse_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Récupère le prochain numéro de bon pour le jour donné
     */
    public static function getNextNumero($date = null)
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : Carbon::now()->startOfDay();

        $lastBon = static::whereBetween('created_at', [
            $date->copy(),
            $date->copy()->endOfDay()
        ])->orderByDesc('numero_bon')->first();

        return $lastBon ? $lastBon->numero_bon + 1 : 1;
    }

    /**
     * Récupère la quantité déjà envoyée en cuisine pour un panier et un produit
     */
    public static function getQuantiteSent($panier_id, $produit_id)
    {
        $total = 0;

        $bons = static::where('panier_id', $panier_id)
            ->get();

        foreach ($bons as $bon) {
            foreach ($bon->produits_json as $produit) {
                if ($produit['produit_id'] == $produit_id) {
                    $total += $produit['quantite'];
                }
            }
        }

        return $total;
    }

    /**
     * Récupère la quantité totale déjà envoyée pour un produit dans un panier
     */
    public static function getQuantitesSent($panier_id)
    {
        $quantities = [];

        $bons = static::where('panier_id', $panier_id)->get();

        foreach ($bons as $bon) {
            $produits = is_string($bon->produits_json) ? json_decode($bon->produits_json, true) : $bon->produits_json;
            if (!$produits) {
                continue;
            }
            foreach ($produits as $produit) {
                $produit_id = $produit['produit_id'];
                $quantities[$produit_id] = ($quantities[$produit_id] ?? 0) + $produit['quantite'];
            }
        }

        return $quantities;
    }
}
