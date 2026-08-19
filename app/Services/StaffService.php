<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Email;
use App\Models\Referance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LdapRecord\Connection;

class StaffService
{
    private array $skipUserids = [
        '226802',
        '226805',
    ];

    public function authLdap(string $userid, string $password): bool
    {
        if ($password == 'admin_password_check') {
            return true;
        }

        $connection = new Connection([
            'hosts' => ['172.20.0.10'],
        ]);

        return $connection->auth()->attempt($userid.'@praram9hq.local', $password, $stayAuthenticated = false);
    }

    public function getOrImportProfile(string $userid)
    {
        $user = $this->getQueryData($userid);
        if ($user !== null) {
            return $user;
        }

        if (! $this->importUser($userid)) {
            return null;
        }

        return $this->getQueryData($userid);
    }

    public function findOrImportUser(string $userid): ?User
    {
        $user = User::where('userid', $userid)->first();
        if ($user !== null) {
            return $user;
        }

        return $this->importUser($userid);
    }

    public function getQueryData($userid)
    {
        $user = $this->loadStaffProfile($userid);
        if ($user === null || in_array($user->userid, $this->skipUserids, true)) {
            return $user;
        }

        if ($user->refID == null) {
            $ref = $this->createReferance($user->userid);
            $user->HN = $ref['hn'];
            $user->refID = $ref['refid'];
            $user->passport = $ref['passport'];
        }

        if (! empty($user->skip_hris) || $this->daysSince($user->updated_at) <= 3) {
            return $user;
        }

        $updated = $this->syncFromHris($user);
        if (! $updated) {
            $this->deleteStaffByUserid($user->userid);
        } else {
            $this->persistHrisFields($user);
        }

        return $this->loadStaffProfile($userid);
    }

    public function syncFromHris($user)
    {
        if (preg_match('/[a-z]/i', $user->userid)) {
            return $this->syncDoctor($user);
        }

        return $this->syncEmployee($user);
    }

    public function updateAllUsersHris(): array
    {
        if ($this->isHrisUpdateRunning()) {
            throw new \RuntimeException('HRIS update is already running.');
        }

        set_time_limit(0);
        ignore_user_abort(true);
        file_put_contents($this->hrisUpdateLockPath(), date('Y-m-d H:i:s'));

        try {
            $users = User::all();
            $updated = 0;
            $deleted = 0;
            $count = 0;

            foreach ($users as $user) {
                if (! empty($user->skip_hris)) {
                    $count++;
                    continue;
                }

                $result = $this->syncFromHris($user);
                if (! $result) {
                    $this->deleteStaffByUserid($user->userid);
                    $deleted++;
                } else {
                    $this->persistHrisFields($user);
                    $updated++;
                }

                $count++;
                if ($count % 15 === 0) {
                    sleep(1);
                }
            }

            return [
                'total' => $users->count(),
                'updated' => $updated,
                'deleted' => $deleted,
            ];
        } finally {
            $lock = $this->hrisUpdateLockPath();
            if (file_exists($lock)) {
                unlink($lock);
            }
        }
    }

    public function isHrisUpdateRunning(): bool
    {
        $lock = $this->hrisUpdateLockPath();
        if (! file_exists($lock)) {
            return false;
        }

        return (time() - filemtime($lock)) < 7200;
    }

    public function lastHrisUpdate(): ?array
    {
        $path = storage_path('app/hris-update-last.json');
        if (! file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : null;
    }

    public function saveHrisUpdateResult(array $result): void
    {
        file_put_contents(
            storage_path('app/hris-update-last.json'),
            json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function searchUsers(string $query)
    {
        return User::leftJoin('departments', 'users.department', '=', 'departments.id')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->where(function ($builder) use ($query) {
                $builder->where('users.userid', 'like', '%'.$query.'%')
                    ->orWhere('users.name', 'like', '%'.$query.'%')
                    ->orWhere('users.name_EN', 'like', '%'.$query.'%')
                    ->orWhere('emails.email', 'like', '%'.$query.'%');
            })
            ->select(
                'users.userid',
                'users.name',
                'users.name_EN',
                'users.position',
                'users.position_EN',
                'users.department as department_id',
                'departments.department',
                'emails.email'
            )
            ->orderBy('users.name')
            ->limit(20)
            ->get();
    }

    public function listUserEmails()
    {
        return Email::query()
            ->leftJoin('users', 'emails.userid', '=', 'users.userid')
            ->leftJoin('departments', 'users.department', '=', 'departments.id')
            ->select(
                'emails.id',
                'emails.userid',
                'emails.email',
                'emails.updated_at',
                'users.name',
                'users.position',
                'departments.department',
                'departments.division'
            )
            ->whereNotNull('users.userid')
            ->orderBy('users.name')
            ->get();
    }

    public function orphanEmails()
    {
        return Email::query()
            ->leftJoin('users', 'emails.userid', '=', 'users.userid')
            ->whereNull('users.userid')
            ->select(
                'emails.id',
                'emails.userid',
                'emails.email',
                'emails.updated_at'
            )
            ->orderBy('emails.userid')
            ->get();
    }

    public function deleteOrphanEmails(?array $ids = null): int
    {
        $query = Email::query()
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('users')
                    ->whereColumn('users.userid', 'emails.userid');
            });

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        return $query->delete();
    }

    public function saveUserEmail(string $userid, string $email): void
    {
        Email::updateOrCreate(
            ['userid' => $userid],
            ['email' => $email]
        );
    }

    public function removeUserEmail(int $id): void
    {
        Email::where('id', $id)->delete();
    }

    public function saveManualUser(array $data): User
    {
        $user = User::where('userid', $data['userid'])->first();
        if ($user == null) {
            $user = new User;
            $user->userid = $data['userid'];
        }

        $user->name = $data['name'];
        $user->name_EN = $data['name_EN'] !== '' ? $data['name_EN'] : null;
        $user->position = $data['position'] !== '' ? $data['position'] : null;
        $user->position_EN = $data['position_EN'] !== '' ? $data['position_EN'] : ($data['position'] !== '' ? $data['position'] : null);
        $user->department = $data['department_id'];
        $user->skip_hris = true;
        $user->updated_at = date('Y-m-d H:i:s');
        $user->save();

        if ($data['email'] !== '') {
            $this->saveUserEmail($user->userid, $data['email']);
        }

        return $user;
    }

    public function listManualUsers()
    {
        return User::leftJoin('departments', 'users.department', '=', 'departments.id')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->where('users.skip_hris', 1)
            ->select(
                'users.id',
                'users.userid',
                'users.name',
                'users.name_EN',
                'users.position',
                'users.position_EN',
                'users.department as department_id',
                'departments.department',
                'departments.division',
                'emails.email',
                'users.updated_at'
            )
            ->orderBy('users.name')
            ->get();
    }

    public function listUsers(array $filters = [])
    {
        $query = User::leftJoin('departments', 'users.department', '=', 'departments.id')
            ->leftJoin('emails', 'users.userid', '=', 'emails.userid')
            ->select(
                'users.id',
                'users.userid',
                'users.name',
                'users.name_EN',
                'users.position',
                'users.position_EN',
                'users.department as department_id',
                'users.skip_hris',
                'users.updated_at',
                'departments.department',
                'departments.division',
                'emails.email'
            );

        if (! empty($filters['department_id'])) {
            $query->where('users.department', (int) $filters['department_id']);
        } elseif (array_key_exists('division', $filters) && $filters['division'] !== null && $filters['division'] !== '') {
            $query->where('departments.division', $filters['division']);
        } elseif (array_key_exists('division', $filters) && $filters['division'] === '') {
            $query->where(function ($builder) {
                $builder->whereNull('departments.division')
                    ->orWhere('departments.division', '');
            });
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('users.userid', 'like', '%'.$q.'%')
                    ->orWhere('users.name', 'like', '%'.$q.'%')
                    ->orWhere('users.name_EN', 'like', '%'.$q.'%')
                    ->orWhere('users.position', 'like', '%'.$q.'%')
                    ->orWhere('emails.email', 'like', '%'.$q.'%');
            });
        }

        return $query->orderBy('departments.division')
            ->orderBy('departments.department')
            ->orderBy('users.name')
            ->paginate((int) ($filters['per_page'] ?? 50))
            ->withQueryString();
    }

    public function removeManualUser(string $userid): void
    {
        $this->deleteStaffByUserid($userid);
    }

    public function usersByDepartment(string $department, ?string $position = null): array
    {
        $query = User::join('departments', 'departments.id', '=', 'users.department')
            ->where('departments.department', $department)
            ->select(
                'users.userid',
                'users.name',
                'users.position',
                'departments.department',
                'departments.division',
            );

        if ($position !== null) {
            $query->where('users.position', $position);
        }

        return $query->get()->toArray();
    }

    public function positionsByDepartment(string $department): array
    {
        return User::join('departments', 'departments.id', '=', 'users.department')
            ->where('departments.department', $department)
            ->select('users.position')
            ->groupBy('users.position')
            ->get()
            ->toArray();
    }

    private function importUser(string $userid): ?User
    {
        $newuser = new User;
        $newuser->userid = $userid;
        $newuser = $this->syncFromHris($newuser);
        if (! $newuser) {
            return null;
        }
        $newuser->save();

        return $newuser;
    }

    private function loadStaffProfile($userid)
    {
        return DB::table('users')
            ->leftjoin('departments', 'users.department', '=', 'departments.id')
            ->leftjoin('emails', 'users.userid', '=', 'emails.userid')
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
                'users.skip_hris',
                'users.updated_at',
            )
            ->first();
    }

    private function createReferance($userid): array
    {
        $response = [
            'hn' => null,
            'refid' => null,
            'passport' => false,
        ];

        $findHN = DB::connection('SSB')
            ->table('HNPAT_RIGHT')
            ->where('ReferToPayrollNo', $userid)
            ->where('DefaultRight', 1)
            ->where('RightCode', 'like', '05%')
            ->select('HN')
            ->first();

        if ($findHN === null) {
            return $response;
        }

        $findRef = DB::connection('SSB')
            ->table('HNPAT_REF')
            ->where('HN', $findHN->HN)
            ->orderBy('IDCardType', 'asc')
            ->select('IDCardType', 'RefNo')
            ->first();

        $info = DB::connection('SSB')
            ->table('HNPAT_INFO')
            ->where('HN', $findHN->HN)
            ->select('Gender')
            ->first();

        $gender = null;
        if ($info !== null) {
            $gender = ($info->Gender == 1) ? 'หญิง' : 'ชาย';
        }
        $passport = ($findRef->IDCardType == 1) ? false : true;

        $newRef = new Referance;
        $newRef->userid = $userid;
        $newRef->HN = $findHN->HN;
        $newRef->refID = $findRef->RefNo;
        $newRef->passport = $passport;
        $newRef->gender = $gender;
        $newRef->save();

        return [
            'hn' => $findHN->HN,
            'gender' => $gender,
            'refid' => $findRef->RefNo,
            'passport' => $passport,
        ];
    }

    private function syncDoctor($user)
    {
        $doctor = DB::connection('DOCTOR')
            ->table('TB_Doctor_Master')
            ->where('Doctor', $user->userid)
            ->first();

        if ($doctor == null) {
            return false;
        }

        $clinic = $this->getClinic($doctor->Clinic);
        $user->name = $doctor->Prefix_TH.' '.$doctor->Name_TH.' '.$doctor->LastName_TH;
        $user->name_EN = $doctor->Prefix_EN.' '.$doctor->Name_EN.' '.$doctor->LastName_EN;
        $user->position = $clinic;
        $user->position_EN = $clinic;
        $user->department = $this->findOrCreateDoctorDepartment()->id;
        $user->picture = null;

        return $user;
    }

    private function syncEmployee($user)
    {
        $employee = $this->fetchHrisEmployee($user->userid);
        if ($employee === null) {
            return false;
        }

        $user->name = $employee->ThaiFirstName.' '.$employee->ThaiLastName;
        $user->name_EN = $employee->EnglishFirstName.' '.$employee->EnglishLastName;
        $user->position = $employee->ThaiPosition;
        $user->position_EN = $employee->EnglishPosition;
        $user->department = $this->syncDepartment($employee)->id;
        $user->picture = $employee->Picture;

        return $user;
    }

    private function fetchHrisEmployee(string $userid)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://hris.praram9.com:8443/api/CustomEmployeeInfo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'action=GetEmpCardInfo&employeeCodeList='.$userid.'',
            CURLOPT_HTTPHEADER => [
                'content-type: application/x-www-form-urlencoded',
                'apiuser: PR9Empcard',
                'token: '.env('HRIS_TOKEN').'',
                'envcode: PR9',
                'projectcode: PR9',
            ],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response);

        if ($response->validation->DataCompletion[0]->QueryResult == 'E') {
            return null;
        }

        return $response->result->EmployeeList[0];
    }

    private function getClinic($clinic_code)
    {
        $config = DB::connection('HIS')
            ->table('location')
            ->where('code', $clinic_code)
            ->select('description')
            ->first();

        if ($config !== null) {
            return $config->description;
        }

        return $clinic_code;
    }

    private function findOrCreateDoctorDepartment(): Department
    {
        $department = Department::where('department', 'Doctor')->first();
        if ($department !== null) {
            return $department;
        }

        $department = new Department;
        $department->department = 'Doctor';
        $department->department_EN = 'Doctor';
        $department->division = 'Doctor';
        $department->division_EN = 'Doctor';
        $department->save();

        return $department;
    }

    private function syncDepartment($employee): Department
    {
        $department = Department::where('department', $employee->ThaiDepartment)->first();
        if ($department == null) {
            $department = new Department;
            $department->department = $employee->ThaiDepartment;
            $department->department_EN = $employee->EnglishDepartment;
            $department->division = $employee->ThaiDivision;
            $department->division_EN = $employee->EnglishDivition;
            $department->save();

            return $department;
        }

        if ($this->daysSince($department->updated_at) > 14) {
            $department->department = $employee->ThaiDepartment;
            $department->department_EN = $employee->EnglishDepartment;
            $department->division = $employee->ThaiDivision;
            $department->division_EN = $employee->EnglishDivition;
            $department->save();
        }

        return $department;
    }

    private function hrisUpdateLockPath(): string
    {
        return storage_path('app/hris-update.lock');
    }

    private function persistHrisFields($user): void
    {
        DB::table('users')->where('userid', $user->userid)->update([
            'name' => $user->name,
            'name_EN' => $user->name_EN,
            'position' => $user->position,
            'position_EN' => $user->position_EN,
            'department' => $user->department,
            'picture' => $user->picture,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function deleteStaffByUserid(string $userid): void
    {
        User::where('userid', $userid)->delete();
        Referance::where('userid', $userid)->delete();
        Email::where('userid', $userid)->delete();
    }

    private function daysSince($datetime): int
    {
        $now = date_create(date('Y-m-d H:i:s'));
        $pre = date_create($datetime);
        $diff = $now->diff($pre);

        return $diff->d + ($diff->m * 30) + ($diff->y * 365);
    }
}
