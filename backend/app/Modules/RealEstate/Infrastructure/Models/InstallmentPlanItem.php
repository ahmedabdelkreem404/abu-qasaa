<?php

namespace App\Modules\RealEstate\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPlanItem extends Model
{
    protected $fillable = ['installment_plan_id', 'sequence', 'label', 'amount', 'due_offset_days', 'due_date'];

    protected function casts(): array
    {
        return ['sequence' => 'integer', 'amount' => 'decimal:2', 'due_offset_days' => 'integer', 'due_date' => 'date'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }
}
