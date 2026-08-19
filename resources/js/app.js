window.AppModal = {
    open(id) {
        const dialog = typeof id === 'string' ? document.getElementById(id) : id;
        if (dialog && typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    },
    close(id) {
        const dialog = typeof id === 'string' ? document.getElementById(id) : id;
        if (dialog && typeof dialog.close === 'function') {
            dialog.close();
        }
    },
};

function themeColor(name) {
    return getComputedStyle(document.documentElement).getPropertyValue('--color-' + name).trim() || undefined;
}

window.AppAlert = {
    defaults() {
        const openDialog = document.querySelector('dialog[open]');
        return {
            target: openDialog || document.body,
            confirmButtonColor: themeColor('primary'),
            cancelButtonColor: themeColor('neutral'),
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก',
        };
    },
    show(options) {
        return new Promise((resolve) => {
            requestAnimationFrame(() => {
                resolve(Swal.fire(Object.assign({}, this.defaults(), options)));
            });
        });
    },
    success(message) {
        return this.show({
            icon: 'success',
            title: 'สำเร็จ',
            text: message,
        });
    },
    error(message) {
        return this.show({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: message,
            confirmButtonColor: themeColor('error'),
        });
    },
    warning(message) {
        return this.show({
            icon: 'warning',
            title: 'แจ้งเตือน',
            text: message,
        });
    },
    confirm(message, options = {}) {
        return this.show({
            icon: 'warning',
            title: options.title || 'ยืนยัน',
            text: message,
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'ยืนยัน',
            cancelButtonText: options.cancelText || 'ยกเลิก',
            confirmButtonColor: options.confirmColor || themeColor('error'),
            reverseButtons: true,
        }).then((result) => result.isConfirmed);
    },
};

window.AppSearch = {
    syncClearButton(input) {
        const field = input.closest('.search-field');
        if (!field) {
            return;
        }
        const btn = field.querySelector('.search-clear');
        if (!btn) {
            return;
        }
        if (input.value.length > 0) {
            btn.removeAttribute('hidden');
        } else {
            btn.setAttribute('hidden', 'hidden');
        }
    },
    clear(input) {
        input.value = '';
        this.syncClearButton(input);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
    },
    init(root) {
        const scope = root || document;
        scope.querySelectorAll('.search-field [data-clearable]').forEach((input) => {
            this.syncClearButton(input);
            input.addEventListener('input', () => {
                this.syncClearButton(input);
            });
        });
    },
};

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.search-clear');
    if (!btn) {
        return;
    }
    e.preventDefault();
    e.stopPropagation();
    const field = btn.closest('.search-field');
    const input = field ? field.querySelector('[data-clearable]') : null;
    if (input) {
        AppSearch.clear(input);
    }
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    const message = form.getAttribute('data-confirm');
    if (!message || form.dataset.confirmed === '1') {
        return;
    }
    e.preventDefault();
    AppAlert.confirm(message).then((ok) => {
        if (!ok) {
            return;
        }
        form.dataset.confirmed = '1';
        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });
});

function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const openBtn = document.getElementById('sidebar-open');
    const closeBtn = document.getElementById('sidebar-close');

    if (!sidebar || !overlay || !openBtn || !closeBtn) {
        return;
    }

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    };

    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
}

document.addEventListener('DOMContentLoaded', () => {
    AppSearch.init();
    initSidebar();
});

import './department-select';
