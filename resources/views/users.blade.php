@extends('layouts.app')

@section('title', 'รายชื่อพนักงาน')
@section('heading', 'รายชื่อพนักงาน')
@section('subtitle', 'ดูพนักงานทั้งหมด และกรองตามฝ่าย แผนก หรือค้นหาชื่อ')

@section('content')
    <form method="GET" action="{{ url('/users') }}" class="panel" id="filter-form">
        <div class="panel-toolbar">
            <div>
                <h2 class="panel-title">ตัวกรอง</h2>
                <p class="mt-0.5 text-sm text-base-content/50">เลือกฝ่าย/แผนก หรือค้นหาจากรหัส ชื่อ อีเมล</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ url('/users') }}" class="btn btn-ghost btn-sm">ล้างตัวกรอง</a>
                <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
            </div>
        </div>
        <div class="panel-body space-y-4">
            @include('partials.department_division_select', [
                'groupedDepartments' => $groupedDepartments,
                'selectedId' => $departmentId,
                'selectedDivision' => $selectedDivision ?? '',
                'hasDivisionFilter' => $selectedDivision !== null,
                'autoSubmit' => false,
                'allowEmptyDepartment' => true,
                'emptyDepartmentLabel' => 'ทุกแผนกในฝ่ายนี้',
            ])
            <div>
                <label class="field-label" for="q">ค้นหา</label>
                @include('partials.search_input', [
                    'id' => 'q',
                    'name' => 'q',
                    'value' => $q,
                    'placeholder' => 'รหัสพนักงาน ชื่อ ตำแหน่ง หรืออีเมล',
                    'attrs' => 'aria-label="ค้นหาพนักงาน"',
                ])
            </div>
        </div>
    </form>

    <div class="panel">
        <div class="panel-toolbar">
            <div class="flex items-center gap-2">
                <h2 class="panel-title">ผลการค้นหา</h2>
                <span class="badge badge-primary">{{ number_format($users->total()) }}</span>
            </div>
            <div class="text-sm text-base-content/50">
                หน้า {{ $users->currentPage() }} / {{ max($users->lastPage(), 1) }}
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="users-table">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ</th>
                        <th>ตำแหน่ง</th>
                        <th>ฝ่าย</th>
                        <th>แผนก</th>
                        <th>อีเมล</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $row)
                        <tr>
                            <td class="font-medium whitespace-nowrap">{{ $row->userid }}</td>
                            <td>
                                <div class="font-semibold text-base-content">{{ $row->name ?: '-' }}</div>
                                <div class="mt-0.5 text-xs text-base-content/50">{{ $row->name_EN ?: '-' }}</div>
                            </td>
                            <td>{{ $row->position ?: '-' }}</td>
                            <td>{{ $row->division ?: '-' }}</td>
                            <td>{{ $row->department ?: '-' }}</td>
                            <td>{{ $row->email ?: '-' }}</td>
                            <td>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" class="btn btn-outline btn-sm edit-email-btn"
                                        data-userid="{{ $row->userid }}" data-name="{{ $row->name }}"
                                        data-position="{{ $row->position }}" data-department="{{ $row->department }}"
                                        data-email="{{ $row->email }}">
                                        แก้ไขอีเมล
                                    </button>
                                    @if (! empty($row->skip_hris))
                                        <span class="badge badge-ghost badge-sm">สร้างเอง</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">ไม่พบพนักงานตามเงื่อนไขที่เลือก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-base-300 px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <dialog id="email-modal" class="modal">
        <div class="modal-box max-w-lg">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" aria-label="ปิด">✕</button>
            </form>
            <h3 class="text-lg font-bold">แก้ไขอีเมลพนักงาน</h3>
            <p class="mt-1 text-sm text-base-content/50" id="email-user-label">เลือกพนักงานจากตาราง</p>

            <form method="POST" action="{{ url('/users/email') }}" id="email-form" class="mt-5">
                @csrf
                <input type="hidden" name="userid" id="email-userid" value="{{ old('userid') }}">
                <div>
                    <label class="field-label" for="email">อีเมล</label>
                    <input type="email" class="input input-bordered w-full" id="email" name="email"
                        value="{{ old('email') }}" placeholder="name@example.com" required>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline" id="close-email-modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" id="save-email-btn">บันทึก</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        const modal = document.getElementById('email-modal');
        const useridInput = document.getElementById('email-userid');
        const emailInput = document.getElementById('email');
        const userLabel = document.getElementById('email-user-label');
        const shouldOpenModal = @json($errors->any() || old('userid') || old('email'));

        function openEmailModal(data) {
            useridInput.value = data.userid || '';
            emailInput.value = data.email || '';
            const parts = [data.name || data.userid, data.userid, data.position, data.department].filter(Boolean);
            userLabel.textContent = parts.join(' · ');
            AppModal.open(modal);
            emailInput.focus();
        }

        document.querySelectorAll('.edit-email-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openEmailModal(btn.dataset);
            });
        });

        document.getElementById('close-email-modal').addEventListener('click', function() {
            AppModal.close(modal);
        });

        document.getElementById('email-form').addEventListener('submit', function(e) {
            const btn = document.getElementById('save-email-btn');
            if (!useridInput.value) {
                e.preventDefault();
                AppAlert.warning('ไม่พบรหัสพนักงาน');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });

        if (shouldOpenModal) {
            AppModal.open(modal);
        }
    </script>
@endpush
