<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Contrôleur de gestion des modules.
 */
class ModuleController extends Controller
{
    /**
     * Liste les modules d'une formation.
     */
    public function index($formationId): JsonResponse
    {
        $formation = Formation::with('modules')->find($formationId);

        if (! $formation) {
            return response()->json([
                'message' => 'Formation introuvable'
            ], 404);
        }

        return response()->json($formation->modules);
    }

    /**
     * Ajoute un module à une formation.
     * Seul le formateur propriétaire peut ajouter.
     */
    public function store(Request $request, $formationId): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $formation = Formation::find($formationId);

            if (! $formation) {
                return response()->json([
                    'message' => 'Formation introuvable'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut ajouter un module'
                ], 403);
            }

            if ($formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous ne pouvez modifier que vos propres formations'
                ], 403);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'contenu' => 'required|string',
                'ordre' => 'required|integer|min:1',
            ]);

            $module = Module::create([
                'titre' => $request->input('titre'),
                'contenu' => $request->input('contenu'),
                'ordre' => $request->input('ordre'),
                'formation_id' => $formation->id,
            ]);

            return response()->json([
                'message' => 'Module créé avec succès',
                'module' => $module
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Modifie un module.
     * Seul le formateur propriétaire de la formation peut modifier.
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

            $module = Module::find($id);

            if (! $module) {
                return response()->json([
                    'message' => 'Module introuvable'
                ], 404);
            }

            $formation = Formation::find($module->formation_id);

            if (! $formation) {
                return response()->json([
                    'message' => 'Formation introuvable'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut modifier un module'
                ], 403);
            }

            if ($formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous ne pouvez modifier que vos propres modules'
                ], 403);
            }

            $request->validate([
                'titre' => 'required|string|max:255',
                'contenu' => 'required|string',
                'ordre' => 'required|integer|min:1',
            ]);

            $module->update([
                'titre' => $request->input('titre'),
                'contenu' => $request->input('contenu'),
                'ordre' => $request->input('ordre'),
            ]);

            return response()->json([
                'message' => 'Module modifié avec succès',
                'module' => $module
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Supprime un module.
     * Seul le formateur propriétaire de la formation peut supprimer.
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

            $module = Module::find($id);

            if (! $module) {
                return response()->json([
                    'message' => 'Module introuvable'
                ], 404);
            }

            $formation = Formation::find($module->formation_id);

            if (! $formation) {
                return response()->json([
                    'message' => 'Formation introuvable'
                ], 404);
            }

            if ($user->role !== 'formateur') {
                return response()->json([
                    'message' => 'Seul un formateur peut supprimer un module'
                ], 403);
            }

            if ($formation->formateur_id !== $user->id) {
                return response()->json([
                    'message' => 'Vous ne pouvez supprimer que vos propres modules'
                ], 403);
            }

            $module->delete();

            return response()->json([
                'message' => 'Module supprimé avec succès'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }
}