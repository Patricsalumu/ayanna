<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * Notifications reçues par l'utilisateur
     */
    public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class);
    }

    /**
     * Logs d'activité de l'utilisateur
     */
    public function logsActivite()
    {
        return $this->hasMany(\App\Models\LogActivite::class);
    }

    /**
     * Historique des ouvertures/fermetures de PDV par l'utilisateur
     */
    public function historiquesOuvertures()
    {
        return $this->hasMany(\App\Models\Historiquepdv::class, 'user_id');
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'entreprise_id',
        'telephone', // harmonisé avec la migration, à adapter si besoin
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation avec l'entreprise (clé étrangère entreprise_id)
     */
    public function entreprise()
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }
}
