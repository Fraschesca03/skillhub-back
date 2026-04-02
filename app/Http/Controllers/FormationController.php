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
     * Liste des formations.
     */
    public function index(): JsonResponse
    {
        $formations = Formation::with('formateur:id,nom,email')->get();

        return response()->json($formations);
    }

    /**
     * Créer une nouvelle formation.
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
                'niveau' => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation = Formation::create([
                'titre' => $request->titre,
                'description' => $request->description,
                'niveau' => $request->niveau,
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
     * Afficher une formation.
     */
    public function show($id): JsonResponse
    {
        $formation = Formation::with('formateur:id,nom,email')->find($id);

        if (! $formation) {
            return response()->json([
                'message' => 'Formation introuvable'
            ], 404);
        }

        $formation->increment('nombre_de_vues');

        return response()->json($formation->fresh('formateur:id,nom,email'));
    }

    /**
     * Mettre à jour une formation.
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

            if ($user->role !== 'formateur' || $formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Action non autorisée'
                ], 403);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'description' => 'required|string',
                'niveau' => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation->update([
                'titre' => $request->titre,
                'description' => $request->description,
                'niveau' => $request->niveau,
            ]);

            return response()->json([
                'message' => 'Formation mise à jour avec succès',
                'formation' => $formation
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Supprimer une formation.
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

            if ($user->role !== 'formateur' || $formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Action non autorisée'
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