@extends('layouts.app')

@section('title', 'เพิ่มพนักงาน')
@section('heading', 'เพิ่ม / แก้ไขพนักงานที่ไม่มีใน HRIS')
@section('subtitle', 'ใช้เมื่อดึงข้อมูลจาก HRIS ไม่เจอ จะไม่ถูกลบตอนอัปเดต HRIS')

@section('content')
    <div class="panel">
        <div class="panel-toolbar">
            <div class="flex items-center gap-2">
                <h2 class="panel-title">พนักงานที่สร้างเอง</h2>
                <span class="badge badge-primary">{{ $users->count() }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('partials.search_input', [
                    'id' => 'table-filter',
                    'placeholder' => 'ค้นหาในตาราง',
                    'wrapperClass' => 'w-full max-w-xs',
                    'attrs' => 'aria-label="ค้นหาในตาราง"',
                ])
                <button type="button" class="btn btn-primary" id="open-add-modal">เพิ่มพนักงาน</button>
            </div>
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
                            <td class="font-medium">{{ $row->userid }}</td>
                            <td>
                                <div class="font-semibold text-base-content">{{ $row->name ?: '-' }}</div>
                                <div class="mt-0.5 text-xs text-base-content/50">{{ $row->name_EN ?: '-' }}</div>
                            </td>
                            <td>{{ $row->position ?: '-' }}</td>
                            <td>{{ $row->department ?: '-' }}@if ($row->division)
                                    · {{ $row->division }}
                                @endif
                            </td>
                            <td>{{ $row->email ?: '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline btn-sm edit-btn"
                                        data-userid="{{ $row->userid }}" data-name="{{ $row->name }}"
                                        data-name-en="{{ $row->name_EN }}" data-position="{{ $row->position }}"
                                        data-position-en="{{ $row->position_EN }}"
                                        data-department-id="{{ $row->department_id }}" data-email="{{ $row->email }}">
                                        แก้ไข
                                    </button>
                                    <form method="POST" action="{{ url('/user-manual/'.$row->id.'/delete') }}"
                                        data-confirm="ลบพนักงาน {{ $row->userid }} ?">
                                        @csrf
                                        <button type="submit" class="btn btn-error btn-outline btn-sm">ลบ</button>
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

    <dialog id="user-modal" class="modal">
        <div class="modal-box max-w-4xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" aria-label="ปิด">✕</button>
            </form>
            <h3 class="text-lg font-bold" id="modal-title">เพิ่มพนักงาน</h3>
            <p class="mt-1 text-sm text-base-content/50">พิมพ์รหัสใหม่ หรือค้นหารหัสเดิมเพื่อแก้ไข</p>

            <form method="POST" action="{{ url('/user-manual') }}" id="user-form" class="mt-5">
                @csrf
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="field-label" for="userid">รหัสพนักงาน</label>
                        <div class="relative z-30">
                            @include('partials.search_input', [
                                'id' => 'userid',
                                'name' => 'userid',
                                'type' => 'text',
                                'placeholder' => 'ค้นหาหรือพิมพ์รหัสใหม่',
                                'value' => old('userid'),
                                'attrs' => 'required',
                            ])
                            <div class="suggest-list" id="user-suggest" role="listbox"></div>
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="name">ชื่อ - นามสกุล (ไทย)</label>
                        <input type="text" class="input input-bordered w-full" id="name" name="name"
                            value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label class="field-label" for="name_EN">ชื่อ - นามสกุล (อังกฤษ)</label>
                        <input type="text" class="input input-bordered w-full" id="name_EN" name="name_EN"
                            value="{{ old('name_EN') }}">
                    </div>
                    <div>
                        <label class="field-label" for="position">ตำแหน่ง (ไทย)</label>
                        <input type="text" class="input input-bordered w-full" id="position" name="position"
                            value="{{ old('position') }}">
                    </div>
                    <div>
                        <label class="field-label" for="position_EN">ตำแหน่ง (อังกฤษ)</label>
                        <input type="text" class="input input-bordered w-full" id="position_EN" name="position_EN"
                            value="{{ old('position_EN') }}">
                    </div>
                    <div>
                        <label class="field-label" for="email">อีเมล</label>
                        <input type="email" class="input input-bordered w-full" id="email" name="email"
                            value="{{ old('email') }}">
                    </div>
                </div>
                <div class="mt-5">
                    @include('partials.department_division_select', [
                        'groupedDepartments' => $groupedDepartments,
                        'selectedId' => old('department_id'),
                        'autoSubmit' => false,
                    ])
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline" id="close-modal-btn">ยกเลิก</button>
                    <button type="reset" class="btn btn-ghost" id="reset-btn">ล้างฟอร์ม</button>
                    <button type="submit" class="btn btn-primary" id="save-btn">บันทึก</button>
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
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const modal = document.getElementById('user-modal');
        const modalTitle = document.getElementById('modal-title');
        const useridInput = document.getElementById('userid');
        const suggest = document.getElementById('user-suggest');
        const shouldOpenModal = @json($errors->any() || old('userid') || old('name'));
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
            if (window.AppSearch) {
                AppSearch.syncClearButton(useridInput);
            }
            document.getElementById('name').value = item.name || '';
            document.getElementById('name_EN').value = item.name_EN || '';
            document.getElementById('position').value = item.position || '';
            document.getElementById('position_EN').value = item.position_EN || '';
            document.getElementById('email').value = item.email || '';
            if (item.department_id && typeof window.setDepartmentDivisionSelection === 'function') {
                window.setDepartmentDivisionSelection(item.department_id);
            }
        }

        function resetForm() {
            document.getElementById('user-form').reset();
            modalTitle.textContent = 'เพิ่มพนักงาน';
            document.getElementById('save-btn').disabled = false;
            document.getElementById('save-btn').textContent = 'บันทึก';
            hideSuggest();
            setTimeout(function() {
                if (window.AppSearch) {
                    AppSearch.syncClearButton(useridInput);
                    var divisionSearch = document.getElementById('division-search');
                    if (divisionSearch) {
                        AppSearch.syncClearButton(divisionSearch);
                    }
                }
                var dept = document.querySelector('[data-department-select]');
                if (dept) {
                    dept.innerHTML = '<option value="">เลือกแผนก</option>';
                }
            }, 0);
        }

        function openAddModal() {
            resetForm();
            AppModal.open(modal);
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
            if (!useridInput.contains(e.target) && !suggest.contains(e.target) && !e.target.closest('.search-clear')) {
                hideSuggest();
            }
        });

        document.getElementById('open-add-modal').addEventListener('click', openAddModal);
        document.getElementById('close-modal-btn').addEventListener('click', function() {
            AppModal.close(modal);
        });

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                resetForm();
                modalTitle.textContent = 'แก้ไขพนักงาน';
                fillForm({
                    userid: btn.dataset.userid,
                    name: btn.dataset.name,
                    name_EN: btn.dataset.nameEn,
                    position: btn.dataset.position,
                    position_EN: btn.dataset.positionEn,
                    department_id: btn.dataset.departmentId,
                    email: btn.dataset.email
                });
                AppModal.open(modal);
            });
        });

        document.getElementById('user-form').addEventListener('submit', function(e) {
            var dept = document.querySelector('[data-department-select]');
            if (!dept || !dept.value) {
                e.preventDefault();
                AppAlert.warning('กรุณาเลือกฝ่ายและแผนก');
                return;
            }
            var btn = document.getElementById('save-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });

        document.getElementById('user-form').addEventListener('reset', function() {
            setTimeout(function() {
                if (window.AppSearch) {
                    AppSearch.syncClearButton(useridInput);
                    var divisionSearch = document.getElementById('division-search');
                    if (divisionSearch) {
                        AppSearch.syncClearButton(divisionSearch);
                    }
                }
            }, 0);
        });

        document.getElementById('table-filter').addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#user-table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });

        if (shouldOpenModal) {
            AppModal.open(modal);
        }
    </script>
@endpush
