function initDepartmentDivisionSelect() {
    const dataEl = document.getElementById('department-data');
    const root = document.querySelector('.department-division-select');
    if (!dataEl || !root) {
        return;
    }

    const data = JSON.parse(dataEl.textContent || '{}');
    const divisions = Object.keys(data);
    const search = root.querySelector('[data-division-search]');
    const suggest = root.querySelector('[data-division-suggest]');
    const departmentSelect = root.querySelector('[data-department-select]');
    const divisionValue = root.querySelector('[data-division-value]');
    const selectedId = root.getAttribute('data-selected-id') || '';
    const hasDivision = root.getAttribute('data-has-division') === '1';
    const selectedDivision = root.getAttribute('data-selected-division') || '';
    const autoSubmit = root.getAttribute('data-auto-submit') === '1';
    const allowEmptyDepartment = root.getAttribute('data-allow-empty-department') === '1';
    const emptyDepartmentLabel = root.getAttribute('data-empty-department-label') || 'เลือกแผนก';
    const form = root.closest('form');

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[char]);
    }

    function displayDivision(name) {
        return name ? name : '(ไม่มีฝ่าย)';
    }

    function hideSuggest() {
        suggest.style.display = 'none';
        suggest.innerHTML = '';
    }

    function setDivisionValue(name, active) {
        if (!divisionValue) {
            return;
        }
        if (!active) {
            divisionValue.value = '';
            divisionValue.disabled = true;
            return;
        }
        divisionValue.disabled = false;
        divisionValue.value = name;
    }

    function findById(id) {
        for (let i = 0; i < divisions.length; i++) {
            const items = data[divisions[i]] || [];
            for (let j = 0; j < items.length; j++) {
                if (String(items[j].id) === String(id)) {
                    return { division: divisions[i], item: items[j] };
                }
            }
        }
        return null;
    }

    function fillDepartments(divisionName, selectedDepartmentId) {
        const items = data[divisionName] || [];
        departmentSelect.innerHTML = '<option value="">' + emptyDepartmentLabel + '</option>';
        items.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.department ? item.department : '(ไม่มีแผนก)';
            if (String(item.id) === String(selectedDepartmentId)) {
                opt.selected = true;
            }
            departmentSelect.appendChild(opt);
        });
        if (!allowEmptyDepartment && !selectedDepartmentId && items.length === 1) {
            departmentSelect.value = String(items[0].id);
        }
    }

    function selectDivision(name, selectedDepartmentId) {
        search.value = displayDivision(name);
        if (window.AppSearch) {
            AppSearch.syncClearButton(search);
        }
        setDivisionValue(name, true);
        fillDepartments(name, selectedDepartmentId);
        hideSuggest();
    }

    function clearDivision() {
        setDivisionValue('', false);
        departmentSelect.innerHTML = '<option value="">' + emptyDepartmentLabel + '</option>';
        hideSuggest();
    }

    function renderSuggest(q) {
        const query = (q || '').toLowerCase();
        const matched = divisions.filter((name) => {
            return displayDivision(name).toLowerCase().indexOf(query) !== -1;
        }).slice(0, 30);

        if (!matched.length) {
            suggest.innerHTML = '<div class="suggest-item">ไม่พบฝ่าย</div>';
            suggest.style.display = 'block';
            return;
        }

        suggest.innerHTML = matched.map((name) => {
            return '<button type="button" class="suggest-item" data-name="' + encodeURIComponent(name) +
                '" role="option"><strong>' + escapeHtml(displayDivision(name)) + '</strong><br><small>' +
                (data[name].length) + ' แผนก</small></button>';
        }).join('');
        suggest.style.display = 'block';
    }

    search.addEventListener('input', () => {
        if (window.AppSearch) {
            AppSearch.syncClearButton(search);
        }
        if (!search.value.trim()) {
            clearDivision();
            return;
        }
        clearDivision();
        departmentSelect.innerHTML = '<option value="">' + emptyDepartmentLabel + '</option>';
        renderSuggest(search.value.trim());
    });

    search.addEventListener('focus', () => {
        renderSuggest(search.value.trim());
    });

    suggest.addEventListener('click', (e) => {
        const btn = e.target.closest('.suggest-item');
        if (!btn || btn.getAttribute('data-name') === null) {
            return;
        }
        selectDivision(decodeURIComponent(btn.getAttribute('data-name')));
        if (autoSubmit && form && departmentSelect.value) {
            form.submit();
        }
    });

    document.addEventListener('click', (e) => {
        if (!search.contains(e.target) && !suggest.contains(e.target) && !e.target.closest('.search-clear')) {
            hideSuggest();
        }
    });

    departmentSelect.addEventListener('change', () => {
        if (autoSubmit && form && departmentSelect.value) {
            form.submit();
        }
    });

    if (selectedId) {
        const found = findById(selectedId);
        if (found) {
            selectDivision(found.division, found.item.id);
        }
    } else if (hasDivision) {
        selectDivision(selectedDivision);
    } else {
        setDivisionValue('', false);
    }

    window.setDepartmentDivisionSelection = function (id) {
        const found = findById(id);
        if (found) {
            selectDivision(found.division, found.item.id);
        }
    };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDepartmentDivisionSelect);
} else {
    initDepartmentDivisionSelect();
}
