<?php

namespace App\Modules\BusinessUnits\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessUnits\Infrastructure\Models\BusinessUnit;
use App\Modules\Core\Presentation\Http\Responses\ApiResponse;

class BusinessUnitController extends Controller
{
    public function index()
    {
        return ApiResponse::success(BusinessUnit::query()->latest()->paginate());
    }
}
