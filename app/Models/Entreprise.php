<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'telephone', 'email', 'logo'];

    /**
     * Relation : une entreprise possède plusieurs utilisateurs
     */
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
    public function pointsDeVente()
    {
        return $this->hasMany(PointDeVente::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'entreprise_module', 'entreprise_id', 'module_id');
    }
    
    public function categories()
    {
        return $this->hasMany(\App\Models\Categorie::class);
    }

    public function salles()
    {
        return $this->hasMany(\App\Models\Salle::class);
    }

    public function clients()
    {
        return $this->hasMany(\App\Models\Client::class);
    }
}
?>

