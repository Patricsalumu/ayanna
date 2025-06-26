<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDeVente extends Model
{
    use HasFactory;
    protected $table = 'points_de_vente';

    protected $fillable = [
        'nom', 
        'module_id', 
        'entreprise_id', 
        'etat',
        'compte_caisse_id',
        'compte_vente_id', 
        'compte_client_id',
        'comptabilite_active'
    ];

    protected $casts = [
        'comptabilite_active' => 'boolean'
    ];

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Categorie::class, 'categorie_point_de_vente');
    }

    public function salles()
    {
        return $this->belongsToMany(\App\Models\Salle::class, 'salle_point_de_vente');
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
        public function historiques()
    {
        return $this->hasMany(\App\Models\Historiquepdv::class, 'point_de_vente_id');
    }

    public function commandes()
    {
        return $this->hasMany(\App\Models\Commande::class, 'point_de_vente_id');
    }
    /**
     * Retourne tous les produits liés à ce point de vente via les catégories associées.
     */
    public function produits()
    {
        return \App\Models\Produit::whereIn('categorie_id', function($query) {
            $query->select('categorie_id')
                ->from('categorie_point_de_vente')
                ->where('point_de_vente_id', $this->id);
        });
    }
    // Retourne true si un panier en cours existe pour ce point de vente
    public function hasPanierEnCours()
    {
        return \App\Models\Panier::where('point_de_vente_id', $this->id)
            ->where('status', 'en_cours')
            ->exists();
    }
    /**
     * Retourne le solde en cours pour ce point de vente (ou 0 si aucun historique ouvert).
     */
    public function getSoldeEnCours()
    {
        $ouverture = $this->historiques()->where('etat', 'ouvert')->latest('opened_at')->first();
        return $ouverture && isset($ouverture->solde) ? $ouverture->solde : 0;
    }

    // Relations comptables
    public function compteCaisse()
    {
        return $this->belongsTo(Compte::class, 'compte_caisse_id');
    }

    public function compteVente()
    {
        return $this->belongsTo(Compte::class, 'compte_vente_id');
    }

    public function compteClient()
    {
        return $this->belongsTo(Compte::class, 'compte_client_id');
    }

    public function journaux()
    {
        return $this->hasMany(JournalComptable::class);
    }
}
?>