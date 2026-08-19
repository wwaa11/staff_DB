@extends('layouts.guest')

@section('title', 'เข้าสู่ระบบ')

@section('content')
    <h1 class="text-base font-semibold text-base-content">เข้าสู่ระบบ</h1>
    <p class="mb-5 mt-1 text-sm text-base-content/60">ใช้รหัสพนักงานและรหัสผ่าน Windows (LDAP)</p>

    @include('partials.alerts')

    <form method="POST" action="{{ url('/login') }}" id="login-form" class="space-y-4">
        @csrf
        <div>
            <label class="field-label" for="userid">รหัสพนักงาน</label>
            <input type="text" class="input input-bordered w-full" id="userid" name="userid"
                value="{{ old('userid') }}" autocomplete="username" required autofocus>
        </div>
        <div>
            <label class="field-label" for="password">รหัสผ่าน</label>
            <input type="password" class="input input-bordered w-full" id="password" name="password"
                autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary w-full" id="login-btn">เข้าสู่ระบบ</button>
    </form>
@endsection

@push('scripts')
    <script>
        document.getElementById('login-form').addEventListener('submit', function() {
            var btn = document.getElementById('login-btn');
            btn.disabled = true;
            btn.textContent = 'กำลังเข้าสู่ระบบ...';
        });
    </script>
@endpush
