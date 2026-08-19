<?php

namespace App\Services;

use App\Models\Approver;
use App\Models\Department;
use Illuminate\Support\Collection;

class DepartmentService
{
    public function groupedForSelect(): array
    {
        $grouped = [];
        $rows = Department::orderBy('division')->orderBy('department')->get();

        foreach ($rows as $row) {
            $division = $row->division ?: '';
            if (! isset($grouped[$division])) {
                $grouped[$division] = [];
            }
            $grouped[$division][] = [
                'id' => $row->id,
                'department' => $row->department,
                'department_EN' => $row->department_EN,
            ];
        }

        return $grouped;
    }

    public function unused(): Collection
    {
        return Department::doesntHave('users')
            ->orderBy('department')
            ->orderBy('division')
            ->get();
    }

    public function deleteUnused(?array $ids = null): int
    {
        $query = Department::doesntHave('users');
        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        $departments = $query->get();
        $deleted = 0;

        foreach ($departments as $department) {
            Approver::where('department_id', $department->id)->delete();
            $department->delete();
            $deleted++;
        }

        return $deleted;
    }
}
