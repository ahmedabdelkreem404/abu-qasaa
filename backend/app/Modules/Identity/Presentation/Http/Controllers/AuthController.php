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

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->with(['roles.permissions', 'businessUnitAssignments.role.permissions', 'businessUnitAssignments.businessUnit'])
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password) || ! $user->isActive()) {
            return ApiResponse::error('Invalid credentials.', 422, [
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

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
}
