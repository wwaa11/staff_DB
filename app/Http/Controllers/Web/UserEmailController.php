<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\Request;

class UserEmailController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function index()
    {
        $emails = $this->staffService->listUserEmails();
        $orphans = $this->staffService->orphanEmails();

        return view('user_email', compact('emails', 'orphans'));
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
            'email' => 'required|email|max:255',
        ]);

        $userid = trim($request->userid);
        $user = User::where('userid', $userid)->first();
        if ($user == null) {
            return redirect()->back()->withInput()->with('error', 'ไม่พบรหัสพนักงาน: '.$userid);
        }

        $this->staffService->saveUserEmail($userid, trim($request->email));

        return redirect('/user-email')->with('success', 'บันทึกอีเมลของ '.$user->name.' เรียบร้อย');
    }

    public function destroy($id)
    {
        $this->staffService->removeUserEmail((int) $id);

        return redirect('/user-email')->with('success', 'ลบอีเมลเรียบร้อย');
    }

    public function destroyOrphans()
    {
        $deleted = $this->staffService->deleteOrphanEmails();
        if ($deleted === 0) {
            return redirect('/user-email')->with('error', 'ไม่มีอีเมลที่ไม่มีพนักงานให้ลบ');
        }

        return redirect('/user-email')->with('success', 'ลบอีเมลที่ไม่มีพนักงานแล้ว '.$deleted.' รายการ');
    }

    public function destroyOrphan($id)
    {
        $deleted = $this->staffService->deleteOrphanEmails([(int) $id]);
        if ($deleted === 0) {
            return redirect('/user-email')->with('error', 'ไม่สามารถลบได้ เพราะอีเมลนี้ยังมีพนักงานอยู่');
        }

        return redirect('/user-email')->with('success', 'ลบอีเมลที่ไม่มีพนักงานเรียบร้อย');
    }
}
