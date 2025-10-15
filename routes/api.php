<?php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UploadImageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\LikeDislikeController;
use App\Http\Controllers\Api\AdminKpiController;
use App\Http\Controllers\Api\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
        Route::get('reviews/', [ReviewController::class, 'index']);

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/email/verify', [AuthController::class, 'verifyEmail'])
        ->name('verification.verify');
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);

    Route::post('/forgot/password', [ResetPasswordController::class, 'sendResetLink']);
    Route::post('/reset/password', [ResetPasswordController::class, 'resetPassword']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // user profile
    Route::prefix('profile')->middleware(['verified', 'reliable'])->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::delete('/', [ProfileController::class, 'destroy']);
        Route::put('/password', [ProfileController::class, 'updatePassword'])->middleware(['verified', 'reliable']);

    });


    // Routes Admin uniquement
    Route::prefix('admin')->middleware('admin')->group(function () {
        // Gestion des catégories
        Route::get('/categories', [ReviewController::class, 'index']);
        Route::get('/categories/{id}', [ReviewController::class, 'show']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        // Dashboard KPIs
        Route::get('/kpis/summary', [AdminKpiController::class, 'getSummary']);
    Route::get('/kpis/users-stats', [AdminKpiController::class, 'getUsersStats']);
    Route::get('/kpis/reviews-stats', [AdminKpiController::class, 'getReviewsStats']);
    Route::get('/kpis/growth', [AdminKpiController::class, 'getGrowthStats']);
    Route::get('/kpis/activity', [AdminKpiController::class, 'getActivityStats']);
        // Gestion des utilisateurs
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'delete']);
        // Modération des avis
    });

    // Reviews (CRUD)
        Route::prefix('reviews')->middleware(['verified', 'reliable'])->group(function () {
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
    });


     // Commentaires sur les avis
    Route::prefix('reviews/{reviewId}/comments')->middleware(['verified', 'reliable'])->group(function () {
        Route::get('/', [CommentController::class, 'index']); // Voir les commentaires
        Route::post('/', [CommentController::class, 'store']); // Ajouter un commentaire
    });

    Route::prefix('comments')->middleware(['verified', 'reliable'])->group(function () {
        Route::put('/{id}', [CommentController::class, 'update']); // Modifier son commentaire
        Route::delete('/{id}', [CommentController::class, 'destroy']); // Supprimer un commentaire
    });


    // Routes pour les Likes/Dislikes
    Route::middleware(['verified', 'reliable'])->group(function () {

        // Liker un avis
        Route::post('/reviews/{reviewId}/like', [LikeDislikeController::class, 'like']);

        // Disliker un avis
        Route::post('/reviews/{reviewId}/dislike', [LikeDislikeController::class, 'dislike']);

        // Voir le statut de like/dislike de l'utilisateur pour un avis
        Route::get('/reviews/{reviewId}/like-status', [LikeDislikeController::class, 'status']);

    });
});
