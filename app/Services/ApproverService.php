<?php

namespace App\Services;

use App\Models\Approver;
use App\Models\Department;
use App\Models\Email;
use App\Models\User;
use App\Models\UserApprover;

class ApproverService
{
    public function findForUser(string $userid): ?array
    {
        $user = User::where('userid', $userid)->first();
        if ($user == null) {
            return null;
        }

        $userApprover = UserApprover::where('userid', $user->userid)->first();
        if ($userApprover !== null) {
            $customApprover = $this->payloadFromUserid($userApprover->approver_userid, 1);
            if ($customApprover !== null) {
                return $this->keyedApprovers([$customApprover]);
            }
        }

        $payloads = $this->payloadsByDepartmentId((int) $user->getAttribute('department'));
        if ($payloads === []) {
            return [];
        }

        return $this->keyedApprovers($payloads);
    }

    public function findForDepartment(string $departmentName): ?array
    {
        $departmentIds = Department::where('department', $departmentName)->pluck('id');
        if ($departmentIds->isEmpty()) {
            return null;
        }

        $payloads = $this->payloadsByDepartmentIds($departmentIds->all());
        if ($payloads === []) {
            return null;
        }

        return $this->keyedApprovers($payloads);
    }

    public function listMappings()
    {
        return UserApprover::query()
            ->leftJoin('users as u', 'user_approvers.userid', '=', 'u.userid')
            ->leftJoin('users as a', 'user_approvers.approver_userid', '=', 'a.userid')
            ->leftJoin('departments as ud', 'u.department', '=', 'ud.id')
            ->leftJoin('departments as ad', 'a.department', '=', 'ad.id')
            ->select(
                'user_approvers.id',
                'user_approvers.userid',
                'u.name as user_name',
                'u.position as user_position',
                'ud.department as user_department',
                'user_approvers.approver_userid',
                'a.name as approver_name',
                'a.position as approver_position',
                'ad.department as approver_department',
                'user_approvers.updated_at'
            )
            ->orderBy('u.name')
            ->get();
    }

    public function assign(string $userid, string $approverUserid): void
    {
        UserApprover::updateOrCreate(
            ['userid' => $userid],
            ['approver_userid' => $approverUserid]
        );
    }

    public function remove(int $id): void
    {
        UserApprover::where('id', $id)->delete();
    }

    public function listDepartments()
    {
        return Department::orderBy('department')->orderBy('division')->get();
    }

    public function listByDepartment(int $departmentId)
    {
        return Approver::with(['userData', 'email'])
            ->where('department_id', $departmentId)
            ->orderBy('level')
            ->orderBy('id')
            ->get();
    }

    public function nextLevel(int $departmentId): int
    {
        $max = Approver::where('department_id', $departmentId)->max('level');

        return ((int) $max) + 1;
    }

    public function saveDepartmentLevel(array $data, $actor): ?Approver
    {
        $user = User::where('userid', $data['userid'])->first();
        if ($user == null) {
            return null;
        }

        $approver = null;
        if (! empty($data['id'])) {
            $approver = Approver::where('id', $data['id'])
                ->where('department_id', $data['department_id'])
                ->first();
        }
        if ($approver == null) {
            $approver = new Approver;
            $approver->department_id = $data['department_id'];
        }

        $approver->userid = $user->userid;
        $approver->level = (int) $data['level'];
        $approver->updated_userid = $actor->userid ?? null;
        $approver->updated_username = $actor->name ?? null;
        $approver->save();

        return $approver;
    }

    public function removeDepartmentLevel(int $id): ?Approver
    {
        $approver = Approver::find($id);
        if ($approver == null) {
            return null;
        }
        $approver->delete();

        return $approver;
    }

    public function incompleteDepartmentApprovers()
    {
        return Approver::query()
            ->leftJoin('departments', 'approvers.department_id', '=', 'departments.id')
            ->leftJoin('users', 'approvers.userid', '=', 'users.userid')
            ->where(function ($query) {
                $query->whereNull('departments.id')
                    ->orWhereNull('departments.department')
                    ->orWhere('departments.department', '')
                    ->orWhereNull('departments.division')
                    ->orWhere('departments.division', '');
            })
            ->select(
                'approvers.id',
                'approvers.userid',
                'approvers.level',
                'approvers.department_id',
                'approvers.updated_at',
                'users.name',
                'users.position',
                'departments.department',
                'departments.division'
            )
            ->orderBy('approvers.id')
            ->get();
    }

    public function deleteIncompleteDepartmentApprovers(?array $ids = null): int
    {
        $query = Approver::query()
            ->leftJoin('departments', 'approvers.department_id', '=', 'departments.id')
            ->where(function ($builder) {
                $builder->whereNull('departments.id')
                    ->orWhereNull('departments.department')
                    ->orWhere('departments.department', '')
                    ->orWhereNull('departments.division')
                    ->orWhere('departments.division', '');
            });

        if ($ids !== null) {
            $query->whereIn('approvers.id', $ids);
        }

        $idsToDelete = $query->pluck('approvers.id');
        if ($idsToDelete->isEmpty()) {
            return 0;
        }

        return Approver::whereIn('id', $idsToDelete)->delete();
    }

    private function payloadsByDepartmentId(int $departmentId): array
    {
        return $this->payloadsByDepartmentIds([$departmentId]);
    }

    private function payloadsByDepartmentIds(array $departmentIds): array
    {
        $approvers = Approver::with(['userData', 'email'])
            ->whereIn('department_id', $departmentIds)
            ->orderBy('level')
            ->orderBy('id')
            ->get();

        $payloads = [];
        foreach ($approvers as $approver) {
            $payload = $this->payloadFromUserid((string) $approver->userid, (int) $approver->level);
            if ($payload !== null) {
                $payloads[] = $payload;
            }
        }

        return $payloads;
    }

    private function keyedApprovers(array $payloads): array
    {
        $response = [];
        foreach (array_values($payloads) as $index => $payload) {
            $key = $index === 0 ? 'approver' : 'approver_'.($index + 1);
            $response[$key] = $payload;
        }

        return $response;
    }

    private function payloadFromUserid(string $approverUserid, ?int $level = null): ?array
    {
        $approverUser = User::where('userid', $approverUserid)->first();
        if ($approverUser == null) {
            return null;
        }

        $email = Email::where('userid', $approverUserid)->first();

        return $this->payloadFromArray([
            'userid' => $approverUser->userid,
            'name' => $approverUser->name,
            'name_EN' => $approverUser->name_EN,
            'position' => $approverUser->position,
            'position_EN' => $approverUser->position_EN,
            'email' => $email !== null ? $email->email : null,
            'level' => $level,
        ]);
    }

    private function payloadFromArray(array $data): array
    {
        return [
            'userid' => $data['userid'] ?? null,
            'name' => $data['name'] ?? null,
            'name_EN' => $data['name_EN'] ?? null,
            'position' => $data['position'] ?? null,
            'position_EN' => $data['position_EN'] ?? null,
            'email' => $data['email'] ?? null,
            'level' => $data['level'] ?? null,
        ];
    }
}
