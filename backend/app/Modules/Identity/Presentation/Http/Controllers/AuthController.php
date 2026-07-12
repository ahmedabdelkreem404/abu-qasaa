<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Presentation\Http\Requests\LoginRequest;
use App\Modules\Identity\Presentation\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $rateLimitKey = $this->loginRateLimitKey($request);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return ApiResponse::error('Too many login attempts. Please try again later.', 429);
        }

        $user = User::query()
            ->where('email', $request->validated('email'))
            ->with(['roles.permissions', 'businessUnitAssignments.role.permissions', 'businessUnitAssignments.businessUnit'])
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password) || ! $user->isActive()) {
            RateLimiter::hit($rateLimitKey, 60);

            return ApiResponse::error('Invalid credentials.', 422, [
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        RateLimiter::clear($rateLimitKey);

        $token = $user->createToken('dashboard')->plainTextToken;

        return ApiResponse::success([
            'user' => AuthUserResource::make($user),
            'token' => $token,
        ], 'Logged in successfully');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles.permissions', 'businessUnitAssignments.role.permissions', 'businessUnitAssignments.businessUnit']);

        return ApiResponse::success(AuthUserResource::make($user), 'Authenticated user retrieved successfully');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->tokens()->delete();

        return ApiResponse::success(null, 'Logged out successfully');
    }

    private function loginRateLimitKey(Request $request): string
    {
        return 'login:'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
