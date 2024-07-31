<?php

namespace App\Http\Controllers;

use App\Constants\AuthConstants;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response as HTTPCode;

class AuthController extends Controller
{
    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return $this->failedResponse(AuthConstants::UNAUTHORIZED, HTTPCode::HTTP_UNAUTHORIZED);
        }

        $user = Auth::user();

        return $this->authorizationResponse(AuthConstants::LOGIN, $token, $user);
    }

    /**
     * Create account User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::login($user);
        return $this->authorizationResponse(AuthConstants::REGISTER, $token, $user);
    }

    /**
     * Log out account User with credential token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();

        return $this->successResponse(AuthConstants::LOGOUT, HTTPCode::HTTP_OK);
    }

    /**
     * Refresh token User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $user = Auth::user();
        $token = Auth::refresh();

        return $this->authorizationResponse(AuthConstants::REFRESH, $token, $user);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function me(): JsonResponse
    {
        $authUser = Auth::user();
        $user = $authUser->toArray();

        return $this->successResponse(AuthConstants::ME, HTTPCode::HTTP_OK, $user);
    }
}
