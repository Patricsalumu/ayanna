<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleEntreprise extends Model
{
    protected $table = 'entreprise_module';
    protected $fillable = [
        'entreprise_id',
        'module_id',
    ];
    public $timestamps = false;
}
