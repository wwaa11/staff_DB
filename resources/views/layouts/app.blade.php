<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('logo/Logo.ico') }}" type="image/x-icon">
    <title>@yield('title', 'PR9 Staff')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('head')
</head>

<body class="min-h-screen bg-base-200 font-sans text-base-content antialiased">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-base-content/20 lg:hidden"></div>

    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-60 -translate-x-full flex-col border-r border-base-300 bg-base-100 transition-transform duration-200 motion-reduce:transition-none lg:translate-x-0">
        <div class="flex items-center justify-between gap-3 border-b border-base-300 px-4 py-4 cursor-pointer">
            <div class="flex items-center gap-2">
                <img src="{{ asset('logo/Logo.png') }}" alt="PR9 Staff" class="h-8 w-8">
                <div>
                    <div class="text-sm font-semibold tracking-wide text-base-content">PR9 Staff</div>
                    <div class="text-xs text-base-content/50">ระบบข้อมูลพนักงาน</div>
                </div>
            </div>
            <button type="button" id="sidebar-close"
                class="btn btn-ghost btn-sm btn-square lg:hidden" aria-label="ปิดเมนู">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-2 py-3">
            @include('partials.admin_nav')
        </div>
        <div class="border-t border-base-300 px-3 py-3">
            <div class="mb-2 px-2">
                <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
                <div class="text-xs text-base-content/50">{{ auth()->user()->userid }}</div>
            </div>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm w-full justify-start">ออกจากระบบ</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-60">
        <header class="sticky top-0 z-20 border-b border-base-300 bg-base-100">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                <button type="button" id="sidebar-open"
                    class="btn btn-ghost btn-sm btn-square lg:hidden" aria-label="เปิดเมนู">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-lg font-semibold text-base-content">@yield('heading')</h1>
                    @hasSection('subtitle')
                        <p class="mt-0.5 max-w-3xl truncate text-sm text-base-content/60">@yield('subtitle')</p>
                    @endif
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>

    @vite('resources/js/app.js')
    @stack('scripts')
</body>

</html>
