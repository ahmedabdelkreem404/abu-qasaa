<?php

use App\Modules\RealEstate\Presentation\Http\Controllers\RealEstateController;
use Illuminate\Support\Facades\Route;

Route::get('/public/{businessSlug}/real-estate/projects', [RealEstateController::class, 'publicProjects']);
Route::get('/public/{businessSlug}/real-estate/projects/{projectSlug}', [RealEstateController::class, 'publicProject']);
Route::get('/public/{businessSlug}/real-estate/units', [RealEstateController::class, 'publicUnits']);
Route::post('/public/{businessSlug}/real-estate/leads', [RealEstateController::class, 'submitLead']);
Route::post('/public/{businessSlug}/real-estate/viewing-requests', [RealEstateController::class, 'submitViewing']);
Route::post('/public/{businessSlug}/real-estate/reservation-interests', [RealEstateController::class, 'submitReservationInterest']);

Route::middleware('auth:sanctum')->prefix('real-estate')->group(function (): void {
    Route::get('/projects', [RealEstateController::class, 'projects'])->middleware('permission:real_estate.view');
    Route::get('/leads', [RealEstateController::class, 'leads'])->middleware('permission:leads.view');
});
