<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Modèle User.
 * Représente un utilisateur SkillHub.
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * Champs autorisés en insertion.
     */
    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
    ];

    /**
     * Champs cachés dans les réponses JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast automatique du mot de passe.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * ID utilisé dans le JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Données supplémentaires du JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}