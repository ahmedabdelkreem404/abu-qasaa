<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            foreach ([
                'action' => fn () => $table->string('action')->nullable()->after('user_id'),
                'auditable_type' => fn () => $table->string('auditable_type')->nullable(),
                'auditable_id' => fn () => $table->unsignedBigInteger('auditable_id')->nullable(),
                'old_values_json' => fn () => $table->json('old_values_json')->nullable(),
                'new_values_json' => fn () => $table->json('new_values_json')->nullable(),
                'route' => fn () => $table->string('route')->nullable(),
                'method' => fn () => $table->string('method')->nullable(),
                'ip_address' => fn () => $table->string('ip_address')->nullable(),
                'user_agent' => fn () => $table->text('user_agent')->nullable(),
                'metadata_json' => fn () => $table->json('metadata_json')->nullable(),
            ] as $column => $add) {
                if (! Schema::hasColumn('audit_logs', $column)) {
                    $add();
                }
            }
        });
    }
};
