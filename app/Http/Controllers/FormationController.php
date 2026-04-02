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
     * Liste des formations avec filtres optionnels.
     * Filtres disponibles : recherche (mot-clé), categorie, niveau.
     * Route : GET /formations
     */
    public function index(Request $request): JsonResponse
    {
        $query = Formation::with('formateur:id,nom,email')
            ->withCount('inscriptions');

        // Filtre par mot-clé sur le titre ou la description
        if ($request->filled('recherche')) {
            $motCle = $request->input('recherche');
            $query->where(function ($q) use ($motCle) {
                $q->where('titre', 'like', '%' . $motCle . '%')
                  ->orWhere('description', 'like', '%' . $motCle . '%');
            });
        }

        // Filtre par catégorie
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->input('categorie'));
        }

        // Filtre par niveau
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->input('niveau'));
        }

        $formations = $query->get();

        return response()->json($formations);
    }

    /**
     * Créer une nouvelle formation.
     * Route : POST /formations
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
                'titre'       => 'required|string|max:255',
                'description' => 'required|string',
                'categorie'   => 'required|in:developpement_web,data,design,marketing,devops,autre',
                'niveau'      => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation = Formation::create([
                'titre'          => $request->titre,
                'description'    => $request->description,
                'categorie'      => $request->categorie,
                'niveau'         => $request->niveau,
                'nombre_de_vues' => 0,
                'formateur_id'   => $user->id,
            ]);

            return response()->json([
                'message'   => 'Formation créée avec succès',
                'formation' => $formation
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalide ou absent'
            ], 401);
        }
    }

    /**
     * Afficher une formation et incrémenter ses vues.
     * Route : GET /formations/{id}
     */
    public function show($id): JsonResponse
    {
        $formation = Formation::with('formateur:id,nom,email')
            ->withCount('inscriptions')
            ->find($id);

        if (! $formation) {
            return response()->json([
                'message' => 'Formation introuvable'
            ], 404);
        }

        // Incrémentation automatique du nombre de vues
        $formation->increment('nombre_de_vues');

        return response()->json($formation->fresh(['formateur:id,nom,email']));
    }

    /**
     * Mettre à jour une formation.
     * Route : PUT /formations/{id}
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
                'titre'       => 'required|string|max:255',
                'description' => 'required|string',
                'categorie'   => 'required|in:developpement_web,data,design,marketing,devops,autre',
                'niveau'      => 'required|in:debutant,intermediaire,avance',
            ]);

            $formation->update([
                'titre'       => $request->titre,
                'description' => $request->description,
                'categorie'   => $request->categorie,
                'niveau'      => $request->niveau,
            ]);

            return response()->json([
                'message'   => 'Formation mise à jour avec succès',
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
     * Route : DELETE /formations/{id}
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