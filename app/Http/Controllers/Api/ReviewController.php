<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{

    /**
     * Lister tous les avis (avec filtres)
     */
    public function index(Request $request): JsonResponse
    {

        $query = Review::with(['user:id,name,location,avatar'])
            ->withCount('comments');

        // Recherche par titre de produit
        if ($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        // Trier
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reviews = $query->paginate(1000);

        return response()->json($reviews, 200);
    }

    /**
     * Afficher un avis avec ses commentaires
     */
    public function show($id): JsonResponse
    {
        $review = Review::with([
            'user:id,name',
        ])
            ->withCount('comments')
            ->findOrFail($id);

        return response()->json([
            'review' => $review,
        ], 200);
    }

    /**
     * Créer un avis
     */
    public function store(Request $request): JsonResponse
    {
        // $request->validate([
        //     'product_name' => 'required|string|max:255',
        //     'product_link' => 'nullable|url',
        //     'product_image' => 'nullable|url',
        //     'content' => 'required|string|min:10',
        // ]);

        // Log::info($request);

        // $imgPath = null;
        // if ($request->hasFile('image')) {
        //     $imgPath = $request->file('image')->store('', 'public');
        // }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_name' => $request->product_name,
            'product_link' => $request->product_link,
            'product_image' => $request->product_image,
            'content' => $request->content,
            'tag' => $request->tag,
        ]);

        $review->load(['user:id,name']);



        return response()->json([
            'message' => 'Avis créé avec succès.',
            'review' => $review,
        ], 201);
    }

    /**
     * Modifier un avis (uniquement le propriétaire)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez modifier que vos propres avis.',
            ], 403);
        }

        $request->validate([
            'product_name' => 'required|string|min:3|max:30',
            'product_link' => 'required|url',
            'product_image' => 'required|url',
            'content' => 'sometimes|string|min:10|max:255',
        ]);

        $review->update($request->only([
            'product_name',
            'product_link',
            'product_image',
            'content',
            'tag',

        ]));

        $review->load(['user:id,name']);

        return response()->json([
            'message' => 'Avis modifié avec succès.',
            'review' => $review,
        ], 200);
    }

    /**
     * Supprimer un avis (propriétaire ou admin)
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire ou un admin
        if ($review->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Vous ne pouvez supprimer que vos propres avis.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Avis supprimé avec succès.',
        ], 200);
    }
}
