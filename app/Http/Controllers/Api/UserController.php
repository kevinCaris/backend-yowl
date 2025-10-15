<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index():JsonResponse
    {

       $users = User::withCount('reviews')->get();
        // $users = User::paginate(10);
        // $users= $allUsers->paginate(10);
        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $user= User::findOrFail($id);
        return response()->json($user, 200);
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $request ->validate([
            'name' => 'string|min:3|max:50',
            'email' => 'string',

        ]);
        $user->update([
             'name' => $request->name,
             'email' => $request->email,
        ]);
        return response()->json([
            'message' => 'User update avec succes',
            'user' => $user,
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        $user= User::findOrFail($id);
        $user->delete();
        return response()->json([
            'message' => 'User supprimé avec succès.',
        ], 200);
    }
}
