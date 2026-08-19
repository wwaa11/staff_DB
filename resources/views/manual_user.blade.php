@extends('layouts.app')

@section('title', 'เพิ่มพนักงาน')
@section('heading', 'เพิ่ม / แก้ไขพนักงานที่ไม่มีใน HRIS')
@section('subtitle', 'ใช้เมื่อดึงข้อมูลจาก HRIS ไม่เจอ จะไม่ถูกลบตอนอัปเดต HRIS')

@section('content')
    <form method="POST" action="{{ url('/user-manual') }}" class="card" id="user-form">
        @csrf
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="label" for="userid">รหัสพนักงาน</label>
                <div class="relative">
                    <input type="text" class="input" id="userid" name="userid" value="{{ old('userid') }}"
                        placeholder="ค้นหาหรือพิมพ์รหัสใหม่" autocomplete="off" required>
                    <div class="suggest-list" id="user-suggest" role="listbox"></div>
                </div>
            </div>
            <div>
                <label class="label" for="name">ชื่อ - นามสกุล (ไทย)</label>
                <input type="text" class="input" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label class="label" for="name_EN">ชื่อ - นามสกุล (อังกฤษ)</label>
                <input type="text" class="input" id="name_EN" name="name_EN" value="{{ old('name_EN') }}">
            </div>
            <div>
                <label class="label" for="position">ตำแหน่ง (ไทย)</label>
                <input type="text" class="input" id="position" name="position" value="{{ old('position') }}">
            </div>
            <div>
                <label class="label" for="position_EN">ตำแหน่ง (อังกฤษ)</label>
                <input type="text" class="input" id="position_EN" name="position_EN"
                    value="{{ old('position_EN') }}">
            </div>
            <div>
                <label class="label" for="email">อีเมล</label>
                <input type="email" class="input" id="email" name="email" value="{{ old('email') }}">
            </div>
        </div>
        <div class="mt-4">
            @include('partials.department_division_select', [
                'groupedDepartments' => $groupedDepartments,
                'selectedId' => old('department_id'),
                'autoSubmit' => false,
            ])
        </div>
        <div class="mt-4 flex flex-wrap gap-2">
            <button type="submit" class="btn-primary" id="save-btn">บันทึก</button>
            <button type="reset" class="btn-secondary" id="reset-btn">ล้างฟอร์ม</button>
        </div>
    </form>

    <div class="card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900">พนักงานที่สร้างเอง ({{ $users->count() }})</h2>
            <input type="search" class="input max-w-xs" id="table-filter" placeholder="ค้นหาในตาราง"
                aria-label="ค้นหาในตาราง">
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="user-table">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ</th>
                        <th>ตำแหน่ง</th>
                        <th>แผนก / ฝ่าย</th>
                        <th>อีเมล</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $row)
                        <tr>
                            <td>{{ $row->userid }}</td>
                            <td>
                                <strong>{{ $row->name ?: '-' }}</strong><br>
                                <small class="text-slate-500">{{ $row->name_EN ?: '-' }}</small>
                            </td>
                            <td>{{ $row->position ?: '-' }}</td>
                            <td>{{ $row->department ?: '-' }}@if ($row->division)
                                    · {{ $row->division }}
                                @endif
                            </td>
                            <td>{{ $row->email ?: '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn-secondary edit-btn"
                                        data-userid="{{ $row->userid }}" data-name="{{ $row->name }}"
                                        data-name-en="{{ $row->name_EN }}" data-position="{{ $row->position }}"
                                        data-position-en="{{ $row->position_EN }}"
                                        data-department-id="{{ $row->department_id }}" data-email="{{ $row->email }}">
                                        แก้ไข
                                    </button>
                                    <form method="POST" action="{{ url('/user-manual/'.$row->id.'/delete') }}"
                                        onsubmit="return confirm('ลบพนักงาน {{ $row->userid }} ?');">
                                        @csrf
                                        <button type="submit" class="btn-danger">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">ยังไม่มีพนักงานที่สร้างเอง</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/department-select.js') }}"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const useridInput = document.getElementById('userid');
        const suggest = document.getElementById('user-suggest');
        let lastItems = [];

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

        function fillForm(item) {
            useridInput.value = item.userid || '';
            document.getElementById('name').value = item.name || '';
            document.getElementById('name_EN').value = item.name_EN || '';
            document.getElementById('position').value = item.position || '';
            document.getElementById('position_EN').value = item.position_EN || '';
            document.getElementById('email').value = item.email || '';
            if (item.department_id && typeof window.setDepartmentDivisionSelection === 'function') {
                window.setDepartmentDivisionSelection(item.department_id);
            }
        }

        function hideSuggest() {
            suggest.style.display = 'none';
            suggest.innerHTML = '';
        }

        const runSearch = debounce(function() {
            const q = useridInput.value.trim();
            if (q.length < 1) {
                hideSuggest();
                return;
            }
            fetch('{{ url('/user-manual/search') }}?q=' + encodeURIComponent(q), {
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            }).then(function(res) {
                return res.json();
            }).then(function(items) {
                lastItems = items;
                if (!items.length) {
                    hideSuggest();
                    return;
                }
                suggest.innerHTML = items.map(function(item, index) {
                    return '<button type="button" class="suggest-item" data-index="' + index +
                        '" role="option"><strong>' + (item.name || item.userid) + '</strong><br><small>' +
                        item.userid + (item.department ? ' · ' + item.department : '') + '</small></button>';
                }).join('');
                suggest.style.display = 'block';
            });
        }, 250);

        useridInput.addEventListener('input', runSearch);
        useridInput.addEventListener('focus', function() {
            if (useridInput.value.trim()) {
                runSearch();
            }
        });

        suggest.addEventListener('click', function(e) {
            var btn = e.target.closest('.suggest-item');
            if (!btn || btn.dataset.index === undefined) {
                return;
            }
            fillForm(lastItems[Number(btn.dataset.index)]);
            hideSuggest();
        });

        document.addEventListener('click', function(e) {
            if (!useridInput.contains(e.target) && !suggest.contains(e.target)) {
                hideSuggest();
            }
        });

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                fillForm({
                    userid: btn.dataset.userid,
                    name: btn.dataset.name,
                    name_EN: btn.dataset.nameEn,
                    position: btn.dataset.position,
                    position_EN: btn.dataset.positionEn,
                    department_id: btn.dataset.departmentId,
                    email: btn.dataset.email
                });
                document.getElementById('user-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        document.getElementById('user-form').addEventListener('submit', function(e) {
            var dept = document.querySelector('[data-department-select]');
            if (!dept || !dept.value) {
                e.preventDefault();
                alert('กรุณาเลือกฝ่ายและแผนก');
                return;
            }
            var btn = document.getElementById('save-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });

        document.getElementById('table-filter').addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#user-table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });
    </script>
@endpush
