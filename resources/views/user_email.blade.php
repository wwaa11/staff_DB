@extends('layouts.app')

@section('title', 'แก้ไขอีเมลพนักงาน')
@section('heading', 'แก้ไขอีเมลพนักงาน')
@section('subtitle', 'ค้นหาพนักงานแล้วบันทึกหรือแก้ไขอีเมลที่ใช้ในระบบ')

@section('content')
    <form method="POST" action="{{ url('/user-email') }}" class="card" id="email-form">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="label" for="user-search">พนักงาน</label>
                <div class="relative">
                    <input type="text" class="input" id="user-search" autocomplete="off"
                        placeholder="ค้นหารหัส ชื่อ หรืออีเมล">
                    <input type="hidden" name="userid" id="userid" value="{{ old('userid') }}">
                    <div class="suggest-list" id="user-suggest" role="listbox"></div>
                </div>
                <div class="preview" id="user-preview">ยังไม่ได้เลือกพนักงาน</div>
            </div>
            <div>
                <label class="label" for="email">อีเมล</label>
                <input type="email" class="input" id="email" name="email" value="{{ old('email') }}"
                    placeholder="name@example.com" required>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-primary" id="save-btn">บันทึก</button>
        </div>
    </form>

    <div class="card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900">อีเมลที่ไม่มีพนักงาน ({{ $orphans->count() }})</h2>
            @if ($orphans->count() > 0)
                <form method="POST" action="{{ url('/user-email/clear-orphans') }}"
                    onsubmit="return confirm('ลบอีเมลที่ไม่มีพนักงานทั้งหมด {{ $orphans->count() }} รายการ?');">
                    @csrf
                    <button type="submit" class="btn-danger">ลบทั้งหมด</button>
                </form>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="orphan-table">
                <thead>
                    <tr>
                        <th>รหัสพนักงาน</th>
                        <th>อีเมล</th>
                        <th>อัปเดต</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orphans as $row)
                        <tr>
                            <td>{{ $row->userid }}</td>
                            <td>{{ $row->email }}</td>
                            <td>{{ $row->updated_at }}</td>
                            <td>
                                <form method="POST" action="{{ url('/user-email/orphan/'.$row->id.'/delete') }}"
                                    onsubmit="return confirm('ลบอีเมล {{ $row->email }} ของรหัส {{ $row->userid }} ?');">
                                    @csrf
                                    <button type="submit" class="btn-danger">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">ไม่มีอีเมลที่ไม่มีพนักงาน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900">รายการอีเมล ({{ $emails->count() }})</h2>
            <input type="search" class="input max-w-xs" id="table-filter" placeholder="ค้นหาในตาราง"
                aria-label="ค้นหาในตาราง">
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="email-table">
                <thead>
                    <tr>
                        <th>พนักงาน</th>
                        <th>แผนก</th>
                        <th>อีเมล</th>
                        <th>อัปเดต</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emails as $row)
                        <tr>
                            <td>
                                <strong>{{ $row->name ?: '-' }}</strong><br>
                                <small class="text-slate-500">{{ $row->userid }} · {{ $row->position ?: '-' }}</small>
                            </td>
                            <td>{{ $row->department ?: '-' }}@if ($row->division)
                                    · {{ $row->division }}
                                @endif
                            </td>
                            <td>{{ $row->email }}</td>
                            <td>{{ $row->updated_at }}</td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="btn-secondary edit-btn"
                                        data-userid="{{ $row->userid }}" data-name="{{ $row->name }}"
                                        data-position="{{ $row->position }}" data-department="{{ $row->department }}"
                                        data-email="{{ $row->email }}">
                                        แก้ไข
                                    </button>
                                    <form method="POST" action="{{ url('/user-email/'.$row->id.'/delete') }}"
                                        onsubmit="return confirm('ลบอีเมลของ {{ $row->name }} ?');">
                                        @csrf
                                        <button type="submit" class="btn-danger">ลบ</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">ยังไม่มีข้อมูลอีเมล</td>
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
        const search = document.getElementById('user-search');
        const hidden = document.getElementById('userid');
        const suggest = document.getElementById('user-suggest');
        const preview = document.getElementById('user-preview');
        const emailInput = document.getElementById('email');
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

        function personHtml(item) {
            if (!item) {
                return '';
            }
            return '<strong>' + (item.name || '-') + '</strong><br><small>' + item.userid + ' · ' +
                (item.position || '-') + (item.department ? ' · ' + item.department : '') +
                (item.email ? '<br>' + item.email : '') + '</small>';
        }

        function selectItem(item) {
            hidden.value = item.userid;
            search.value = item.userid + ' - ' + (item.name || '');
            preview.innerHTML = personHtml(item);
            emailInput.value = item.email || '';
            suggest.style.display = 'none';
            suggest.innerHTML = '';
            emailInput.focus();
        }

        const runSearch = debounce(function() {
            const q = search.value.trim();
            if (q.length < 1) {
                hidden.value = '';
                preview.textContent = 'ยังไม่ได้เลือกพนักงาน';
                suggest.style.display = 'none';
                return;
            }
            fetch('{{ url('/user-email/search') }}?q=' + encodeURIComponent(q), {
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
                suggest.style.display = 'none';
            }
        });

        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                selectItem({
                    userid: btn.dataset.userid,
                    name: btn.dataset.name,
                    position: btn.dataset.position,
                    department: btn.dataset.department,
                    email: btn.dataset.email
                });
                document.getElementById('email-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        document.getElementById('email-form').addEventListener('submit', function(e) {
            const btn = document.getElementById('save-btn');
            if (!hidden.value) {
                e.preventDefault();
                alert('กรุณาเลือกพนักงานจากรายการค้นหา');
                return;
            }
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });

        document.getElementById('table-filter').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#email-table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });
    </script>
@endpush
