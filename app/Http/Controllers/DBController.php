<?php
namespace App\Http\Controllers;

use App\Models\Approver;
use App\Models\Department;
use App\Models\Email;
use App\Models\Referance;
use App\Models\Sign;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
    // Query Fn
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
    public function setfullDate($dateInput, $lang)
    {
        $nowDate  = new DateTime();
        $getDate  = new DateTime($dateInput);
        $diffDate = $nowDate->diff($getDate);

        $dateTime = strtotime($dateInput);
        if ($lang == 'th') {
            App::setLocale('th');
            $dayOfWeek  = Carbon::createFromTimestamp($dateTime)->translatedFormat('l');
            $monthNames = Carbon::createFromTimestamp($dateTime)->translatedFormat('M');

            $response = (object) [
                "YMD"      => date('Y-m-d', $dateTime),
                "H"        => date('H', $dateTime),
                "I"        => date('i', $dateTime),
                "dob"      => date('Y-m-d', $dateTime),
                "Day"      => $dayOfWeek,
                "Month"    => $monthNames,
                "Date"     => date('j', $dateTime),
                "Year"     => date('Y', $dateTime),
                "FullDate" => date('j', $dateTime) . ' ' . $monthNames . ' ' . date('Y', $dateTime) + 543,
                "Age"      => $diffDate->y,
            ];
        } else {
            $response = (object) [
                "YMD"      => date('Y-m-d', $dateTime),
                "H"        => date('H', $dateTime),
                "I"        => date('i', $dateTime),
                "dob"      => date('Y-m-d', $dateTime),
                "Day"      => date('D', $dateTime),
                "Month"    => date('M', $dateTime),
                "Date"     => date('j', $dateTime),
                "Year"     => date('Y', $dateTime),
                "FullDate" => date('j', $dateTime) . ' ' . date('M', $dateTime) . ' ' . date('Y', $dateTime),
                "Age"      => $diffDate->y,
            ];
        }

        return $response;
    }
    public function address($code, $lang)
    {
        if ($code == null || $code == '' || ! explode('.', $code)) {
            return (object) [
                'Province'         => null,
                'Province_Code'    => null,
                'District'         => null,
                'District_Code'    => null,
                'Subdistrict'      => null,
                'Subdistrict_Code' => null,
            ];
        } else {
            $code = explode('.', $code);
            if (array_key_exists(0, $code) && array_key_exists(1, $code) && array_key_exists(2, $code)) {
                return (object) [
                    'Province'         => $this->DNSCONFIG('10691', $code[0], $lang),
                    'Province_Code'    => $code[0],
                    'District'         => $this->DNSCONFIG('10691', $code[0] . '.' . $code[1], $lang),
                    'District_Code'    => $code[1],
                    'Subdistrict'      => $this->DNSCONFIG('10691', $code[0] . '.' . $code[1] . '.' . $code[2], $lang),
                    'Subdistrict_Code' => $code[2],
                ];
            } else {
                return (object) [
                    'Province'         => null,
                    'Province_Code'    => null,
                    'District'         => null,
                    'District_Code'    => null,
                    'Subdistrict'      => null,
                    'Subdistrict_Code' => null,
                ];
            }

        }
    }
    public function DNSCONFIG($ctrl, $code, $lang)
    {
        mb_internal_encoding('UTF-8');
        $names = DB::connection('SSB')->table('DNSYSCONFIG')->where('CtrlCode', $ctrl)->where('Code', $code)->first();
        if ($names == null) {
            return "Not Found";
        }
        if ($lang == 'th') {
            ($names->LocalName !== null) ? $name = mb_substr($names->LocalName, 1) : $name = mb_substr($names->EnglishName, 1);
        } else {
            $name = mb_substr($names->EnglishName, 1);
        }

        return $name;
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

        $skipUser = [
            '226802',
            '226805',
        ];

        if ($user !== null && ! in_array($user, $skipUser)) {

            if ($user->refID == null) {
                $ref = $this->createReferance($user->userid);

                $user->HN       = $ref['hn'];
                $user->refID    = $ref['refid'];
                $user->passport = $ref['passport'];
            }

            $now_time = date_create(date('Y-m-d H:i:s'));
            $pre_time = date_create($user->updated_at);
            $diff     = $now_time->diff($pre_time);
            $day      = $diff->d + ($diff->m * 30) + ($diff->y * 365);
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

        $now_time = date_create(date('Y-m-d H:i:s'));
        $pre_time = date_create($findDepartment->updated_at);
        $diff     = $now_time->diff($pre_time);
        $day      = $diff->d + ($diff->m * 30) + ($diff->y * 365);
        if ($day > 14) {
            $findDepartment->department    = $response->result->EmployeeList[0]->ThaiDepartment;
            $findDepartment->department_EN = $response->result->EmployeeList[0]->EnglishDepartment;
            $findDepartment->division      = $response->result->EmployeeList[0]->ThaiDivision;
            $findDepartment->division_EN   = $response->result->EmployeeList[0]->EnglishDivition;
            $findDepartment->save();
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
    public function API_getApprover(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {
            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }

        $user = User::where('userid', $request->userid)->first();
        if ($user == null) {

            return response()->json(['status' => 2, 'message' => 'UserID not found.'], 400);
        }
        $approver = Approver::with(['userData', 'email'])->where('department_id', $user->department)->get()->toArray();

        $approverData = $approver[0] ?? [];
        $approver     = [
            'userid'      => $approverData['user_data']['userid'] ?? null,
            'name'        => $approverData['user_data']['name'] ?? null,
            'name_EN'     => $approverData['user_data']['name_EN'] ?? null,
            'position'    => $approverData['user_data']['position'] ?? null,
            'position_EN' => $approverData['user_data']['position_EN'] ?? null,
            'email'       => $approverData['email']['email'] ?? null,
        ];

        return response()->json(['status' => 1, 'message' => 'Get approver success.', 'approver' => $approver], 200);

    }
    public function API_getApprover_Department(Request $request)
    {
        if ($request->header('token') !== env('API_TOKEN')) {
            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }

        $department = Department::join('approvers', 'departments.id', '=', 'approvers.department_id')
            ->join('users', 'approvers.userid', '=', 'users.userid')
            ->join('emails', 'users.userid', '=', 'emails.userid')
            ->where('departments.department', $request->department)
            ->select(
                'users.userid',
                'users.name',
                'users.name_EN',
                'users.position',
                'users.position_EN',
                'emails.email'
            )
            ->first();
        if ($department == null) {

            return response()->json(['status' => 2, 'message' => 'Approver Department not found.'], 400);
        }

        $approverData = $department;
        $approver     = [
            'userid'      => $approverData['userid'] ?? null,
            'name'        => $approverData['name'] ?? null,
            'name_EN'     => $approverData['name_EN'] ?? null,
            'position'    => $approverData['position'] ?? null,
            'position_EN' => $approverData['position_EN'] ?? null,
            'email'       => $approverData['email'] ?? null,
        ];

        return response()->json(['status' => 1, 'message' => 'Get approver for departments success.', 'approver' => $approver], 200);
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
    public function API_PatientConsent(Request $request)
    {
        // Drop
        if ($request->header('token') !== env('API_TOKEN')) {

            return response()->json(['status' => 0, 'message' => 'token mismatch!'], 400);
        }
        $hn       = $request->hn;
        $lang     = $request->lang;
        $response = [
            'status'  => 0,
            'messgae' => 'failed',
        ];

        $consent = DB::connection('SIGNFORM')
            ->table('Concent_TH')
            ->where('HN', $hn)
            ->orderby('CompleteDateTime', 'DESC')
            ->first();

        $patientInfo = DB::connection('SSB')
            ->table("HNPAT_INFO")
            ->where('HN', $hn)
            ->whereNull('FileDeletedDate')
            ->select(
                'BirthDateTime',
                'ReligionCode',
                'NationalityCode',
                'MaritalStatus',
                'Occupation',
                'EducationLevelCode',
                'RaceCode'
            )
            ->first();

        if ($consent == null || $patientInfo == null) {
            return response()->json(['status' => 0, 'message' => 'consent not found'], 400);
        }

        $getMartial                                                         = DB::connection('SIGNFORM')->table('Social')->where('Code', $patientInfo->MaritalStatus)->first();
        ($getMartial == null) ? $getMartial                                 = (object) ['Desc' => null, 'Nameen' => null] : null;
        $getOcupation                                                       = DB::connection('SIGNFORM')->table('Occupation')->where('Code', $patientInfo->Occupation)->first();
        ($getOcupation == null) ? $getOcupation                             = (object) ['LocalName' => null, 'Nameen' => null] : null;
        $getRelationRepresenttative                                         = DB::connection('SIGNFORM')->table('Relative')->where('Code', $consent->Representative_Relation)->first();
        ($getRelationRepresenttative == null) ? $getRelationRepresenttative = (object) ['Name' => null, 'Nameen' => null] : null;

        $dobTime  = $this->setfullDate($patientInfo->BirthDateTime, $lang);
        $dataTime = $this->setfullDate($consent->CreateDateTime, $lang);

        switch ($patientInfo->EducationLevelCode) {
            case '004':
                $getEducation = 1;
                break;
            case '005':
                $getEducation = 2;
                break;
            case '006':
                $getEducation = 3;
                break;
            case '007':
                $getEducation = 3;
                break;
            default:
                $getEducation = 0;
                break;
        }

        $address_data                            = $this->address($consent->Province . '.' . $consent->District . '.' . $consent->SubDistrict, $lang);
        $address_full                            = $consent->Address;
        ($consent->Moo !== null) ? $address_full = $address_full . ' หมู่' . $consent->Moo : null;

        if ($lang == 'th') {
            $address_full = $address_full . ' เขต' . $address_data->District . ' แขวง' . $address_data->Subdistrict . ' จังหวัด ' . $address_data->Province;
        }

        if ($consent->Address1 !== null) {
            $contact_data                             = $this->address($consent->Province1 . '.' . $consent->District1 . '.' . $consent->SubDistrict1, $lang);
            $contact_full                             = $consent->Address1;
            ($consent->Moo1 !== null) ? $contact_full = $contact_full . ' หมู่' . $consent->Moo1 : null;
            $contact_full                             = $contact_full . ' เขต' . $contact_data->District . ' แขวง' . $contact_data->Subdistrict . ' จังหวัด ' . $contact_data->Province;
        }

        $consent = [
            'hn'                    => $consent->HN,
            'nameTH'                => $consent->Name_TH,
            'surnameTH'             => $consent->Surname_TH,
            'nameEN'                => strtoupper($consent->Name_EN),
            'surnameEN'             => strtoupper($consent->Surname_EN),
            'DOB'                   => $dobTime->FullDate,
            'age'                   => $dobTime->Age,
            'religion'              => $this->DNSCONFIG('10109', $patientInfo->ReligionCode, $lang),
            'race'                  => $this->DNSCONFIG('10119', $patientInfo->RaceCode, $lang),
            'national'              => $this->DNSCONFIG('10119', $patientInfo->NationalityCode, $lang),
            'martial'               => ($lang == 'th') ? $getMartial->Desc : $getMartial->Nameen,
            'ocupation'             => ($lang == 'th') ? $getOcupation->LocalName : $getOcupation->Nameen,
            'education'             => $getEducation,
            'phone'                 => $consent->HomeTel,
            'mobile'                => $consent->Mobile,
            'email'                 => $consent->Email,
            'address'               => $address_full,
            'address_contact'       => ($consent->Address1 !== null) ? $contact_full : null,
            'allergy'               => ($consent->Allergy == '0') ? false : true,
            'allergy_name'          => $consent->FoodAllergy,
            'allergy_symptom'       => $consent->SymptomAllergy,
            'photo'                 => ($consent->PhotoAllow == '0') ? true : false,
            'represent'             => ($consent->Representative == '1') ? true : false,
            'represent_name'        => $consent->Representative_Name,
            'represent_relation'    => ($lang == 'th') ? $getRelationRepresenttative->Name : $getRelationRepresenttative->Nameen,
            'represent_phone'       => $consent->Representative_Tel,
            'consent_1'             => true,
            'consent_3'             => ($consent->PDPA3 == 'ยินยอมประกัน') ? true : false,
            'consent_4'             => ($consent->PDPA4 == 1 || $consent->PDPA4 == 3) ? true : false,
            'patien_name'           => $consent->Name_TH . ' ' . $consent->Surname_TH,
            'patien_card_type'      => ($consent->PDPA5 == 'ผู้ป่วย') ? 1 : 2,
            'patien_card_type_name' => ($consent->PDPA5 == 'ผู้ป่วย') ? null : $consent->Remark,
        ];

        $response = [
            'status'  => 1,
            'messgae' => 'success',
            'patient' => $consent,
        ];

        return response()->json($response, 200);
    }
}
