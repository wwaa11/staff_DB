<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApproverService;
use Illuminate\Http\Request;

class ApproverController extends Controller
{
    public function __construct(private ApproverService $approverService)
    {
    }

    public function show(Request $request)
    {
        $approver = $this->approverService->findForUser($request->userid);
        if ($approver === null) {
            return response()->json(['status' => 2, 'message' => 'UserID not found.'], 400);
        }
        if ($approver === []) {
            return response()->json(['status' => 2, 'message' => 'Approver not found.'], 400);
        }

        return response()->json(array_merge([
            'status' => 1,
            'message' => 'Get approver success.',
        ], $approver), 200);
    }

    public function showByDepartment(Request $request)
    {
        $approver = $this->approverService->findForDepartment($request->department);
        if ($approver === null) {
            return response()->json(['status' => 2, 'message' => 'Approver Department not found.'], 400);
        }

        return response()->json(array_merge([
            'status' => 1,
            'message' => 'Get approver for departments success.',
        ], $approver), 200);
    }
}
