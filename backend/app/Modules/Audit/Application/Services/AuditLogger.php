<?php

namespace App\Modules\Audit\Application\Services;

use App\Modules\Audit\Infrastructure\Models\AuditLog;
use Throwable;

class AuditLogger
{
    private const SENSITIVE = ['password', 'token', 'api_key', 'secret', 'hmac', 'authorization', 'card'];

    public function log(?int $businessUnitId, ?int $userId, string $action, string $event, ?string $auditableType = null, int|string|null $auditableId = null, array $newValues = [], array $oldValues = [], array $metadata = []): void
    {
        try {
            AuditLog::query()->create([
                'business_unit_id' => $businessUnitId,
                'user_id' => $userId,
                'action' => $action,
                'event' => $event,
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'old_values_json' => $this->redact($oldValues),
                'new_values_json' => $this->redact($newValues),
                'metadata_json' => $this->redact($metadata),
            ]);
        } catch (Throwable) {
            report('Audit logging failed without interrupting the main transaction.');
        }
    }

    private function redact(array $values): array
    {
        foreach ($values as $key => $value) {
            $lower = strtolower((string) $key);
            if (str($lower)->contains(self::SENSITIVE)) {
                $values[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $values[$key] = $this->redact($value);
            }
        }

        return $values;
    }
}
