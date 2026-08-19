@extends('layouts.app')

@section('title', 'ลบแผนกว่าง')
@section('heading', 'ลบแผนก / ฝ่ายที่ไม่มีพนักงาน')
@section('subtitle', 'รายการที่ไม่มีพนักงานในระบบ จะถูกลบออกจากตารางแผนก และผู้อนุมัติของแผนกนั้นด้วย')

@section('content')
    <div class="card">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900">พบ {{ $unused->count() }} รายการ</h2>
            <div class="flex flex-wrap items-center gap-2">
                <input type="search" class="input max-w-xs" id="table-filter" placeholder="ค้นหาในตาราง"
                    aria-label="ค้นหาในตาราง">
                @if ($unused->count() > 0)
                    <form method="POST" action="{{ url('/department-cleanup/delete-all') }}"
                        onsubmit="return confirm('ลบแผนก/ฝ่ายที่ไม่มีพนักงานทั้งหมด {{ $unused->count() }} รายการ?');">
                        @csrf
                        <button type="submit" class="btn-danger">ลบทั้งหมด</button>
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
                            <td>{{ $row->department ?: '-' }}</td>
                            <td>{{ $row->division ?: '-' }}</td>
                            <td>{{ $row->updated_at }}</td>
                            <td>
                                <form method="POST" action="{{ url('/department-cleanup/'.$row->id.'/delete') }}"
                                    onsubmit="return confirm('ลบ {{ $row->department }} / {{ $row->division ?: '-' }} ?');">
                                    @csrf
                                    <button type="submit" class="btn-danger">ลบ</button>
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
        document.getElementById('table-filter').addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#unused-table tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
            });
        });
    </script>
@endpush
