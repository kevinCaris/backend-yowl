<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\LikeDislike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeDislikeController extends Controller
{
    /**
     * Liker un avis
     */
    public function like(Request $request, $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $userId = $request->user()->id;

        // Vérifier si l'utilisateur a déjà une réaction sur cet avis
        $existingLike = LikeDislike::where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->first();

        if ($existingLike) {
            if ($existingLike->type === 'like') {
                // Si déjà liké, on retire le like
                $existingLike->delete();
                $review->decrement('likes_count');

                return response()->json([
                    'message' => 'Like retiré.',
                    'action' => 'removed',
                    'likes_count' => $review->likes_count,
                    'dislikes_count' => $review->dislikes_count,
                ], 200);
            } else {
                // Si disliké, on change en like
                $existingLike->update(['type' => 'like']);
                $review->decrement('dislikes_count');
                $review->increment('likes_count');

                return response()->json([
                    'message' => 'Avis liké avec succès.',
                    'action' => 'changed',
                    'likes_count' => $review->likes_count,
                    'dislikes_count' => $review->dislikes_count,
                ], 200);
            }
        }

        // Créer un nouveau like
        LikeDislike::create([
            'user_id' => $userId,
            'review_id' => $reviewId,
            'type' => 'like',
        ]);

        $review->increment('likes_count');

        return response()->json([
            'message' => 'Avis liké avec succès.',
            'action' => 'added',
            'likes_count' => $review->likes_count,
            'dislikes_count' => $review->dislikes_count,
        ], 200);
    }

    /**
     * Disliker un avis
    */

    public function dislike(Request $request, $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $userId = $request->user()->id;

        // Vérifier si l'utilisateur a déjà une réaction sur cet avis
        $existingLike = LikeDislike::where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->first();

        if ($existingLike) {
            if ($existingLike->type === 'dislike') {
                $existingLike->delete();
                $review->decrement('dislikes_count');

                return response()->json([
                    'message' => 'Dislike retiré.',
                    'action' => 'removed',
                    'likes_count' => $review->likes_count,
                    'dislikes_count' => $review->dislikes_count,
                ], 200);
            } else {
                // Si c' est liké, on change en dislike
                $existingLike->update(['type' => 'dislike']);
                $review->decrement('likes_count');
                $review->increment('dislikes_count');

                return response()->json([
                    'message' => 'Avis disliké avec succès.',
                    'action' => 'changed',
                    'likes_count' => $review->likes_count,
                    'dislikes_count' => $review->dislikes_count,
                ], 200);
            }
        }

        // Créer un nouveau dislike
        LikeDislike::create([
            'user_id' => $userId,
            'review_id' => $reviewId,
            'type' => 'dislike',
        ]);

        $review->increment('dislikes_count');

        return response()->json([
            'message' => 'Avis disliké avec succès.',
            'action' => 'added',
            'likes_count' => $review->likes_count,
            'dislikes_count' => $review->dislikes_count,
        ], 200);
    }

    /**
     * Obtenir le statut de like/dislike de l'utilisateur pour un avis
     */
    public function status(Request $request, $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);
        $userId = $request->user()->id;

        $userLike = LikeDislike::where('user_id', $userId)
            ->where('review_id', $reviewId)
            ->first();
        return response()->json([
            'user_reaction' => $userLike ? $userLike->type : null,
            'likes_count' => $review->likes_count,
            'dislikes_count' => $review->dislikes_count,
        ], 200);
    }
}


