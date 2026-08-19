@extends('layouts.app')

@section('title', 'ล้างข้อมูล')
@section('heading', 'ล้างข้อมูล')
@section('subtitle', 'ลบอีเมลที่ไม่มีพนักงาน และแผนก/ฝ่ายที่ไม่มีคนใช้งาน')

@section('content')
    <div class="panel">
        <div class="panel-toolbar">
            <div class="flex items-center gap-2">
                <h2 class="panel-title">อีเมลที่ไม่มีพนักงาน</h2>
                <span class="badge {{ $orphans->count() > 0 ? 'badge-error' : 'badge-neutral' }}">{{ $orphans->count() }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('partials.search_input', [
                    'id' => 'orphan-filter',
                    'placeholder' => 'ค้นหาในตาราง',
                    'wrapperClass' => 'w-full max-w-xs',
                    'attrs' => 'aria-label="ค้นหาอีเมลที่ไม่มีพนักงาน"',
                ])
                @if ($orphans->count() > 0)
                    <form method="POST" action="{{ url('/data-cleanup/emails/delete-all') }}"
                        data-confirm="ลบอีเมลที่ไม่มีพนักงานทั้งหมด {{ $orphans->count() }} รายการ?">
                        @csrf
                        <button type="submit" class="btn btn-error btn-outline btn-sm">ลบทั้งหมด</button>
                    </form>
                @endif
            </div>
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
                            <td class="font-medium">{{ $row->userid }}</td>
                            <td>{{ $row->email }}</td>
                            <td class="whitespace-nowrap text-base-content/50">{{ $row->updated_at }}</td>
                            <td>
                                <form method="POST" action="{{ url('/data-cleanup/emails/'.$row->id.'/delete') }}"
                                    data-confirm="ลบอีเมล {{ $row->email }} ของรหัส {{ $row->userid }} ?">
                                    @csrf
                                    <button type="submit" class="btn btn-error btn-outline btn-sm">ลบ</button>
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

    <div class="panel">
        <div class="panel-toolbar">
            <div class="flex items-center gap-2">
                <h2 class="panel-title">แผนก / ฝ่ายที่ไม่มีพนักงาน</h2>
                <span class="badge {{ $unused->count() > 0 ? 'badge-error' : 'badge-neutral' }}">{{ $unused->count() }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('partials.search_input', [
                    'id' => 'unused-filter',
                    'placeholder' => 'ค้นหาในตาราง',
                    'wrapperClass' => 'w-full max-w-xs',
                    'attrs' => 'aria-label="ค้นหาแผนกว่าง"',
                ])
                @if ($unused->count() > 0)
                    <form method="POST" action="{{ url('/data-cleanup/departments/delete-all') }}"
                        data-confirm="ลบแผนก/ฝ่ายที่ไม่มีพนักงานทั้งหมด {{ $unused->count() }} รายการ?">
                        @csrf
                        <button type="submit" class="btn btn-error btn-outline btn-sm">ลบทั้งหมด</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="unused-table">
                <thead>
                    <tr>
                        <th>แผนก</th>
                        <th>ฝ่าย</th>
                        <th>อัปเดตล่าสุด</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unused as $row)
                        <tr>
                            <td class="font-medium">{{ $row->department ?: '-' }}</td>
                            <td>{{ $row->division ?: '-' }}</td>
                            <td class="whitespace-nowrap text-base-content/50">{{ $row->updated_at }}</td>
                            <td>
                                <form method="POST" action="{{ url('/data-cleanup/departments/'.$row->id.'/delete') }}"
                                    data-confirm="ลบ {{ $row->department }} / {{ $row->division ?: '-' }} ?">
                                    @csrf
                                    <button type="submit" class="btn btn-error btn-outline btn-sm">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">ไม่มีแผนก/ฝ่ายที่ว่าง</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function bindTableFilter(inputId, tableId) {
            const input = document.getElementById(inputId);
            if (!input) {
                return;
            }
            input.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(row) {
                    row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
                });
            });
        }
        bindTableFilter('orphan-filter', 'orphan-table');
        bindTableFilter('unused-filter', 'unused-table');
    </script>
@endpush
