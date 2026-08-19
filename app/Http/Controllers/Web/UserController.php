<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Services\DepartmentService;
use App\Services\StaffService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private StaffService $staffService,
        private DepartmentService $departmentService
    ) {
    }

    public function index(Request $request)
    {
        $departmentId = $request->filled('department_id') ? (int) $request->department_id : null;
        $hasDivision = $request->has('division');
        $division = $hasDivision ? (string) $request->division : null;
        $q = trim((string) $request->q);

        if ($departmentId !== null) {
            $department = Department::find($departmentId);
            if ($department !== null) {
                $division = (string) ($department->division ?? '');
                $hasDivision = true;
            }
        }

        $filters = [
            'department_id' => $departmentId,
            'q' => $q,
        ];
        if ($hasDivision) {
            $filters['division'] = $division;
        }

        $users = $this->staffService->listUsers($filters);
        $groupedDepartments = $this->departmentService->groupedForSelect();

        return view('users', [
            'users' => $users,
            'groupedDepartments' => $groupedDepartments,
            'departmentId' => $departmentId,
            'selectedDivision' => $hasDivision ? $division : null,
            'q' => $q,
        ]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'userid' => 'required|string',
            'email' => 'required|email|max:255',
        ]);

        $userid = trim($request->userid);
        $user = User::where('userid', $userid)->first();
        if ($user === null) {
            return redirect()->back()->withInput()->with('error', 'ไม่พบรหัสพนักงาน: '.$userid);
        }

        $this->staffService->saveUserEmail($userid, trim($request->email));

        return redirect()->back()->with('success', 'บันทึกอีเมลของ '.$user->name.' เรียบร้อย');
    }
}
