<?php

use App\Modules\Audit\Presentation\Http\Controllers\AuditLogController;
use App\Modules\Reports\Presentation\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/reports/executive-summary', [ReportsController::class, 'executive'])->middleware('permission:reports.view');
    Route::get('/reports/commerce/orders/export', [ReportsController::class, 'exportOrders'])->middleware('permission:reports.view');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view');
});
