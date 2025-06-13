<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivite extends Model
{
    protected $table = 'logs_activite';

    protected $fillable = [
        'user_id', 'action', 'details'
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
