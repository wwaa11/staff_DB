<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $departmentService)
    {
    }

    public function cleanup()
    {
        $unused = $this->departmentService->unused();

        return view('department_cleanup', compact('unused'));
    }

    public function destroyUnused(Request $request, $id)
    {
        $deleted = $this->departmentService->deleteUnused([(int) $id]);
        if ($deleted === 0) {
            return redirect('/department-cleanup')->with('error', 'ไม่สามารถลบได้ เพราะยังมีพนักงานในแผนก/ฝ่ายนี้');
        }

        return redirect('/department-cleanup')->with('success', 'ลบแผนก/ฝ่ายที่ไม่มีพนักงานเรียบร้อย');
    }

    public function destroyAllUnused()
    {
        $deleted = $this->departmentService->deleteUnused();
        if ($deleted === 0) {
            return redirect('/department-cleanup')->with('error', 'ไม่มีแผนก/ฝ่ายว่างให้ลบ');
        }

        return redirect('/department-cleanup')->with('success', 'ลบแผนก/ฝ่ายที่ไม่มีพนักงานแล้ว '.$deleted.' รายการ');
    }
}
