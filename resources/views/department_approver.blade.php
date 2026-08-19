@extends('layouts.app')

@section('title', 'ผู้อนุมัติตามแผนก')
@section('heading', 'ผู้อนุมัติตามแผนก')
@section('subtitle', 'เลือกฝ่ายแล้วเลือกแผนก เพื่อดูและแก้ไขผู้อนุมัติทุกระดับ')

@section('content')
    <form method="GET" action="{{ url('/department-approver') }}" class="card">
        @include('partials.department_division_select', [
            'groupedDepartments' => $groupedDepartments,
            'selectedId' => $departmentId,
            'autoSubmit' => true,
        ])
        <div class="mt-4">
            <button type="submit" class="btn-primary">แสดง</button>
        </div>
    </form>

    @if ($selected)
        <form method="POST" action="{{ url('/department-approver') }}" class="card" id="approver-form">
            @csrf
            <input type="hidden" name="department_id" value="{{ $selected->id }}">
            <input type="hidden" name="id" id="approver-id" value="">
            <h2 class="mb-4 text-base font-semibold text-slate-900" id="form-title">เพิ่มผู้อนุมัติ ·
                {{ $selected->department }}@if ($selected->division)
                    · {{ $selected->division }}
                @endif
            </h2>
            <div class="grid gap-4 md:grid-cols-12">
                <div class="md:col-span-3">
                    <label class="label" for="level">ระดับ</label>
                    <input type="number" class="input" id="level" name="level" min="1"
                        value="{{ old('level', $nextLevel) }}" required>
                </div>
                <div class="md:col-span-9">
                    <label class="label" for="approver-search">ผู้อนุมัติ</label>
                    <div class="relative">
                        <input type="text" class="input" id="approver-search" autocomplete="off"
                            placeholder="ค้นหารหัสหรือชื่อผู้อนุมัติ">
                        <input type="hidden" name="userid" id="userid" value="{{ old('userid') }}">
                        <div class="suggest-list" id="approver-suggest" role="listbox"></div>
                    </div>
                    <div class="preview" id="approver-preview">ยังไม่ได้เลือกผู้อนุมัติ</div>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit" class="btn-primary" id="save-btn">บันทึก</button>
                <button type="button" class="btn-secondary" id="reset-btn">ยกเลิกการแก้ไข</button>
            </div>
        </form>

        <div class="card">
            <h2 class="mb-4 text-base font-semibold text-slate-900">รายการผู้อนุมัติ ({{ $approvers->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ระดับ</th>
                            <th>ผู้อนุมัติ</th>
                            <th>อีเมล</th>
                            <th>อัปเดตโดย</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($approvers as $row)
                            @php
                                $rowName = optional($row->userData)->name;
                                $rowPosition = optional($row->userData)->position;
                                $rowEmail = optional($row->email)->email;
                            @endphp
                            <tr>
                                <td>{{ $row->level ?: '-' }}</td>
                                <td>
                                    <strong>{{ $rowName ?: '-' }}</strong><br>
                                    <small class="text-slate-500">{{ $row->userid }}@if ($rowPosition)
                                            · {{ $rowPosition }}
                                        @endif
                                    </small>
                                </td>
                                <td>{{ $rowEmail ?: '-' }}</td>
                                <td>
                                    {{ $row->updated_username ?: '-' }}<br>
                                    <small class="text-slate-500">{{ $row->updated_at }}</small>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="btn-secondary edit-btn" data-id="{{ $row->id }}"
                                            data-level="{{ $row->level }}" data-userid="{{ $row->userid }}"
                                            data-name="{{ $rowName }}" data-position="{{ $rowPosition }}"
                                            data-email="{{ $rowEmail }}">
                                            แก้ไข
                                        </button>
                                        <form method="POST"
                                            action="{{ url('/department-approver/'.$row->id.'/delete') }}"
                                            onsubmit="return confirm('ลบผู้อนุมัติระดับ {{ $row->level }} ?');">
                                            @csrf
                                            <input type="hidden" name="department_id" value="{{ $selected->id }}">
                                            <button type="submit" class="btn-danger">ลบ</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">ยังไม่มีผู้อนุมัติในแผนกนี้</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/department-select.js') }}"></script>
    @if ($selected)
        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const nextLevel = {{ (int) $nextLevel }};

            function debounce(fn, wait) {
                let t;
                return function() {
                    const args = arguments;
                    clearTimeout(t);
                    t = setTimeout(function() {
                        fn.apply(null, args);
                    }, wait);
                };
            }

            function personHtml(item) {
                if (!item) {
                    return '';
                }
                return '<strong>' + item.name + '</strong><br><small>' + item.userid + ' · ' + (item.position || '-') +
                    (item.department ? ' · ' + item.department : '') + '</small>';
            }

            const search = document.getElementById('approver-search');
            const hidden = document.getElementById('userid');
            const suggest = document.getElementById('approver-suggest');
            const preview = document.getElementById('approver-preview');
            const idInput = document.getElementById('approver-id');
            const levelInput = document.getElementById('level');
            const formTitle = document.getElementById('form-title');
            let lastItems = [];

            function hideSuggest() {
                suggest.style.display = 'none';
                suggest.innerHTML = '';
            }

            function selectItem(item) {
                hidden.value = item.userid;
                search.value = item.userid + ' - ' + item.name;
                preview.innerHTML = personHtml(item);
                hideSuggest();
            }

            function resetForm() {
                idInput.value = '';
                levelInput.value = nextLevel;
                hidden.value = '';
                search.value = '';
                preview.textContent = 'ยังไม่ได้เลือกผู้อนุมัติ';
                formTitle.textContent = 'เพิ่มผู้อนุมัติ · {{ $selected->department }}@if ($selected->division) · {{ $selected->division }}@endif';
                document.getElementById('save-btn').textContent = 'บันทึก';
            }

            const runSearch = debounce(function() {
                const q = search.value.trim();
                if (q.length < 1) {
                    hidden.value = '';
                    preview.textContent = 'ยังไม่ได้เลือกผู้อนุมัติ';
                    hideSuggest();
                    return;
                }
                fetch('{{ url('/department-approver/search') }}?q=' + encodeURIComponent(q), {
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    }
                }).then(function(res) {
                    return res.json();
                }).then(function(items) {
                    lastItems = items;
                    if (!items.length) {
                        suggest.innerHTML = '<div class="suggest-item">ไม่พบข้อมูล</div>';
                        suggest.style.display = 'block';
                        return;
                    }
                    suggest.innerHTML = items.map(function(item, index) {
                        return '<button type="button" class="suggest-item" data-index="' + index +
                            '" role="option">' + personHtml(item) + '</button>';
                    }).join('');
                    suggest.style.display = 'block';
                });
            }, 250);

            search.addEventListener('input', function() {
                hidden.value = '';
                runSearch();
            });

            suggest.addEventListener('click', function(e) {
                const btn = e.target.closest('.suggest-item');
                if (!btn || btn.dataset.index === undefined) {
                    return;
                }
                selectItem(lastItems[Number(btn.dataset.index)]);
            });

            document.addEventListener('click', function(e) {
                if (!search.contains(e.target) && !suggest.contains(e.target)) {
                    hideSuggest();
                }
            });

            document.querySelectorAll('.edit-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    idInput.value = btn.dataset.id;
                    levelInput.value = btn.dataset.level || 1;
                    hidden.value = btn.dataset.userid;
                    search.value = btn.dataset.userid + ' - ' + (btn.dataset.name || '');
                    preview.innerHTML = personHtml({
                        userid: btn.dataset.userid,
                        name: btn.dataset.name,
                        position: btn.dataset.position,
                        department: ''
                    });
                    formTitle.textContent = 'แก้ไขผู้อนุมัติระดับ ' + btn.dataset.level +
                        ' · {{ $selected->department }}@if ($selected->division) · {{ $selected->division }}@endif';
                    document.getElementById('save-btn').textContent = 'อัปเดต';
                    document.getElementById('approver-form').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            document.getElementById('reset-btn').addEventListener('click', resetForm);

            document.getElementById('approver-form').addEventListener('submit', function(e) {
                const userid = hidden.value;
                const btn = document.getElementById('save-btn');
                if (!userid) {
                    e.preventDefault();
                    alert('กรุณาเลือกผู้อนุมัติจากรายการค้นหา');
                    return;
                }
                btn.disabled = true;
                btn.textContent = 'กำลังบันทึก...';
            });
        </script>
    @endif
@endpush
