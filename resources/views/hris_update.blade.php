@extends('layouts.app')

@section('title', 'อัปเดต HRIS')
@section('heading', 'อัปเดตข้อมูลพนักงานจาก HRIS')
@section('subtitle', 'ดึงชื่อ ตำแหน่ง และแผนกของพนักงานทั้งหมดจาก HRIS แล้วอัปเดตในระบบ')

@section('content')
    <div class="card">
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-sm text-slate-600">พนักงานในระบบ</div>
                <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($userCount) }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-sm text-slate-600">สถานะ</div>
                <div class="mt-1 text-lg font-bold text-slate-900">
                    {{ $running ? 'กำลังอัปเดต...' : 'พร้อมรัน' }}
                </div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-sm text-slate-600">รันล่าสุด</div>
                <div class="mt-1 text-lg font-bold text-slate-900">
                    {{ $last['ran_at'] ?? '-' }}
                </div>
            </div>
        </div>

        @if ($last)
            <p class="mb-4 text-slate-700">
                ครั้งล่าสุดอัปเดต {{ $last['updated'] ?? 0 }} รายการ ลบ {{ $last['deleted'] ?? 0 }} รายการ
                จากทั้งหมด {{ $last['total'] ?? 0 }}
                @if (! empty($last['ran_by_name']))
                    โดย {{ $last['ran_by_name'] }} ({{ $last['ran_by'] }})
                @endif
            </p>
        @endif

        <p class="mb-5 text-slate-700">งานนี้อาจใช้เวลานาน เพราะระบบเรียก HRIS ทีละคน และพักทุก 15 รายการ
            อย่าปิดหน้านี้จนกว่าจะเสร็จ</p>

        <form method="POST" action="{{ url('/hris-update') }}" id="hris-form">
            @csrf
            <button type="submit" class="btn-primary" id="run-btn" @disabled($running)>
                {{ $running ? 'กำลังอัปเดต...' : 'เริ่มอัปเดตทั้งหมด' }}
            </button>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('hris-form').addEventListener('submit', function(e) {
            if (!confirm('เริ่มอัปเดตพนักงานทั้งหมดจาก HRIS? งานนี้อาจใช้เวลานาน')) {
                e.preventDefault();
                return;
            }
            var btn = document.getElementById('run-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังอัปเดต... กรุณารอ';
        });
    </script>
@endpush
