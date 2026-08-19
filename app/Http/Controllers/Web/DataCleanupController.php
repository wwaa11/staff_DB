<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApproverService;
use App\Services\DepartmentService;
use App\Services\StaffService;

class DataCleanupController extends Controller
{
    public function __construct(
        private StaffService $staffService,
        private DepartmentService $departmentService,
        private ApproverService $approverService
    ) {
    }

    public function index()
    {
        return view('data_cleanup', [
            'orphans' => $this->staffService->orphanEmails(),
            'unused' => $this->departmentService->unused(),
            'incompleteApprovers' => $this->approverService->incompleteDepartmentApprovers(),
        ]);
    }

    public function destroyOrphans()
    {
        $deleted = $this->staffService->deleteOrphanEmails();
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่มีอีเมลที่ไม่มีพนักงานให้ลบ');
        }

        return redirect('/data-cleanup')->with('success', 'ลบอีเมลที่ไม่มีพนักงานแล้ว '.$deleted.' รายการ');
    }

    public function destroyOrphan($id)
    {
        $deleted = $this->staffService->deleteOrphanEmails([(int) $id]);
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่สามารถลบได้ เพราะอีเมลนี้ยังมีพนักงานอยู่');
        }

        return redirect('/data-cleanup')->with('success', 'ลบอีเมลที่ไม่มีพนักงานเรียบร้อย');
    }

    public function destroyUnused($id)
    {
        $deleted = $this->departmentService->deleteUnused([(int) $id]);
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่สามารถลบได้ เพราะยังมีพนักงานในแผนก/ฝ่ายนี้');
        }

        return redirect('/data-cleanup')->with('success', 'ลบแผนก/ฝ่ายที่ไม่มีพนักงานเรียบร้อย');
    }

    public function destroyAllUnused()
    {
        $deleted = $this->departmentService->deleteUnused();
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่มีแผนก/ฝ่ายว่างให้ลบ');
        }

        return redirect('/data-cleanup')->with('success', 'ลบแผนก/ฝ่ายที่ไม่มีพนักงานแล้ว '.$deleted.' รายการ');
    }

    public function destroyIncompleteApprovers()
    {
        $deleted = $this->approverService->deleteIncompleteDepartmentApprovers();
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่มีผู้อนุมัติที่ไม่มีแผนก/ฝ่ายให้ลบ');
        }

        return redirect('/data-cleanup')->with('success', 'ลบผู้อนุมัติที่ไม่มีแผนก/ฝ่ายแล้ว '.$deleted.' รายการ');
    }

    public function destroyIncompleteApprover($id)
    {
        $deleted = $this->approverService->deleteIncompleteDepartmentApprovers([(int) $id]);
        if ($deleted === 0) {
            return redirect('/data-cleanup')->with('error', 'ไม่สามารถลบได้ เพราะผู้อนุมัตินี้ยังมีแผนกและฝ่าย');
        }

        return redirect('/data-cleanup')->with('success', 'ลบผู้อนุมัติที่ไม่มีแผนก/ฝ่ายเรียบร้อย');
    }
}
