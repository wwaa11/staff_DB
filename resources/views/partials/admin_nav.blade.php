<nav class="space-y-6" aria-label="เมนูหลัก">
    <div>
        <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">การอนุมัติ</p>
        <div class="space-y-1">
            <a href="{{ url('/user-approver') }}"
                class="nav-link {{ request()->is('user-approver*') ? 'nav-link-active' : '' }}"
                @if (request()->is('user-approver*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                </svg>
                ผู้อนุมัติเฉพาะบุคคล
            </a>
            <a href="{{ url('/department-approver') }}"
                class="nav-link {{ request()->is('department-approver*') ? 'nav-link-active' : '' }}"
                @if (request()->is('department-approver*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3.75 21V8.25L12 3l8.25 5.25V21M9 21v-6h6v6" />
                </svg>
                ผู้อนุมัติตามแผนก
            </a>
        </div>
    </div>

    <div>
        <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">พนักงาน</p>
        <div class="space-y-1">
            <a href="{{ url('/user-email') }}"
                class="nav-link {{ request()->is('user-email*') ? 'nav-link-active' : '' }}"
                @if (request()->is('user-email*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5H4.5a2.25 2.25 0 00-2.25 2.25m19.5 0l-8.634 5.756a2.25 2.25 0 01-2.532 0L2.25 6.75" />
                </svg>
                อีเมลพนักงาน
            </a>
            <a href="{{ url('/user-manual') }}"
                class="nav-link {{ request()->is('user-manual*') ? 'nav-link-active' : '' }}"
                @if (request()->is('user-manual*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3M4.5 19.5a7.5 7.5 0 0115 0M12 11.25a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z" />
                </svg>
                เพิ่มพนักงาน
            </a>
        </div>
    </div>

    <div>
        <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">ข้อมูลระบบ</p>
        <div class="space-y-1">
            <a href="{{ url('/hris-update') }}"
                class="nav-link {{ request()->is('hris-update*') ? 'nav-link-active' : '' }}"
                @if (request()->is('hris-update*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                </svg>
                อัปเดต HRIS
            </a>
            <a href="{{ url('/department-cleanup') }}"
                class="nav-link {{ request()->is('department-cleanup*') ? 'nav-link-active' : '' }}"
                @if (request()->is('department-cleanup*')) aria-current="page" @endif>
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                ลบแผนกว่าง
            </a>
        </div>
    </div>
</nav>
