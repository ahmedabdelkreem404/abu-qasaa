<?php

namespace App\Modules\Core\Presentation\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class PaginationResponse
{
    public static function fromPaginator(LengthAwarePaginator $paginator): JsonResponse
    {
        return ApiResponse::paginated($paginator);
    }
}
