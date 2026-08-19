@extends('layouts.guest')

@section('title', 'เข้าสู่ระบบ')

@section('content')
    <h1 class="text-xl font-bold text-slate-900">เข้าสู่ระบบ</h1>
    <p class="mb-6 mt-1 text-sm text-slate-600">ใช้รหัสพนักงานและรหัสผ่าน Windows (LDAP)</p>

    @include('partials.alerts')

    <form method="POST" action="{{ url('/login') }}" id="login-form">
        @csrf
        <div class="mb-4">
            <label class="label" for="userid">รหัสพนักงาน</label>
            <input type="text" class="input" id="userid" name="userid" value="{{ old('userid') }}"
                autocomplete="username" required autofocus>
        </div>
        <div class="mb-6">
            <label class="label" for="password">รหัสผ่าน</label>
            <input type="password" class="input" id="password" name="password" autocomplete="current-password"
                required>
        </div>
        <button type="submit" class="btn-primary" id="login-btn">เข้าสู่ระบบ</button>
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
