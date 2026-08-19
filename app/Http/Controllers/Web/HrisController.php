<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\Request;
use RuntimeException;

class HrisController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function index()
    {
        $userCount = User::count();
        $running = $this->staffService->isHrisUpdateRunning();
        $last = $this->staffService->lastHrisUpdate();

        return view('hris_update', compact('userCount', 'running', 'last'));
    }

    public function run(Request $request)
    {
        if ($this->staffService->isHrisUpdateRunning()) {
            return redirect('/hris-update')->with('error', 'กำลังอัปเดตข้อมูลอยู่แล้ว กรุณารอให้เสร็จก่อน');
        }

        try {
            $result = $this->staffService->updateAllUsersHris();
        } catch (RuntimeException $e) {
            return redirect('/hris-update')->with('error', 'กำลังอัปเดตข้อมูลอยู่แล้ว กรุณารอให้เสร็จก่อน');
        }

        $this->staffService->saveHrisUpdateResult([
            'ran_at' => date('Y-m-d H:i:s'),
            'ran_by' => $request->user()->userid,
            'ran_by_name' => $request->user()->name,
            'total' => $result['total'],
            'updated' => $result['updated'],
            'deleted' => $result['deleted'],
        ]);

        return redirect('/hris-update')->with(
            'success',
            'อัปเดตเสร็จแล้ว: ทั้งหมด '.$result['total'].' อัปเดต '.$result['updated'].' ลบ '.$result['deleted']
        );
    }
}
