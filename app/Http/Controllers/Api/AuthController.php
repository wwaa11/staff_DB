<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function auth(Request $request)
    {
        if (! $this->staffService->authLdap($request->userid, $request->password)) {
            return response()->json(['status' => 2, 'message' => 'Userid or Password not correct.'], 400);
        }

        $user = $this->staffService->getOrImportProfile($request->userid);
        if ($user == null) {
            return response()->json(['status' => 2, 'message' => 'UserID not found.'], 200);
        }

        return response()->json(['status' => 1, 'message' => 'Auth Success.', 'user' => $user], 200);
    }
}
