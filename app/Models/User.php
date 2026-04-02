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

    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Relation : un formateur peut avoir plusieurs formations.
     */
    public function formations()
    {
        return $this->hasMany(Formation::class, 'formateur_id');
    }

    /**
     * Relation : un utilisateur peut avoir plusieurs inscriptions.
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'utilisateur_id');
    }
    /**
 * Relation : modules terminés par l'utilisateur.
 */
public function modulesTermines()
{
    return $this->belongsToMany(Module::class, 'module_user', 'utilisateur_id', 'module_id')
        ->withPivot('termine')
        ->withTimestamps();
}
}