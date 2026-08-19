@extends('layouts.app')

@section('title', 'กำหนดผู้อนุมัติเฉพาะบุคคล')
@section('heading', 'กำหนดผู้อนุมัติเฉพาะบุคคล')
@section('subtitle', 'ถ้าพนักงานมีผู้อนุมัติที่ตั้งไว้ที่นี่ API getApprover จะส่งผู้อนุมัติคนนี้แทนผู้อนุมัติตามแผนก')

@section('content')
    <div class="panel">
        <div class="panel-toolbar">
            <div class="flex items-center gap-2">
                <h2 class="panel-title">รายการที่กำหนดแล้ว</h2>
                <span class="badge badge-primary">{{ $mappings->count() }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('partials.search_input', [
                    'id' => 'table-filter',
                    'placeholder' => 'ค้นหาในตาราง',
                    'wrapperClass' => 'w-full max-w-xs',
                    'attrs' => 'aria-label="ค้นหาในตาราง"',
                ])
                <button type="button" class="btn btn-primary" id="open-add-modal">
                    เพิ่มผู้อนุมัติ
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="mapping-table">
                <thead>
                    <tr>
                        <th>พนักงาน</th>
                        <th>แผนก</th>
                        <th>ผู้อนุมัติ</th>
                        <th>แผนกผู้อนุมัติ</th>
                        <th>อัปเดต</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mappings as $row)
                        <tr>
                            <td>
                                <div class="font-semibold text-base-content">{{ $row->user_name ?: '-' }}</div>
                                <div class="mt-0.5 text-xs text-base-content/50">{{ $row->userid }} ·
                                    {{ $row->user_position ?: '-' }}</div>
                            </td>
                            <td>{{ $row->user_department ?: '-' }}</td>
                            <td>
                                <div class="font-semibold text-base-content">{{ $row->approver_name ?: '-' }}</div>
                                <div class="mt-0.5 text-xs text-base-content/50">{{ $row->approver_userid }} ·
                                    {{ $row->approver_position ?: '-' }}</div>
                            </td>
                            <td>{{ $row->approver_department ?: '-' }}</td>
                            <td class="whitespace-nowrap text-base-content/50">{{ $row->updated_at }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline btn-sm edit-btn"
                                        data-userid="{{ $row->userid }}" data-user-name="{{ $row->user_name }}"
                                        data-user-position="{{ $row->user_position }}"
                                        data-user-department="{{ $row->user_department }}"
                                        data-approver-userid="{{ $row->approver_userid }}"
                                        data-approver-name="{{ $row->approver_name }}"
                                        data-approver-position="{{ $row->approver_position }}"
                                        data-approver-department="{{ $row->approver_department }}">
                                        แก้ไข
                                    </button>
                                    <form method="POST" action="{{ url('/user-approver/'.$row->id.'/delete') }}"
                                        data-confirm="ลบการกำหนดผู้อนุมัติของ {{ $row->user_name }} ?">
                                        @csrf
                                        <button type="submit" class="btn btn-error btn-outline btn-sm">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">ยังไม่มีรายการกำหนดผู้อนุมัติเฉพาะบุคคล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <dialog id="approver-modal" class="modal">
        <div class="modal-box max-w-3xl">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" aria-label="ปิด">✕</button>
            </form>
            <h3 class="text-lg font-bold" id="modal-title">เพิ่มผู้อนุมัติเฉพาะบุคคล</h3>
            <p class="mt-1 text-sm text-base-content/50">ค้นหาแล้วเลือกจากรายการเท่านั้น</p>

            <form method="POST" action="{{ url('/user-approver') }}" id="approver-form" class="mt-5">
                @csrf
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="field-label" for="user-search">พนักงาน</label>
                        <div class="relative z-30">
                            @include('partials.search_input', [
                                'id' => 'user-search',
                                'placeholder' => 'ค้นหารหัสหรือชื่อพนักงาน',
                                'attrs' => 'aria-describedby="user-preview"',
                            ])
                            <input type="hidden" name="userid" id="userid" value="{{ old('userid') }}">
                            <div class="suggest-list" id="user-suggest" role="listbox"></div>
                        </div>
                        <div class="preview" id="user-preview">ยังไม่ได้เลือกพนักงาน</div>
                    </div>
                    <div>
                        <label class="field-label" for="approver-search">ผู้อนุมัติ</label>
                        <div class="relative z-30">
                            @include('partials.search_input', [
                                'id' => 'approver-search',
                                'placeholder' => 'ค้นหารหัสหรือชื่อผู้อนุมัติ',
                                'attrs' => 'aria-describedby="approver-preview"',
                            ])
                            <input type="hidden" name="approver_userid" id="approver_userid"
                                value="{{ old('approver_userid') }}">
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
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const modal = document.getElementById('approver-modal');
        const modalTitle = document.getElementById('modal-title');
        const shouldOpenModal = @json($errors->any() || old('userid') || old('approver_userid'));

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

        function resetForm() {
            document.getElementById('userid').value = '';
            document.getElementById('approver_userid').value = '';
            document.getElementById('user-search').value = '';
            document.getElementById('approver-search').value = '';
            document.getElementById('user-preview').textContent = 'ยังไม่ได้เลือกพนักงาน';
            document.getElementById('approver-preview').textContent = 'ยังไม่ได้เลือกผู้อนุมัติ';
            document.getElementById('save-btn').disabled = false;
            document.getElementById('save-btn').textContent = 'บันทึก';
            modalTitle.textContent = 'เพิ่มผู้อนุมัติเฉพาะบุคคล';
            if (window.AppSearch) {
                AppSearch.syncClearButton(document.getElementById('user-search'));
                AppSearch.syncClearButton(document.getElementById('approver-search'));
            }
        }

        function openAddModal() {
            resetForm();
            AppModal.open(modal);
        }

        function bindSearch(searchId, hiddenId, suggestId, previewId, emptyText) {
            const search = document.getElementById(searchId);
            const hidden = document.getElementById(hiddenId);
            const suggest = document.getElementById(suggestId);
            const preview = document.getElementById(previewId);
            let lastItems = [];

            function hide() {
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
                hide();
            }

            const runSearch = debounce(function() {
                const q = search.value.trim();
                if (q.length < 1) {
                    hidden.value = '';
                    preview.textContent = emptyText;
                    hide();
                    return;
                }
                fetch('{{ url('/user-approver/search') }}?q=' + encodeURIComponent(q), {
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
                    hide();
                }
            });

            return {
                selectItem: selectItem
            };
        }

        const userSearch = bindSearch('user-search', 'userid', 'user-suggest', 'user-preview', 'ยังไม่ได้เลือกพนักงาน');
        const approverSearch = bindSearch('approver-search', 'approver_userid', 'approver-suggest', 'approver-preview',
            'ยังไม่ได้เลือกผู้อนุมัติ');

        document.getElementById('open-add-modal').addEventListener('click', openAddModal);
        document.getElementById('close-modal-btn').addEventListener('click', function() {
            AppModal.close(modal);
        });

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                resetForm();
                modalTitle.textContent = 'แก้ไขผู้อนุมัติเฉพาะบุคคล';
                userSearch.selectItem({
                    userid: btn.dataset.userid,
                    name: btn.dataset.userName,
                    position: btn.dataset.userPosition,
                    department: btn.dataset.userDepartment
                });
                approverSearch.selectItem({
                    userid: btn.dataset.approverUserid,
                    name: btn.dataset.approverName,
                    position: btn.dataset.approverPosition,
                    department: btn.dataset.approverDepartment
                });
                AppModal.open(modal);
            });
        });

        document.getElementById('approver-form').addEventListener('submit', function(e) {
            const userid = document.getElementById('userid').value;
            const approver = document.getElementById('approver_userid').value;
            const btn = document.getElementById('save-btn');
            if (!userid || !approver) {
                e.preventDefault();
                AppAlert.warning('กรุณาเลือกพนักงานและผู้อนุมัติจากรายการค้นหา');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });

        document.getElementById('table-filter').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#mapping-table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });

        if (shouldOpenModal) {
            AppModal.open(modal);
        }
    </script>
@endpush
