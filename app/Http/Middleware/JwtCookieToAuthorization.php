<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtCookieToAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = config('app.jwt_cookie', 'access_token');

        if (!$request->bearerToken() && $request->cookies->has($cookieName)) {
            $token = $request->cookies->get($cookieName);
            if (!empty($token)) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        return $next($request);
    }
}
