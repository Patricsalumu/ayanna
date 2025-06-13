<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableResto extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero', 'forme',
        'width', 'height',
        'position_x', 'position_y',
        'salle_id'
    ];

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}
