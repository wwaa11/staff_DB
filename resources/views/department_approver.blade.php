@extends('layouts.app')

@section('title', 'ผู้อนุมัติตามแผนก')
@section('heading', 'ผู้อนุมัติตามแผนก')
@section('subtitle', 'เลือกฝ่ายแล้วเลือกแผนก เพื่อดูและแก้ไขผู้อนุมัติทุกระดับ')

@section('content')
    <form method="GET" action="{{ url('/department-approver') }}" class="panel">
        <div class="panel-toolbar">
            <div>
                <h2 class="panel-title">เลือกแผนก</h2>
                <p class="mt-0.5 text-sm text-base-content/50">ค้นหาฝ่ายก่อน แล้วเลือกแผนก</p>
            </div>
        </div>
        <div class="panel-body">
            @include('partials.department_division_select', [
                'groupedDepartments' => $groupedDepartments,
                'selectedId' => $departmentId,
                'autoSubmit' => true,
            ])
            <div class="mt-5">
                <button type="submit" class="btn btn-primary">แสดง</button>
            </div>
        </div>
    </form>

    @if ($selected)
        <div class="panel">
            <div class="panel-toolbar">
                <div class="flex items-center gap-2">
                    <h2 class="panel-title">รายการผู้อนุมัติ · {{ $selected->department }}@if ($selected->division)
                            · {{ $selected->division }}
                        @endif
                    </h2>
                    <span class="badge badge-primary">{{ $approvers->count() }}</span>
                </div>
                <button type="button" class="btn btn-primary" id="open-add-modal">เพิ่มผู้อนุมัติ</button>
            </div>
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
                                <td>
                                    <span class="badge badge-primary">ระดับ {{ $row->level ?: '-' }}</span>
                                </td>
                                <td>
                                    <div class="font-semibold text-base-content">{{ $rowName ?: '-' }}</div>
                                    <div class="mt-0.5 text-xs text-base-content/50">{{ $row->userid }}@if ($rowPosition)
                                            · {{ $rowPosition }}
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $rowEmail ?: '-' }}</td>
                                <td>
                                    <div>{{ $row->updated_username ?: '-' }}</div>
                                    <div class="mt-0.5 text-xs text-base-content/50">{{ $row->updated_at }}</div>
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline btn-sm edit-btn"
                                            data-id="{{ $row->id }}" data-level="{{ $row->level }}"
                                            data-userid="{{ $row->userid }}" data-name="{{ $rowName }}"
                                            data-position="{{ $rowPosition }}" data-email="{{ $rowEmail }}">
                                            แก้ไข
                                        </button>
                                        <form method="POST"
                                            action="{{ url('/department-approver/'.$row->id.'/delete') }}"
                                            data-confirm="ลบผู้อนุมัติระดับ {{ $row->level }} ?">
                                            @csrf
                                            <input type="hidden" name="department_id" value="{{ $selected->id }}">
                                            <button type="submit" class="btn btn-error btn-outline btn-sm">ลบ</button>
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

        <dialog id="approver-modal" class="modal">
            <div class="modal-box max-w-2xl">
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" aria-label="ปิด">✕</button>
                </form>
                <h3 class="text-lg font-bold" id="modal-title">เพิ่มผู้อนุมัติ</h3>
                <p class="mt-1 text-sm text-base-content/50">{{ $selected->department }}@if ($selected->division)
                        · {{ $selected->division }}
                    @endif
                </p>

                <form method="POST" action="{{ url('/department-approver') }}" id="approver-form" class="mt-5">
                    @csrf
                    <input type="hidden" name="department_id" value="{{ $selected->id }}">
                    <input type="hidden" name="id" id="approver-id" value="">
                    <div class="grid gap-5 md:grid-cols-12">
                        <div class="md:col-span-3">
                            <label class="field-label" for="level">ระดับ</label>
                            <input type="number" class="input input-bordered w-full" id="level" name="level" min="1"
                                value="{{ old('level', $nextLevel) }}" required>
                        </div>
                        <div class="md:col-span-9">
                            <label class="field-label" for="approver-search">ผู้อนุมัติ</label>
                            <div class="relative z-30">
                                @include('partials.search_input', [
                                    'id' => 'approver-search',
                                    'placeholder' => 'ค้นหารหัสหรือชื่อผู้อนุมัติ',
                                ])
                                <input type="hidden" name="userid" id="userid" value="{{ old('userid') }}">
                                <div class="suggest-list" id="approver-suggest" role="listbox"></div>
                            </div>
                            <div class="preview" id="approver-preview">ยังไม่ได้เลือกผู้อนุมัติ</div>
                        </div>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-outline" id="close-modal-btn">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary" id="save-btn">บันทึก</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    @endif
@endsection

@push('scripts')
    @if ($selected)
        <script>
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const nextLevel = {{ (int) $nextLevel }};
            const modal = document.getElementById('approver-modal');
            const modalTitle = document.getElementById('modal-title');
            const shouldOpenModal = @json($errors->any() || old('userid') || old('level'));

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
            let lastItems = [];

            function hideSuggest() {
                suggest.style.display = 'none';
                suggest.innerHTML = '';
            }

            function selectItem(item) {
                hidden.value = item.userid;
                search.value = item.userid + ' - ' + item.name;
                if (window.AppSearch) {
                    AppSearch.syncClearButton(search);
                }
                preview.innerHTML = personHtml(item);
                hideSuggest();
            }

            function resetForm() {
                idInput.value = '';
                levelInput.value = nextLevel;
                hidden.value = '';
                search.value = '';
                if (window.AppSearch) {
                    AppSearch.syncClearButton(search);
                }
                preview.textContent = 'ยังไม่ได้เลือกผู้อนุมัติ';
                modalTitle.textContent = 'เพิ่มผู้อนุมัติ';
                document.getElementById('save-btn').disabled = false;
                document.getElementById('save-btn').textContent = 'บันทึก';
            }

            function openAddModal() {
                resetForm();
                AppModal.open(modal);
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
                if (!search.contains(e.target) && !suggest.contains(e.target) && !e.target.closest('.search-clear')) {
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
                    idInput.value = btn.dataset.id;
                    levelInput.value = btn.dataset.level || 1;
                    selectItem({
                        userid: btn.dataset.userid,
                        name: btn.dataset.name,
                        position: btn.dataset.position,
                        department: ''
                    });
                    modalTitle.textContent = 'แก้ไขผู้อนุมัติระดับ ' + btn.dataset.level;
                    document.getElementById('save-btn').textContent = 'อัปเดต';
                    AppModal.open(modal);
                });
            });

            document.getElementById('approver-form').addEventListener('submit', function(e) {
                const userid = hidden.value;
                const btn = document.getElementById('save-btn');
                if (!userid) {
                    e.preventDefault();
                    AppAlert.warning('กรุณาเลือกผู้อนุมัติจากรายการค้นหา');
                    return;
                }
                btn.disabled = true;
                btn.textContent = 'กำลังบันทึก...';
            });

            if (shouldOpenModal) {
                AppModal.open(modal);
            }
        </script>
    @endif
@endpush
