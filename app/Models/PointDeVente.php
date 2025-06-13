<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointDeVente extends Model
{
    use HasFactory;
    protected $table = 'points_de_vente';

    protected $fillable = ['nom', 'module_id', 'entreprise_id', 'etat'];

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
}
?>