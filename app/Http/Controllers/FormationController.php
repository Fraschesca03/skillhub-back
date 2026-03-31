<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Contrôleur de gestion des formations.
 */
class FormationController extends Controller
{
    /**
     * Liste toutes les formations.
     */
    public function index(): JsonResponse
    {
        $formations = Formation::with('formateur:id,nom,email')
            ->latest()
            ->get();

        return response()->json($formations);
    }

    /**
     * Affiche le détail d'une formation.
     * En même temps, on augmente le nombre de vues.
     */
    public function show($id): JsonResponse
    {
        $formation = Formation::with('formateur:id,nom,email')->find($id);

        if (! $formation) {
            return response()->json([
                'message' => 'Formation introuvable'
            ], 404);
        }

        // Incrément automatique des vues
        $formation->increment('nombre_de_vues');
        $formation->refresh();

        return response()->json($formation);
    }

    /**
     * Crée une formation.
     * Seul un formateur connecté peut créer.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut créer une formation'
                ], 403);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'categorie' => 'required|string|max:255',
                'niveau' => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation = Formation::create([
                'titre' => $request->input('titre'),
                'description' => $request->input('description'),
                'categorie' => $request->input('categorie'),
                'niveau' => $request->input('niveau'),
                'nombre_de_vues' => 0,
                'formateur_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Formation créée avec succès',
                'formation' => $formation
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Modifie une formation.
     * Le formateur ne peut modifier que sa propre formation.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $formation = Formation::find($id);

            if (! $formation) {
                return response()->json([
                    'message' => 'Formation introuvable'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut modifier une formation'
                ], 403);
            }

            if ($formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous ne pouvez modifier que vos propres formations'
                ], 403);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'categorie' => 'required|string|max:255',
                'niveau' => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation->update([
                'titre' => $request->input('titre'),
                'description' => $request->input('description'),
                'categorie' => $request->input('categorie'),
                'niveau' => $request->input('niveau'),
            ]);

            return response()->json([
                'message' => 'Formation modifiée avec succès',
                'formation' => $formation
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Supprime une formation.
     * Le formateur ne peut supprimer que sa propre formation.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $formation = Formation::find($id);

            if (! $formation) {
                return response()->json([
                    'message' => 'Formation introuvable'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut supprimer une formation'
                ], 403);
            }

            if ($formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous ne pouvez supprimer que vos propres formations'
                ], 403);
            }

            $formation->delete();

            return response()->json([
                'message' => 'Formation supprimée avec succès'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }
}