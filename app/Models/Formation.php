<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle Formation.
 * Représente une formation créée par un formateur.
 */
class Formation extends Model
{
    /**
     * Champs autorisés à l'insertion.
     */
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
}