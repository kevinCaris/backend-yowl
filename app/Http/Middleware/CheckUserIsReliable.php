<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsReliable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isReliable()) {
            return response()->json([
                'message' => 'Votre compte a été marqué comme non fiable. Veuillez contacter l\'administrateur.',
            ], 403);
        }
        return $next($request);
    }
}
