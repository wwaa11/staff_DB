<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ApproverService;
use App\Services\StaffService;
use Illuminate\Http\Request;

class UserApproverController extends Controller
{
    public function __construct(
        private ApproverService $approverService,
        private StaffService $staffService
    ) {
    }

    public function index()
    {
        $mappings = $this->approverService->listMappings();

        return view('user_approver', compact('mappings'));
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
            'userid' => 'required|string',
            'approver_userid' => 'required|string',
        ]);

        $userid = trim($request->userid);
        $approverUserid = trim($request->approver_userid);

        $user = User::where('userid', $userid)->first();
        $approver = User::where('userid', $approverUserid)->first();

        if ($user == null) {
            return redirect()->back()->withInput()->with('error', 'ไม่พบรหัสพนักงาน: '.$userid);
        }
        if ($approver == null) {
            return redirect()->back()->withInput()->with('error', 'ไม่พบรหัสผู้อนุมัติ: '.$approverUserid);
        }

        $this->approverService->assign($userid, $approverUserid);

        return redirect('/user-approver')->with('success', 'บันทึกผู้อนุมัติของ '.$user->name.' เรียบร้อย');
    }

    public function destroy($id)
    {
        $this->approverService->remove((int) $id);

        return redirect('/user-approver')->with('success', 'ลบการกำหนดผู้อนุมัติเรียบร้อย');
    }
}
