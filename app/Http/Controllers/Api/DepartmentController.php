<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\StaffService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function index()
    {
        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'departments' => Department::all(),
        ], 200);
    }

    public function positions(Request $request)
    {
        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'positions' => $this->staffService->positionsByDepartment($request->department),
        ], 200);
    }

    public function users(Request $request)
    {
        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'users' => $this->staffService->usersByDepartment($request->department),
        ], 200);
    }

    public function usersByPosition(Request $request)
    {
        return response()->json([
            'status' => 1,
            'messgae' => 'success',
            'users' => $this->staffService->usersByDepartment($request->department, $request->position),
        ], 200);
    }
}
