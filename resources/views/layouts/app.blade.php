<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'W Staff')</title>
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
                            light: '#E8F1F8',
                            dark: '#0B3A63',
                        },
                        danger: '#B42318',
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .btn-primary {
                @apply inline-flex items-center justify-center gap-2 min-h-11 px-4 rounded-lg text-sm font-semibold cursor-pointer border border-transparent bg-primary text-white transition-colors duration-200 hover:bg-primary-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:opacity-60 disabled:cursor-not-allowed;
            }
            .btn-secondary {
                @apply inline-flex items-center justify-center gap-2 min-h-11 px-4 rounded-lg text-sm font-semibold cursor-pointer border border-slate-300 bg-white text-slate-700 transition-colors duration-200 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 disabled:opacity-60 disabled:cursor-not-allowed;
            }
            .btn-danger {
                @apply inline-flex items-center justify-center gap-2 min-h-11 px-4 rounded-lg text-sm font-semibold cursor-pointer border border-danger bg-white text-danger transition-colors duration-200 hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 disabled:opacity-60 disabled:cursor-not-allowed;
            }
            .label {
                @apply mb-1.5 block text-sm font-semibold text-slate-800;
            }
            .input,
            .select {
                @apply block w-full min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-base text-slate-900 placeholder:text-slate-400 transition-colors duration-200 focus-visible:border-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20;
            }
            .card {
                @apply mb-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6;
            }
            .data-table {
                @apply w-full min-w-[640px] text-left text-sm;
            }
            .data-table thead th {
                @apply whitespace-nowrap border-b border-slate-200 bg-slate-50 px-3 py-3 font-semibold text-slate-600;
            }
            .data-table tbody td {
                @apply border-b border-slate-100 px-3 py-3 align-middle text-slate-800;
            }
            .data-table tbody tr {
                @apply transition-colors duration-150;
            }
            .data-table tbody tr:hover {
                @apply bg-slate-50;
            }
            .empty-state {
                @apply py-10 text-center text-slate-500;
            }
            .preview {
                @apply mt-2 min-h-[72px] rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-slate-500;
            }
            .preview strong {
                @apply text-slate-900;
            }
            .preview small,
            .suggest-item small {
                @apply text-slate-500;
            }
            .suggest-list {
                @apply absolute left-0 right-0 top-full z-20 mt-1 max-h-64 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg;
                display: none;
            }
            .suggest-item {
                @apply block min-h-11 w-full cursor-pointer border-0 bg-white px-3 py-2.5 text-left hover:bg-primary-light focus:bg-primary-light focus:outline-none;
            }
            .nav-link {
                @apply flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-200 transition-colors duration-200 hover:bg-white/10 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40;
            }
            .nav-link-active {
                @apply bg-white/15 text-white hover:bg-white/20 hover:text-white;
            }
        }
    </style>
    @stack('head')
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden"></div>

    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-primary-dark text-white transition-transform duration-200 motion-reduce:transition-none lg:translate-x-0">
        <div class="flex items-center justify-between gap-3 border-b border-white/10 px-5 py-4">
            <div>
                <div class="text-lg font-bold tracking-wide">PR9 Staff</div>
                <div class="text-xs text-slate-300">ระบบข้อมูลพนักงาน</div>
            </div>
            <button type="button" id="sidebar-close"
                class="inline-flex min-h-11 min-w-11 cursor-pointer items-center justify-center rounded-lg text-white hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/40 lg:hidden"
                aria-label="ปิดเมนู">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-3 py-4">
            @include('partials.admin_nav')
        </div>
        <div class="border-t border-white/10 px-4 py-4">
            <div class="truncate text-sm font-semibold">{{ auth()->user()->name }}</div>
            <div class="mb-3 text-xs text-slate-300">{{ auth()->user()->userid }}</div>
            <form method="POST" action="{{ url('/logout') }}">
                @csrf
                <button type="submit" class="btn-secondary w-full">ออกจากระบบ</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                <button type="button" id="sidebar-open"
                    class="inline-flex min-h-11 min-w-11 cursor-pointer items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 lg:hidden"
                    aria-label="เปิดเมนู">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    <h1 class="truncate text-xl font-bold text-slate-900">@yield('heading')</h1>
                    @hasSection('subtitle')
                        <p class="mt-0.5 text-sm text-slate-600">@yield('subtitle')</p>
                    @endif
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>

    <script>
        (function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const openBtn = document.getElementById('sidebar-open');
            const closeBtn = document.getElementById('sidebar-close');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }

            openBtn.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);
        })();
    </script>
    @stack('scripts')
</body>

</html>
