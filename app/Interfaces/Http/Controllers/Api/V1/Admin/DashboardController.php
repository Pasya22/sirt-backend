<?php

namespace App\Interfaces\Http\Controllers\Api\V1\Admin;

use App\Application\Dashboard\Queries\AmbilDashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    public function __invoke(AmbilDashboardQuery $query): JsonResponse
    {
        return response()->json($query->execute());
    }
}