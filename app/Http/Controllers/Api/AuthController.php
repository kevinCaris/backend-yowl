<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try{

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'birth_date' => $request->birth_date,
                'location' => $request->location,
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));

            return response()->json([
                'message' => 'Inscription réussie. Veuillez vérifier votre email pour confirmer votre compte.',
            ], 201);
        } catch (\Exception $e) {
            // Gérer les erreurs inattendues (BDD, etc.)
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'inscription',
                'errors' => [
                    'server' => ['Erreur serveur : ' . $e->getMessage()]
                ]
            ], 500);
        }
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try{
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Email ou mot de passe incorrect.',
                ], 401);
            }

            $user = Auth::user();

            // Vérifier si l'email est vérifié
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return response()->json([
                    'message' => 'Veuillez vérifier votre email avant de vous connecter.',
                ], 403);
            }

            // Créer un token d'authentification
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Connexion réussie.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => Auth::user()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion',
                'errors' => [
                    'server' => ['Erreur serveur']
                ]
            ], 500);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }

    /**
     * Obtenir l'utilisateur authentifié
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Vérification de l'email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'Lien de vérification invalide.',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email déjà vérifié.',
            ], 400);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email vérifié avec succès. Vous pouvez maintenant vous connecter.',
        ], 200);
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email déjà vérifié.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email de vérification renvoyé.',
        ], 200);
    }
}
