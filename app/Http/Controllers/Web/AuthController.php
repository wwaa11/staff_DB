<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private StaffService $staffService)
    {
    }

    public function index()
    {
        return view('index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'userid' => 'required|string',
            'password' => 'required|string',
        ]);

        $userid = trim($request->userid);

        if (! $this->staffService->authLdap($userid, $request->password)) {
            return back()->withInput($request->only('userid'))->with('error', 'รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง');
        }

        $user = $this->staffService->findOrImportUser($userid);
        if ($user == null) {
            return back()->withInput($request->only('userid'))->with('error', 'ไม่พบข้อมูลพนักงานในระบบ');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/user-approver');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
