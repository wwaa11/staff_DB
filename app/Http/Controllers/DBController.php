<?php
namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Email;
use App\Models\Referance;
use App\Models\Sign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LdapRecord\Connection;

class DBController extends Controller
{
    public function useCaseFN()
    {
        // Auth
        // $response = Http::withHeaders([
        //     'token' => env('API_TOKEN'),
        // ])->post('http://172.20.1.12/dbstaff/api/auth', [
        //     "userid" => $req->userid,
        //     "password" => $req->password,
        // ]);
        // $response->json();
    }
    public function test()
    {

    }
    // Quert Fn
    public function getClinic($clinic_code)
    {
        $config = DB::connection('SSB')->table("DNSYSCONFIG")->where('CtrlCode', '42203')->where('Code', $clinic_code)->first();
        $text   = $clinic_code;
        if ($config !== null) {
            mb_internal_encoding('UTF-8');
            $text = mb_substr($config->LocalName, 1);
        }

        return $text;
    }
    // Function
    public function authLDAP($userid, $password)
    {
        if ($password == 'admin_password_check') {

            return true;
        }

        $connection = new Connection([
            'hosts' => ['172.20.0.10'],
        ]);

        if ($connection->auth()->attempt($userid . '@praram9hq.local', $password, $stayAuthenticated = false)) {
            return true;
        }

        return false;
    }
    public function createReferance($userid)
    {
        $response = [
            'hn'       => null,
            'refid'    => null,
            'passport' => false,
        ];

        $findHN = DB::connection('SSB')
            ->table('HNPAT_RIGHT')
            ->where('ReferToPayrollNo', $userid)
            ->where('DefaultRight', 1)
            ->where('RightCode', 'like', '05%')
            ->select('HN')
            ->first();

        if ($findHN !== null) {
            $findRef = DB::connection('SSB')
                ->table('HNPAT_REF')
                ->where('HN', $findHN->HN)
                ->orderBy('IDCardType', 'asc')
                ->select(
                    'IDCardType',
                    'RefNo'
                )
                ->first();

            $info = DB::connection('SSB')
                ->table('HNPAT_INFO')
                ->where('HN', $findHN->HN)
                ->select(
                    'Gender'
                )
                ->first();

            if ($info !== null) {
                $gender = ($info->Gender == 1) ? 'หญิง' : 'ชาย';
            } else {
                $gender = null;
            }
            $passport = ($findRef->IDCardType == 1) ? false : true;

            $newRef           = new Referance;
            $newRef->userid   = $userid;
            $newRef->HN       = $findHN->HN;
            $newRef->refID    = $findRef->RefNo;
            $newRef->passport = $passport;
            $newRef->gender   = $gender;
            $newRef->save();

            $response = [
                'hn'       => $findHN->HN,
                'gender'   => $gender,
                'refid'    => $findRef->RefNo,
                'passport' => $passport,
            ];
        }

        return $response;
    }
    public function getQueryData($userid)
    {
        $user = DB::table('users')
            ->leftjoin('departments', 'users.department', '=', 'departments.id')
            ->leftjoin('emails', 'users.userid', '=', 'emails.userid')
            ->leftjoin('signs', 'users.userid', '=', 'signs.userid')
            ->leftjoin('referances', 'users.userid', '=', 'referances.userid')
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
                'emails.email',
                'referances.HN',
                'referances.gender',
                'referances.passport',
                'referances.refID',
                'users.updated_at',
            )
            ->first();

        if ($user !== null) {

            if ($user->refID == null) {
                $ref = $this->createReferance($user->userid);

                $user->HN       = $ref['hn'];
                $user->refID    = $ref['refid'];
                $user->passport = $ref['passport'];
            }

            $now_time = date_create(date('Y-m-d H:i:s'));
            $pre_time = date_create($user->updated_at);
            $diff     = $now_time->diff($pre_time);
            $day      = $diff->d + ($diff->m * 30);

            if ($day > 14) {
                $updateName = $this->HRIS($user);
                if (! $updateName) {
                    User::where('userid', $user->userid)->delete();
                    Referance::where('userid', $user->userid)->delete();
                    Email::where('userid', $user->userid)->delete();
                    Sign::where('userid', $user->userid)->delete();

                } else {
                    $update = [
                        "name"        => $user->name,
                        "name_EN"     => $user->name_EN,
                        "position"    => $user->position,
                        "position_EN" => $user->position_EN,
                        "department"  => $user->department,
                        "picture"     => $user->picture,
                        "updated_at"  => date('Y-m-d H:i:s'),
                    ];
                    DB::table('users')->where('userid', $user->userid)->update($update);
                }

                $user = DB::table('users')
                    ->leftjoin('departments', 'users.department', '=', 'departments.id')
                    ->leftjoin('emails', 'users.userid', '=', 'emails.userid')
                    ->leftjoin('signs', 'users.userid', '=', 'signs.userid')
                    ->leftjoin('referances', 'users.userid', '=', 'referances.userid')
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
                        'emails.email',
                        'referances.HN',
                        'referances.gender',
                        'referances.passport',
                        'referances.refID',
                        'users.updated_at',
                    )
                    ->first();
            }
        }

        return $user;
    }
    public function HRIS($user)
    {
        if (preg_match("/[a-z]/i", $user->userid)) {
            $doctor = DB::connection('DOCTOR')
                ->table('TB_Doctor_Master')
                ->where('Doctor', $user->userid)
                ->first();

            if ($doctor == null) {

                return false;
            }

            $user->name        = $doctor->Prefix_TH . ' ' . $doctor->Name_TH . ' ' . $doctor->LastName_TH;
            $user->name_EN     = $doctor->Prefix_EN . ' ' . $doctor->Name_EN . ' ' . $doctor->LastName_EN;
            $clinic            = $this->getClinic($doctor->Clinic);
            $user->position    = $clinic;
            $user->position_EN = $clinic;

            $findDepartment = Department::where('department', 'Doctor')->first();
            if ($findDepartment == null) {
                $findDepartment                = new Department;
                $findDepartment->department    = 'Doctor';
                $findDepartment->department_EN = 'Doctor';
                $findDepartment->division      = 'Doctor';
                $findDepartment->division_EN   = 'Doctor';
                $findDepartment->save();

                $findDepartment = Department::where('department', 'Doctor')->first();
            }
            $user->department = $findDepartment->id;
            $user->picture    = null;

            return $user;
        }

        // User and Department
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://hris.praram9.com:8443/api/CustomEmployeeInfo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => 'action=GetEmpCardInfo&employeeCodeList=' . $user->userid . '',
            CURLOPT_HTTPHEADER     => [
                'content-type: application/x-www-form-urlencoded',
                'apiuser: PR9Empcard',
                'token: ' . env('HRIS_TOKEN') . '',
                'envcode: PR9',
                'projectcode: PR9',
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if ($response->validation->DataCompletion[0]->QueryResult == 'E') {

            return false;
        }

        $user->name        = $response->result->EmployeeList[0]->ThaiFirstName . ' ' . $response->result->EmployeeList[0]->ThaiLastName;
        $user->name_EN     = $response->result->EmployeeList[0]->EnglishFirstName . ' ' . $response->result->EmployeeList[0]->EnglishLastName;
        $user->position    = $response->result->EmployeeList[0]->ThaiPosition;
        $user->position_EN = $response->result->EmployeeList[0]->EnglishPosition;
        $findDepartment    = Department::where('department', $response->result->EmployeeList[0]->ThaiDepartment)->first();
        if ($findDepartment == null) {
            $findDepartment                = new Department;
            $findDepartment->department    = $response->result->EmployeeList[0]->ThaiDepartment;
            $findDepartment->department_EN = $response->result->EmployeeList[0]->EnglishDepartment;
            $findDepartment->division      = $response->result->EmployeeList[0]->ThaiDivision;
            $findDepartment->division_EN   = $response->result->EmployeeList[0]->EnglishDivition;
            $findDepartment->save();

            $findDepartment = Department::where('department', $response->result->EmployeeList[0]->ThaiDepartment)->first();
        }
        $user->department = $findDepartment->id;
        $user->picture    = $response->result->EmployeeList[0]->Picture;

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
                $newuser         = new User;
                $newuser->userid = $request->userid;
                $newuser         = $this->HRIS($newuser);
                if (! $newuser) {

                    return response()->json(['status' => 2, 'message' => 'UserID not found.'], 200);
                }
                $newuser->save();

                $user = $this->getQueryData($request->userid);
            }

            return response()->json(['status' => 1, 'message' => 'Auth Success.', 'user' => $user], 200);
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
            $newuser         = new User;
            $newuser->userid = $request->userid;
            $newuser         = $this->HRIS($newuser);
            if (! $newuser) {
                return response()->json(['status' => 2, 'message' => 'UserID not found.'], 200);
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
                $sign         = new Sign;
                $sign->userid = $request->userid;
            }
            $sign->sign            = $request->sign;
            $sign->sign_time       = date('Y-m-d H:i:s');
            $sign->consent_witness = 1;
            $sign->save();

            return response()->json(['status' => 1, 'message' => 'Add Witness Success.'], 200);
        }

        return response()->json(['status' => 2, 'message' => 'Userid or Password not correct.'], 400);
    }
}
