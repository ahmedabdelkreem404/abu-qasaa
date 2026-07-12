<?php

namespace App\Modules\Audit\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Audit\Infrastructure\Models\AuditLog;
use App\Modules\Audit\Presentation\Http\Resources\AuditLogResource;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderByDesc('id');
        if (! $request->user()->isSuperAdmin()) {
            $query->whereIn('business_unit_id', $request->user()->businessUnitAssignments()->where('is_active', true)->pluck('business_unit_id'));
        }

        return ApiResponse::paginated($query->paginate((int) $request->query('per_page', 15))->through(fn (AuditLog $log) => AuditLogResource::make($log)->resolve()), 'Audit logs retrieved successfully');
    }
}
