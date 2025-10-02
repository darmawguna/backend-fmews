<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Pastikan pengguna sudah terautentikasi
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Periksa apakah pengguna memiliki role yang diperlukan
        if (!$request->user()->hasRole($role)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. You do not have the required role.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}