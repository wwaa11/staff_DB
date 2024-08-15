<?php

namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Consent;
use App\Models\Department;
use App\Models\Email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DBController extends Controller
{
    public function test()
    {

    }
    public function getQueryData($userid)
    {
        $user = DB::table('users')
            ->leftjoin('departments', 'users.department', '=', 'departments.id')
            ->leftjoin('emails', 'users.userid', '=', 'emails.userid')
            ->leftjoin('consents', 'users.userid', '=', 'consents.userid')
            ->where('users.userid', $userid)
            ->select(
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
                'consents.consent_sign',
            )
            ->first();
        return $user;
    }
    public function HRIS($user)
    {
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
        $user->department = $findDepartment->department;
        $user->department_id = $findDepartment->id;
        $user->picture = $response->result->EmployeeList[0]->Picture;

        return $user;
    }
    public function API_Auth(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {

            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }
        $connection = new \LdapRecord\Connection([
            'hosts' => ['172.20.0.10'],
        ]);
        if ($connection->auth()->attempt($request->userid . '@praram9hq.local', $request->password, $stayAuthenticated = true)) {
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
                        "department" => $user->department_id,
                        "picture" => $user->picture,
                        "updated_at" => date('Y-m-d H:i:s'),
                    ];
                    DB::table('users')->where('userid', $user->userid)->update($update);
                }

                $user = $this->getQueryData($request->userid);

                return response()->json(['status' => 1, 'message' => 'Auth updated user success.', 'user' => $user], 200);
            }
        } else {

            return response()->json(['status' => 2, 'message' => 'Userid or Password not correct.'], 400);
        }
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
    public function Import_main()
    {
        $DMSusers = DB::connection('DMS')->table('users')->get();
        foreach ($DMSusers as $item) {
            $findDepartment = Department::where('department', $item->department)->first();
            if ($findDepartment == null) {
                $findDepartment = new Department;
                $findDepartment->department = $item->department;
                $findDepartment->division = $item->division;
                $findDepartment->save();
            }

            $findConsent = Consent::where('userid', $item->userid)->first();
            if ($findConsent == null) {
                $findConsent = new Consent;
                $findConsent->userid = $item->userid;
                $findConsent->consent_witness = $item->is_witness;
                $findConsent->consent_sign = $item->sign;
                $findConsent->save();
            }

            $findEmail = Email::where('userid', $item->userid)->first();
            $EmailData = DB::connection('EMAIL')->table('divisions')->where('userid', $item->userid)->first();
            if ($findEmail == null && $EmailData !== null) {
                if ($EmailData->email !== 'TEXT_EDIT' && $EmailData->email !== null) {
                    $findEmail = new Email;
                    $findEmail->userid = $item->userid;
                    $findEmail->email = $EmailData->email;
                    $findEmail->save();
                }
            } else {
                $EmailDataCRS = DB::connection('CRS')->table('departments')->where('userid', $item->userid)->first();
                if ($findEmail == null && $EmailDataCRS !== null) {
                    $findEmail = new Email;
                    $findEmail->userid = $item->userid;
                    $findEmail->email = $EmailDataCRS->email;
                    $findEmail->save();
                }
            }

            $findUser = User::where('userid', $item->userid)->first();
            if ($findUser == null) {
                $findUser = new User;
                $findUser->userid = $item->userid;
                $findUser->name = $item->name;
                $findUser->position = $item->position;
                $findUser->department = $findDepartment->id;
                $findUser->save();
            }
        }
    }
    public function Import_approve()
    {
        $getDivision = DB::connection('EMAIL')
            ->table('divisions')
            ->whereNotNull('userid')
            ->whereNotNull('department')
            ->orderBy('department', 'asc')
            ->orderBy('level', 'asc')
            ->get();
        $tempDeptName = '';
        $tempLevel = 1;
        foreach ($getDivision as $item) {
            if ($tempDeptName !== $item->department) {
                $tempDeptName = $item->department;
                $tempLevel = 1;
            } else {
                $tempLevel++;
            }
            $dept = Department::where('department', $item->department)->first();
            if ($dept !== null) {
                $findNew = Approver::where('department_id', $dept->id)->where('userid', $item->userid)->first();
                if ($findNew == null) {
                    $new = new Approver;
                    $new->department_id = $dept->id;
                    $new->userid = $item->userid;
                    $new->name = $item->username;
                    $new->email = $item->email;
                    $new->level = $tempLevel;
                    $new->save();
                }
            }
        }
    }
}
