<?php

namespace App\Http\Middleware;

use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use App\Modules\Identity\Application\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function __construct(private readonly AccessControlService $accessControl) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $businessUnit = $request->route('businessUnit');

        if ($businessUnit && ! $this->accessControl->canAccessBusinessUnit($user, $businessUnit)) {
            return ApiResponse::error('Forbidden.', 403);
        }

        return $next($request);
    }
}
