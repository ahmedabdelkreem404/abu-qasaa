<?php

namespace App\Modules\ServicesRfq\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class RfqActivityLog extends Model
{
    protected $fillable = ['business_unit_id', 'rfq_request_id', 'user_id', 'event', 'from_status', 'to_status', 'metadata_json'];

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }
}
