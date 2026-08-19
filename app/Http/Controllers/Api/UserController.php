<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function show(Request $request)
    {
        $user = $this->staffService->getOrImportProfile($request->userid);
        if ($user == null) {
            return response()->json(['status' => 2, 'message' => 'UserID not found.'], 200);
        }

        return response()->json(['status' => 1, 'message' => 'Get data success.', 'user' => $user], 200);
    }
}
