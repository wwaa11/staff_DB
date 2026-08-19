@extends('layouts.app')

@section('title', 'กำหนดผู้อนุมัติเฉพาะบุคคล')
@section('heading', 'กำหนดผู้อนุมัติเฉพาะบุคคล')
@section('subtitle', 'ถ้าพนักงานมีผู้อนุมัติที่ตั้งไว้ที่นี่ API getApprover จะส่งผู้อนุมัติคนนี้แทนผู้อนุมัติตามแผนก')

@section('content')
    <form method="POST" action="{{ url('/user-approver') }}" class="card" id="approver-form">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label" for="user-search">พนักงาน</label>
                <div class="relative">
                    <input type="text" class="input" id="user-search" autocomplete="off"
                        placeholder="ค้นหารหัสหรือชื่อพนักงาน" aria-describedby="user-preview">
                    <input type="hidden" name="userid" id="userid" value="{{ old('userid') }}">
                    <div class="suggest-list" id="user-suggest" role="listbox"></div>
                </div>
                <div class="preview" id="user-preview">ยังไม่ได้เลือกพนักงาน</div>
            </div>
            <div>
                <label class="label" for="approver-search">ผู้อนุมัติ</label>
                <div class="relative">
                    <input type="text" class="input" id="approver-search" autocomplete="off"
                        placeholder="ค้นหารหัสหรือชื่อผู้อนุมัติ" aria-describedby="approver-preview">
                    <input type="hidden" name="approver_userid" id="approver_userid"
                        value="{{ old('approver_userid') }}">
                    <div class="suggest-list" id="approver-suggest" role="listbox"></div>
                </div>
                <div class="preview" id="approver-preview">ยังไม่ได้เลือกผู้อนุมัติ</div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-primary" id="save-btn">บันทึก</button>
        </div>
    </form>

    <div class="card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900">รายการที่กำหนดแล้ว ({{ $mappings->count() }})</h2>
            <input type="search" class="input max-w-xs" id="table-filter" placeholder="ค้นหาในตาราง"
                aria-label="ค้นหาในตาราง">
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
                                <strong>{{ $row->user_name ?: '-' }}</strong><br>
                                <small class="text-slate-500">{{ $row->userid }} · {{ $row->user_position ?: '-' }}</small>
                            </td>
                            <td>{{ $row->user_department ?: '-' }}</td>
                            <td>
                                <strong>{{ $row->approver_name ?: '-' }}</strong><br>
                                <small class="text-slate-500">{{ $row->approver_userid }} ·
                                    {{ $row->approver_position ?: '-' }}</small>
                            </td>
                            <td>{{ $row->approver_department ?: '-' }}</td>
                            <td>{{ $row->updated_at }}</td>
                            <td>
                                <form method="POST" action="{{ url('/user-approver/'.$row->id.'/delete') }}"
                                    onsubmit="return confirm('ลบการกำหนดผู้อนุมัติของ {{ $row->user_name }} ?');">
                                    @csrf
                                    <button type="submit" class="btn-danger">ลบ</button>
                                </form>
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
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

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
                if (!search.contains(e.target) && !suggest.contains(e.target)) {
                    hide();
                }
            });
        }

        bindSearch('user-search', 'userid', 'user-suggest', 'user-preview', 'ยังไม่ได้เลือกพนักงาน');
        bindSearch('approver-search', 'approver_userid', 'approver-suggest', 'approver-preview', 'ยังไม่ได้เลือกผู้อนุมัติ');

        document.getElementById('approver-form').addEventListener('submit', function(e) {
            const userid = document.getElementById('userid').value;
            const approver = document.getElementById('approver_userid').value;
            const btn = document.getElementById('save-btn');
            if (!userid || !approver) {
                e.preventDefault();
                alert('กรุณาเลือกพนักงานและผู้อนุมัติจากรายการค้นหา');
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
    </script>
@endpush
