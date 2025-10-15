<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Review;
use App\Models\Comment;
use App\Models\LikeDislike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminKpiController extends Controller
{
    /**
     * KPI 1: Résumé général (tous les KPIs principaux)
     * GET /api/admin/kpis/summary
     */
    public function getSummary()
    {
        $totalUsers = User::count();
        $totalReviews = Review::count();
        $totalComments = Comment::count();
        $totalLikes = LikeDislike::where('type', 'like')->count();
        $totalDislikes = LikeDislike::where('type', 'dislike')->count();

        // Nouveaux utilisateurs ce mois
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
                                  ->whereYear('created_at', Carbon::now()->year)
                                  ->count();

        // Nouveaux avis ce mois
        $newReviewsThisMonth = Review::whereMonth('created_at', Carbon::now()->month)
                                      ->whereYear('created_at', Carbon::now()->year)
                                      ->count();

        // Utilisateurs actifs (qui ont posté un avis ou commentaire dans les 30 derniers jours)
        $activeUsers = User::where(function($query) {
            $query->whereHas('reviews', function($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(30));
            })->orWhereHas('comments', function($q) {
                $q->where('created_at', '>=', Carbon::now()->subDays(30));
            });
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_reviews' => $totalReviews,
                'total_comments' => $totalComments,
                'total_likes' => $totalLikes,
                'total_dislikes' => $totalDislikes,
                'new_users_this_month' => $newUsersThisMonth,
                'new_reviews_this_month' => $newReviewsThisMonth,
                'active_users_last_30_days' => $activeUsers,
            ]
        ]);
    }

    /**
     * KPI 2: Statistiques utilisateurs
     * GET /api/admin/kpis/users-stats
     */
    public function getUsersStats()
    {
        // Nombre total d'utilisateurs
        $totalUsers = User::count();

        // Nouveaux utilisateurs par mois (12 derniers mois)
        $newUsersByMonth = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Moyenne d'utilisateurs actifs par an
        $activeUsersByYear = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        $avgActiveUsersPerYear = $activeUsersByYear->avg('count');

        // Répartition par tranche d'âge
        $ageDistribution = User::select(
                DB::raw('CASE
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 13 AND 18 THEN "13-18"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 19 AND 25 THEN "19-25"
                    WHEN TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 26 AND 35 THEN "26-35"
                    ELSE "Autre"
                END as age_group'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('age_group')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'new_users_by_month' => $newUsersByMonth,
                'avg_active_users_per_year' => round($avgActiveUsersPerYear, 2),
                'age_distribution' => $ageDistribution,
            ]
        ]);
    }

    /**
     * KPI 3: Statistiques des avis
     * GET /api/admin/kpis/reviews-stats
     */
    public function getReviewsStats()
    {
        // Nombre total d'avis
        $totalReviews = Review::count();

        // Nouveaux avis par mois (12 derniers mois)
        $reviewsByMonth = Review::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Moyenne d'avis publiés par an
        $reviewsByYear = Review::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        $avgReviewsPerYear = $reviewsByYear->avg('count');



        // Top 10 avis les plus likés
        $topReviews = Review::withCount(['likeDislikes as likes_count' => function($query) {
                $query->where('type', 'like');
            }])
            ->orderBy('likes_count', 'desc')
            ->limit(10)
            ->get(['id', 'title', 'likes_count']);

        return response()->json([
            'success' => true,
            'data' => [
                'total_reviews' => $totalReviews,
                'reviews_by_month' => $reviewsByMonth,
                'avg_reviews_per_year' => round($avgReviewsPerYear, 2),
                'top_reviews' => $topReviews,
            ]
        ]);
    }

    /**
     * KPI 4: Croissance de la plateforme
     * GET /api/admin/kpis/growth
     */
    public function getGrowthStats()
    {
        // Croissance des utilisateurs (mois par mois)
        $userGrowth = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as new_users'),
                DB::raw('(SELECT COUNT(*) FROM users WHERE created_at <= LAST_DAY(CONCAT(DATE_FORMAT(users.created_at, "%Y-%m"), "-01"))) as cumulative_users')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Croissance des avis (mois par mois)
        $reviewGrowth = Review::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as new_reviews'),
                DB::raw('(SELECT COUNT(*) FROM reviews WHERE created_at <= LAST_DAY(CONCAT(DATE_FORMAT(reviews.created_at, "%Y-%m"), "-01"))) as cumulative_reviews')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Taux de rétention (utilisateurs qui reviennent après 30 jours)
        $totalUsers = User::where('created_at', '<=', Carbon::now()->subDays(30))->count();
        $returningUsers = User::where('created_at', '<=', Carbon::now()->subDays(30))
            ->where(function($query) {
                $query->whereHas('reviews', function($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                })->orWhereHas('comments', function($q) {
                    $q->where('created_at', '>=', Carbon::now()->subDays(30));
                });
            })->count();

        $retentionRate = $totalUsers > 0 ? round(($returningUsers / $totalUsers) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'user_growth' => $userGrowth,
                'review_growth' => $reviewGrowth,
                'retention_rate' => $retentionRate,
            ]
        ]);
    }

    /**
     * KPI 5: Activité de la plateforme
     * GET /api/admin/kpis/activity
     */
    public function getActivityStats()
    {
        // Activité des 7 derniers jours
        $dailyActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            $dailyActivity[] = [
                'date' => $date,
                'new_users' => User::whereDate('created_at', $date)->count(),
                'new_reviews' => Review::whereDate('created_at', $date)->count(),
                'new_comments' => Comment::whereDate('created_at', $date)->count(),
            ];
        }

        // Utilisateurs les plus actifs (top 10)
        $topActiveUsers = User::withCount(['reviews', 'comments'])
            ->orderByDesc('reviews_count')
            ->orderByDesc('comments_count')
            ->limit(10)
            ->get(['id', 'pseudo', 'reviews_count', 'comments_count']);

        // Heures d'activité (par heure de la journée)
        $activityByHour = Review::select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'daily_activity' => $dailyActivity,
                'top_active_users' => $topActiveUsers,
                'activity_by_hour' => $activityByHour,
            ]
        ]);
    }
}
