<nav class="space-y-5" aria-label="เมนูหลัก">
    <div>
        <p class="mb-1.5 px-3 text-[11px] font-medium tracking-wide text-base-content/40 uppercase">การอนุมัติ</p>
        <div class="space-y-0.5">
            <a href="{{ url('/user-approver') }}"
                class="nav-link {{ request()->is('user-approver*') ? 'nav-link-active' : '' }}"
                @if (request()->is('user-approver*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                </svg>
                ผู้อนุมัติเฉพาะบุคคล
            </a>
            <a href="{{ url('/department-approver') }}"
                class="nav-link {{ request()->is('department-approver*') ? 'nav-link-active' : '' }}"
                @if (request()->is('department-approver*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M3.75 21V8.25L12 3l8.25 5.25V21M9 21v-6h6v6" />
                </svg>
                ผู้อนุมัติตามแผนก
            </a>
        </div>
    </div>

    <div>
        <p class="mb-1.5 px-3 text-[11px] font-medium tracking-wide text-base-content/40 uppercase">พนักงาน</p>
        <div class="space-y-0.5">
            <a href="{{ url('/users') }}"
                class="nav-link {{ request()->is('users') || request()->is('users/*') ? 'nav-link-active' : '' }}"
                @if (request()->is('users') || request()->is('users/*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                รายชื่อพนักงาน
            </a>
            <a href="{{ url('/user-manual') }}"
                class="nav-link {{ request()->is('user-manual*') ? 'nav-link-active' : '' }}"
                @if (request()->is('user-manual*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3M4.5 19.5a7.5 7.5 0 0115 0M12 11.25a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z" />
                </svg>
                เพิ่มพนักงาน
            </a>
        </div>
    </div>

    <div>
        <p class="mb-1.5 px-3 text-[11px] font-medium tracking-wide text-base-content/40 uppercase">ข้อมูลระบบ</p>
        <div class="space-y-0.5">
            <a href="{{ url('/hris-update') }}"
                class="nav-link {{ request()->is('hris-update*') ? 'nav-link-active' : '' }}"
                @if (request()->is('hris-update*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                </svg>
                อัปเดต HRIS
            </a>
            <a href="{{ url('/data-cleanup') }}"
                class="nav-link {{ request()->is('data-cleanup*') ? 'nav-link-active' : '' }}"
                @if (request()->is('data-cleanup*')) aria-current="page" @endif>
                <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                ล้างข้อมูล
            </a>
        </div>
    </div>
</nav>
