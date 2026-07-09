<?php

namespace App\Modules\Core\Presentation\Http\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class PaginationResponse
{
    public static function fromPaginator(LengthAwarePaginator $paginator): JsonResponse
    {
        return ApiResponse::success($paginator->items(), meta: [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }
}
