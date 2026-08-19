<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DepartmentService;
use App\Services\StaffService;
use Illuminate\Http\Request;

class ManualUserController extends Controller
{
    public function __construct(
        private StaffService $staffService,
        private DepartmentService $departmentService
    ) {
    }

    public function index()
    {
        $groupedDepartments = $this->departmentService->groupedForSelect();
        $users = $this->staffService->listManualUsers();

        return view('manual_user', compact('groupedDepartments', 'users'));
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->q);
        if ($q === '') {
            return response()->json([]);
        }

        return response()->json($this->staffService->searchUsers($q));
    }

    public function store(Request $request)
    {
        $request->validate([
            'userid' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'name_EN' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'position_EN' => 'nullable|string|max:255',
            'department_id' => 'required|integer',
            'email' => 'nullable|email|max:255',
        ]);

        $user = $this->staffService->saveManualUser([
            'userid' => trim($request->userid),
            'name' => trim($request->name),
            'name_EN' => trim((string) $request->name_EN),
            'position' => trim((string) $request->position),
            'position_EN' => trim((string) $request->position_EN),
            'department_id' => (int) $request->department_id,
            'email' => trim((string) $request->email),
        ]);

        return redirect('/user-manual')->with('success', 'บันทึกพนักงาน '.$user->userid.' เรียบร้อย');
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->where('skip_hris', 1)->first();
        if ($user == null) {
            return redirect('/user-manual')->with('error', 'ไม่พบพนักงานที่สร้างเอง');
        }

        $this->staffService->removeManualUser($user->userid);

        return redirect('/user-manual')->with('success', 'ลบพนักงาน '.$user->userid.' เรียบร้อย');
    }
}
