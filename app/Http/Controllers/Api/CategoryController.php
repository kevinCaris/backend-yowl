<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Lister toutes les catégories
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('reviews')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'categories' => $categories,
            'total' => $categories->count(),
        ], 200);
    }

    /**
     * Afficher une catégorie
     */
    public function show($slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->withCount('reviews')
            ->firstOrFail();

        return response()->json([
            'category' => $category,
        ], 200);
    }

    /**
     * Créer une catégorie (Admin uniquement)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Catégorie créée avec succès.',
            'category' => $category,
        ], 201);
    }

    /**
     * Modifier une catégorie (Admin uniquement)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Catégorie modifiée avec succès.',
            'category' => $category,
        ], 200);
    }

    /**
     * Supprimer une catégorie (Admin uniquement)
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        // Vérifier s'il y a des reviews dans cette catégorie
        if ($category->reviews()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette catégorie car elle contient des avis.',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'message' => 'Catégorie supprimée avec succès.',
        ], 200);
    }

    /**
     * Lister les reviews d'une catégorie
     */
    public function reviews($slug): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $reviews = $category->reviews()
            ->with(['user:id,name', 'category:id,name,slug'])
            ->withCount('comments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($reviews, 200);
    }
}
