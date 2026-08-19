@extends('layouts.app')

@section('title', 'อัปเดต HRIS')
@section('heading', 'อัปเดตข้อมูลพนักงานจาก HRIS')
@section('subtitle', 'ดึงชื่อ ตำแหน่ง และแผนกของพนักงานทั้งหมดจาก HRIS แล้วอัปเดตในระบบ')

@section('content')
    <div class="panel">
        <div class="panel-toolbar">
            <div>
                <h2 class="panel-title">สถานะการอัปเดต</h2>
                <p class="mt-0.5 text-sm text-base-content/50">งานนี้อาจใช้เวลานาน อย่าปิดหน้านี้จนกว่าจะเสร็จ</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="stat-card">
                    <div class="stat-label">พนักงานในระบบ</div>
                    <div class="stat-value">{{ number_format($userCount) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">สถานะ</div>
                    <div class="mt-1 text-lg font-bold {{ $running ? 'text-warning' : 'text-success' }}">
                        {{ $running ? 'กำลังอัปเดต...' : 'พร้อมรัน' }}
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">รันล่าสุด</div>
                    <div class="mt-1 text-lg font-bold text-base-content">
                        {{ $last['ran_at'] ?? '-' }}
                    </div>
                </div>
            </div>

            @if ($last)
                <div class="mb-5 rounded-xl border border-base-300 bg-base-200 px-4 py-3 text-sm text-base-content/80">
                    ครั้งล่าสุดอัปเดต <strong>{{ $last['updated'] ?? 0 }}</strong> รายการ
                    ข้าม <strong>{{ $last['skipped'] ?? 0 }}</strong> รายการ
                    ลบ <strong>{{ $last['deleted'] ?? 0 }}</strong> รายการ
                    จากทั้งหมด <strong>{{ $last['total'] ?? 0 }}</strong>
                    @if (! empty($last['ran_by_name']))
                        โดย {{ $last['ran_by_name'] }} ({{ $last['ran_by'] }})
                    @endif
                </div>
            @endif

            <p class="mb-5 text-sm leading-relaxed text-base-content/60">ระบบเรียก HRIS ทีละคน และพักทุก 15 รายการ
                เพื่อลดภาระของเซิร์ฟเวอร์ หากพนักงานอัปเดตภายใน 7 วัน ระบบจะข้ามการดึงข้อมูลจาก HRIS</p>

            <form method="POST" action="{{ url('/hris-update') }}" id="hris-form"
                data-confirm="เริ่มอัปเดตพนักงานทั้งหมดจาก HRIS? งานนี้อาจใช้เวลานาน">
                @csrf
                <button type="submit" class="btn btn-primary" id="run-btn" @disabled($running)>
                    {{ $running ? 'กำลังอัปเดต...' : 'เริ่มอัปเดตทั้งหมด' }}
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('hris-form').addEventListener('submit', function() {
            if (this.dataset.confirmed !== '1') {
                return;
            }
            var btn = document.getElementById('run-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังอัปเดต... กรุณารอ';
        });
    </script>
@endpush
