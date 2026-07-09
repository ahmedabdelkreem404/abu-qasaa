<?php

namespace App\Modules\Identity\Presentation\Http\Resources;

use App\Modules\Identity\Infrastructure\Models\UserBusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'roles' => $this->roles->pluck('key')->values(),
            'permissions' => $this->permissionKeys(),
            'business_units' => $this->businessUnitAssignments
                ->filter(fn (UserBusinessUnit $assignment) => $assignment->is_active)
                ->map(fn (UserBusinessUnit $assignment) => [
                    'id' => $assignment->businessUnit?->id,
                    'name_ar' => $assignment->businessUnit?->name_ar,
                    'name_en' => $assignment->businessUnit?->name_en,
                    'slug' => $assignment->businessUnit?->slug,
                    'role' => $assignment->role?->key ?? $assignment->role_key,
                ])
                ->values(),
        ];
    }
}
