<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'birth_date' => $user->birth_date,
                'location' => $user->location,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'is_reliable' => $user->is_reliable,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {

        \Illuminate\Support\Facades\Log::info($request);
        $user = $request->user();

        $dataToUpdate = $request->only(['name', 'email','location', 'birth_date','avatar']);

        // Si c'est un changement de mot de passe
        if ($request->has('old_password')) {
            $request->validate([
                'old_password' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);

            // Vérifier l'ancien mot de passe
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'message' => 'Old password is incorrect'
                ], 422);
            }
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'message' => 'Password updated successfully'
            ]);
        }

        // // Gérer l'upload de l'avatar
        // if ($request->hasFile('avatar')) {

        //     if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        //         Storage::disk('public')->delete($user->avatar);
        //     }

        //     $avatarPath = $request->file('avatar')->store('avatars', 'public');
        //     $dataToUpdate['avatar'] = $avatarPath;
        // }


        $user->update($dataToUpdate );

        // Ajouter l'URL complète de l'avatar
        // $user->avatar_url = $user->avatar ? Storage::url($user->avatar) : null;

        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar'=>$user->avatar ,
                'birth_date' => $user->birth_date,
                'location' => $user->location,
                'role' => $user->role,
            ],
        ], 200);
    }


    /**
     * Supprimer le compte de l'utilisateur
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        // Vérifier le mot de passe avant la suppression
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Mot de passe incorrect.',
            ], 401);
        }

        // Supprimer tous les tokens de l'utilisateur
        $user->tokens()->delete();

        // Supprimer le compte
        $user->delete();

        return response()->json([
            'message' => 'Compte supprimé avec succès.',
        ], 200);
    }

}
