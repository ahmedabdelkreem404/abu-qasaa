<?php

use App\Modules\CMS\Presentation\Http\Controllers\CmsController;
use Illuminate\Support\Facades\Route;

Route::get('/public/cms/pages', [CmsController::class, 'publicPages']);
Route::get('/public/cms/pages/{slug}', [CmsController::class, 'publicPage']);
Route::get('/public/cms/business-units/{businessSlug}/page', [CmsController::class, 'publicBusinessUnitPage']);
Route::get('/public/cms/menus/{location}', [CmsController::class, 'publicMenu']);
Route::post('/public/contact-inquiries', [CmsController::class, 'submitInquiry']);

Route::middleware('auth:sanctum')->prefix('cms')->group(function (): void {
    Route::get('/pages', [CmsController::class, 'index'])->middleware('permission:cms.view');
    Route::post('/pages', [CmsController::class, 'store'])->middleware('permission:cms.manage');
    Route::get('/pages/{cmsPage}', [CmsController::class, 'show'])->middleware('permission:cms.view');
    Route::match(['put', 'patch'], '/pages/{cmsPage}', [CmsController::class, 'update'])->middleware('permission:cms.manage');
    Route::delete('/pages/{cmsPage}', [CmsController::class, 'destroy'])->middleware('permission:cms.manage');
    Route::post('/pages/{cmsPage}/publish', [CmsController::class, 'publish'])->middleware('permission:cms.manage');
    Route::put('/pages/{cmsPage}/sections', [CmsController::class, 'upsertSections'])->middleware('permission:cms.manage');

    Route::get('/menus', [CmsController::class, 'menus'])->middleware('permission:cms.view');
    Route::post('/menus', [CmsController::class, 'storeMenu'])->middleware('permission:cms.manage');
    Route::get('/menus/{cmsMenu}', [CmsController::class, 'showMenu'])->middleware('permission:cms.view');
    Route::match(['put', 'patch'], '/menus/{cmsMenu}', [CmsController::class, 'updateMenu'])->middleware('permission:cms.manage');
    Route::delete('/menus/{cmsMenu}', [CmsController::class, 'destroyMenu'])->middleware('permission:cms.manage');

    Route::get('/contact-inquiries', [CmsController::class, 'inquiries'])->middleware('permission_any:leads.view,cms.view');
    Route::get('/contact-inquiries/{contactInquiry}', [CmsController::class, 'showInquiry'])->middleware('permission_any:leads.view,cms.view');
    Route::put('/contact-inquiries/{contactInquiry}/status', [CmsController::class, 'updateInquiryStatus'])->middleware('permission_any:leads.manage,cms.manage');
});
