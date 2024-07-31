<?php

namespace App\Http\Middleware;

use App\Constants\AuthConstants;
use App\Traits\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class JWTMiddleware
{
    use ResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): HTTPCode
    {
        try {
            // Check if the token is present and valid
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->failedResponse(AuthConstants::USER_NOT_FOUND, HTTPCode::HTTP_UNAUTHORIZED);
            }
        } catch (JWTException $e) {
            // Handle various token exceptions
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
                return $this->failedResponse(AuthConstants::TOKEN_EXPIRED, HTTPCode::HTTP_UNAUTHORIZED);
            } elseif ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
                return $this->failedResponse(AuthConstants::TOKEN_INVALID, HTTPCode::HTTP_UNAUTHORIZED);
            }

            return $this->failedResponse(AuthConstants::TOKEN_NOT_PROVIDED);
        }

        return $next($request);
    }
}
