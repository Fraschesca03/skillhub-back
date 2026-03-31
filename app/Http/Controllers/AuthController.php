<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur simple pour l'authentification.
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un utilisateur.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:apprenant,formateur',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        $token = Auth::guard('api')->login($user);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Connexion d'un utilisateur.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => Auth::guard('api')->user(),
        ]);
    }

    /**
     * Retourne le profil connecté.
     */
    public function profile(): JsonResponse
    {
        return response()->json(Auth::guard('api')->user());
    }

    /**
     * Déconnexion.
     */
    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }
}