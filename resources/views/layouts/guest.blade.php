<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('logo/Logo.ico') }}" type="image/x-icon">
    <title>@yield('title', 'เข้าสู่ระบบ')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-sm">
            <div class="mb-6 text-center">
                <img src="{{ asset('logo/Logo.png') }}" alt="PR9 Staff" class="h-24 w-24 mx-auto">
                <div class="text-lg font-semibold text-base-content">PR9 Staff</div>
                <div class="mt-0.5 text-sm text-base-content/60">ระบบข้อมูลพนักงาน</div>
            </div>
            <div class="rounded-lg border border-base-300 bg-base-100 p-6">
                @yield('content')
            </div>
        </div>
    </div>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>
