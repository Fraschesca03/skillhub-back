<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Formation.
 * Représente une formation créée par un formateur.
 */
class Formation extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'categorie',
        'niveau',
        'nombre_de_vues',
        'formateur_id',
    ];

    /**
     * Relation : une formation appartient à un formateur.
     */
    public function formateur()
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /**
     * Relation : une formation a plusieurs modules.
     */
    public function modules()
    {
        return $this->hasMany(Module::class, 'formation_id')->orderBy('ordre');
    }

    /**
     * Relation : une formation a plusieurs inscriptions.
     */
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'formation_id');
    }

    /**
     * Relation : une formation a plusieurs vues uniques.
     */
    public function vues()
    {
        return $this->hasMany(FormationVue::class, 'formation_id');
    }
}