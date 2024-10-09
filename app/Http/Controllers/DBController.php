<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Sign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DBController extends Controller
{
    public function useCaseFN()
    {
        // Auth
        $response = Http::withHeaders([
            'token' => env('API_TOKEN'),
        ])->post('http://172.20.1.12/dbstaff/api/auth', [
            "userid" => $req->userid,
            "password" => $req->password,
        ]);
        $response->json();
    }
    public function test()
    {

    }
    // Function
    public function authLDAP($userid, $password)
    {
        $connection = new \LdapRecord\Connection([
            'hosts' => ['172.20.0.10'],
        ]);
        if ($connection->auth()->attempt($userid . '@praram9hq.local', $password, $stayAuthenticated = true)) {
            return true;
        }

        return false;
    }
    public function getQueryData($userid)
    {
        $user = DB::table('users')
            ->leftjoin('departments', 'users.department', '=', 'departments.id')
            ->leftjoin('emails', 'users.userid', '=', 'emails.userid')
            ->leftjoin('signs', 'users.userid', '=', 'signs.userid')
            ->where('users.userid', $userid)
            ->select(
                'users.userid',
                'users.name',
                'users.name_EN',
                'users.position',
                'users.position_EN',
                'departments.department',
                'departments.department_EN',
                'departments.division',
                'departments.division_EN',
                'departments.updated_at',
                'emails.email',
            )
            ->first();

        return $user;
    }
    public function HRIS($user)
    {
        // User and Department
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://hris.praram9.com:8443/api/CustomEmployeeInfo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'action=GetEmpCardInfo&employeeCodeList=' . $user->userid . '',
            CURLOPT_HTTPHEADER => array(
                'content-type: application/x-www-form-urlencoded',
                'apiuser: PR9Empcard',
                'token: ' . env('HRIS_TOKEN') . '',
                'envcode: PR9',
                'projectcode: PR9',
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);
        if ($response->validation->DataCompletion[0]->QueryResult == 'E') {
            return false;
        }
        $user->name = $response->result->EmployeeList[0]->ThaiFirstName . ' ' . $response->result->EmployeeList[0]->ThaiLastName;
        $user->name_EN = $response->result->EmployeeList[0]->EnglishFirstName . ' ' . $response->result->EmployeeList[0]->EnglishLastName;
        $user->position = $response->result->EmployeeList[0]->ThaiPosition;
        $user->position_EN = $response->result->EmployeeList[0]->EnglishPosition;
        $findDepartment = Department::where('department', $response->result->EmployeeList[0]->ThaiDepartment)->first();
        if ($findDepartment == null) {
            $findDepartment = new Department;
            $findDepartment->department = $response->result->EmployeeList[0]->ThaiDepartment;
            $findDepartment->department_EN = $response->result->EmployeeList[0]->EnglishDepartment;
            $findDepartment->division = $response->result->EmployeeList[0]->ThaiDivision;
            $findDepartment->division_EN = $response->result->EmployeeList[0]->EnglishDivition;
            $findDepartment->save();

            $findDepartment = Department::where('department', $response->result->EmployeeList[0]->ThaiDepartment)->first();
        } else {
            $now_time = date_create(date('Y-m-d H:i:s'));
            $pre_time = date_create($findDepartment->updated_at);
            $diff = $now_time->diff($pre_time);
            $day = $diff->d + ($diff->m * 30);
            if ($day > 14) {
                $findDepartment->department = $response->result->EmployeeList[0]->ThaiDepartment;
                $findDepartment->department_EN = $response->result->EmployeeList[0]->EnglishDepartment;
                $findDepartment->division = $response->result->EmployeeList[0]->ThaiDivision;
                $findDepartment->division_EN = $response->result->EmployeeList[0]->EnglishDivition;
                $findDepartment->save();
            }
        }
        $user->department = $findDepartment->id;
        $user->picture = $response->result->EmployeeList[0]->Picture;

        return $user;
    }
    // API
    public function API_Auth(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {

            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }
        $auth = $this->authLDAP($request->userid, $request->password);
        if ($auth == true) {
            $user = $this->getQueryData($request->userid);
            if ($user == null) {
                $newuser = new User;
                $newuser->userid = $request->userid;
                $newuser = $this->HRIS($newuser);
                $newuser->save();

                $user = $this->getQueryData($request->userid);

                return response()->json(['status' => 1, 'message' => 'Auth new user success.', 'user' => $user], 200);
            } else {
                $now_time = date_create(date('Y-m-d H:i:s'));
                $pre_time = date_create($user->updated_at);
                $diff = $now_time->diff($pre_time);
                $day = $diff->d + ($diff->m * 30);
                if ($day > 14) {
                    $user = $this->HRIS($user);
                    $update = [
                        "name" => $user->name,
                        "name_EN" => $user->name_EN,
                        "position" => $user->position,
                        "position_EN" => $user->position_EN,
                        "department" => $user->department,
                        "picture" => $user->picture,
                        "updated_at" => date('Y-m-d H:i:s'),
                    ];
                    DB::table('users')->where('userid', $user->userid)->update($update);
                }
                $user = $this->getQueryData($request->userid);

                return response()->json(['status' => 1, 'message' => 'Auth updated user success.', 'user' => $user], 200);
            }
        }

        return response()->json(['status' => 2, 'message' => 'Userid or Password not correct.'], 400);
    }
    public function API_getUser(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {

            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }

        $user = $this->getQueryData($request->userid);
        if ($user == null) {
            $newuser = new User;
            $newuser->userid = $request->userid;
            $newuser = $this->HRIS($newuser);
            if (!$newuser) {
                return response()->json(['status' => 1, 'message' => 'UserID not found.'], 200);
            }
            $newuser->save();

            $user = $this->getQueryData($request->userid);
        }

        return response()->json(['status' => 1, 'message' => 'Get data success.', 'user' => $user], 200);

    }
    public function API_AddWitness(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {

            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }
        $auth = $this->authLDAP($request->userid, $request->password);
        if ($auth == true) {
            $sign = Sign::where('userid', $request->userid)->first();
            if ($sign == null) {
                $sign = new Sign;
                $sign->userid = $request->userid;
            }
            $sign->sign = $request->sign;
            $sign->sign_time = date('Y-m-d H:i:s');
            $sign->consent_witness = 1;
            $sign->save();

            return response()->json(['status' => 1, 'message' => 'Add Witness Success.'], 200);
        }

        return response()->json(['status' => 2, 'message' => 'Userid or Password not correct.'], 400);
    }
}
