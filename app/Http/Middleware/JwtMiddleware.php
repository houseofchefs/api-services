<?php

namespace App\Http\Middleware;

use App\Traits\ResponseTraits;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    use ResponseTraits;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!$request->header('Authorization')) {
            return $this->errorResponse(false, "","Token not provided",401);
        }

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return $this->errorResponse(false, "","Invalid token",401);
        }

        return $next($request);
    }
}
