<?php

use App\Modules\BusinessUnits\Presentation\Http\Controllers\BusinessUnitController;
use Illuminate\Support\Facades\Route;

Route::get('/business-units', [BusinessUnitController::class, 'index']);
Route::get('/activity-templates', fn () => response()->json(['data' => [], 'meta' => ['todo' => 'List activity templates']]));
