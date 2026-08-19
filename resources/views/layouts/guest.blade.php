<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'เข้าสู่ระบบ')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'Tahoma', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#0F4C81',
                            hover: '#0C3D67',
                            dark: '#0B3A63',
                        },
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply inline-flex w-full items-center justify-center min-h-11 px-4 rounded-lg text-sm font-semibold cursor-pointer border border-transparent bg-primary text-white transition-colors duration-200 hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:opacity-60 disabled:cursor-not-allowed;
            }
            .label {
                @apply mb-1.5 block text-sm font-semibold text-slate-800;
            }
            .input {
                @apply block w-full min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-base text-slate-900 placeholder:text-slate-400 focus-visible:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="text-2xl font-bold text-primary-dark">W Staff</div>
                <div class="text-sm text-slate-600">ระบบข้อมูลพนักงาน</div>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
