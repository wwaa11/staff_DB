<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\ApproverService;
use App\Services\DepartmentService;
use App\Services\StaffService;
use Illuminate\Http\Request;

class DepartmentApproverController extends Controller
{
    public function __construct(
        private ApproverService $approverService,
        private DepartmentService $departmentService,
        private StaffService $staffService
    ) {
    }

    public function index(Request $request)
    {
        $groupedDepartments = $this->departmentService->groupedForSelect();
        $departmentId = $request->department_id ? (int) $request->department_id : null;
        $selected = $departmentId ? Department::find($departmentId) : null;
        $approvers = $selected ? $this->approverService->listByDepartment($departmentId) : collect();
        $nextLevel = $selected ? $this->approverService->nextLevel($departmentId) : 1;

        return view('department_approver', compact(
            'groupedDepartments',
            'departmentId',
            'selected',
            'approvers',
            'nextLevel'
        ));
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
            'department_id' => 'required|integer',
            'userid' => 'required|string',
            'level' => 'required|integer|min:1',
            'id' => 'nullable|integer',
        ]);

        $approver = $this->approverService->saveDepartmentLevel([
            'id' => $request->id,
            'department_id' => (int) $request->department_id,
            'userid' => trim($request->userid),
            'level' => (int) $request->level,
        ], $request->user());

        if ($approver == null) {
            return redirect()
                ->to('/department-approver?department_id='.$request->department_id)
                ->withInput()
                ->with('error', 'ไม่พบรหัสผู้อนุมัติ: '.$request->userid);
        }

        $message = $request->id ? 'อัปเดตผู้อนุมัติระดับ '.$approver->level.' เรียบร้อย' : 'เพิ่มผู้อนุมัติระดับ '.$approver->level.' เรียบร้อย';

        return redirect()
            ->to('/department-approver?department_id='.$request->department_id)
            ->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $approver = $this->approverService->removeDepartmentLevel((int) $id);
        $departmentId = $approver !== null ? $approver->department_id : $request->department_id;

        return redirect()
            ->to('/department-approver?department_id='.$departmentId)
            ->with('success', 'ลบผู้อนุมัติเรียบร้อย');
    }
}
