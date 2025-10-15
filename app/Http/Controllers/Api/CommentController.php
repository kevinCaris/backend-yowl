<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    /**
     * Lister tous les commentaires d'un avis (avec leurs réponses)
     */
    public function index($reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);

        // Récupérer uniquement les commentaires parents (pas les réponses)
        $comments = $review->comments()
            //->whereNull('parent_id')
            ->with([
                'user:id,name', // level 1
                'replies.user:id,name', // level 2
                'replies.replies.user:id,name', // level 3
                'replies.replies.replies.user:id,name', // level 4
                'replies.replies.replies.replies.user:id,name', // level 5
                'replies' => function ($query) {
                    $query->with('user:id,name')
                          ->orderBy('created_at', 'asc');
                }
            ])
            ->with([
             'user:id,name',
             'replies' => function ($query) {
                 // Nous chargeons les replies du 1er niveau
                 $query->with([
                     'user:id,name',
                     // Nous chargeons les replies du 2ème niveau (replies.replies)
                     'replies' => function ($queryReplies) {
                         $queryReplies->with('user:id,name')->orderBy('created_at', 'asc');
                     }
                 ])
                 ->orderBy('created_at', 'asc');
             }
         ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'comments' => $comments,
            'total' => $comments->count(),
        ], 200);
    }

    /**
     * Ajouter un commentaire sur un avis OU répondre à un commentaire
     */
    public function store(Request $request, $reviewId): JsonResponse
    {
        $review = Review::findOrFail($reviewId);

        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|integer|exists:comments,id',
        ]);

        //check depth
         if ($request->parent_id) {
            $parentComment = Comment::findOrFail($request->parent_id);

            // new level of reply will be parent level +1
            $newCommentDepth = $parentComment->depth + 1;
            $MAX_DEPTH = 5;

            if ($newCommentDepth > $MAX_DEPTH) {
                return response()->json([
                    'message' => "La limite de réponses (Niveau {$MAX_DEPTH}) a été atteinte.",
                ], 403);
            }
        }

        // Si parent_id est fourni, vérifier qu'il appartient bien au même review
        if ($request->parent_id) {
            $parentComment = Comment::find($request->parent_id);
            if ($parentComment->review_id !== $review->id) {
                return response()->json([
                    'message' => 'Le commentaire parent n\'appartient pas à cet avis.',
                ], 400);
            }
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'review_id' => $review->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        $comment->load('user:id,name');

        $message = $request->parent_id
            ? 'Réponse ajoutée avec succès.'
            : 'Commentaire ajouté avec succès.';

        return response()->json([
            'message' => $message,
            'comment' => $comment,
        ], 201);
    }

    /**
     * Modifier un commentaire (uniquement le propriétaire)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire du commentaire
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Vous ne pouvez modifier que vos propres commentaires.',
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|min:3|max:1000',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Commentaire modifié avec succès.',
            'comment' => $comment,
        ], 200);
    }

    /**
     * Supprimer un commentaire (propriétaire ou admin)
    */


    public function destroy(Request $request, $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        // Vérifier que l'utilisateur est le propriétaire ou un admin
        if ($comment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Vous ne pouvez supprimer que vos propres commentaires.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Commentaire supprimé avec succès.',
        ], 200);
    }
}
