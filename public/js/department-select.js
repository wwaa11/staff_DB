(function() {
    function initDepartmentDivisionSelect() {
        var dataEl = document.getElementById('department-data');
        var root = document.querySelector('.department-division-select');
        if (!dataEl || !root) {
            return;
        }

        var data = JSON.parse(dataEl.textContent || '{}');
        var divisions = Object.keys(data);
        var search = root.querySelector('[data-division-search]');
        var suggest = root.querySelector('[data-division-suggest]');
        var departmentSelect = root.querySelector('[data-department-select]');
        var selectedId = root.getAttribute('data-selected-id') || '';
        var autoSubmit = root.getAttribute('data-auto-submit') === '1';
        var form = root.closest('form');

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function(char) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                })[char];
            });
        }

        function displayDivision(name) {
            return name ? name : '(ไม่มีฝ่าย)';
        }

        function hideSuggest() {
            suggest.style.display = 'none';
            suggest.innerHTML = '';
        }

        function findById(id) {
            for (var i = 0; i < divisions.length; i++) {
                var items = data[divisions[i]] || [];
                for (var j = 0; j < items.length; j++) {
                    if (String(items[j].id) === String(id)) {
                        return { division: divisions[i], item: items[j] };
                    }
                }
            }
            return null;
        }

        function fillDepartments(divisionName, selectedDepartmentId) {
            var items = data[divisionName] || [];
            departmentSelect.innerHTML = '<option value="">เลือกแผนก</option>';
            items.forEach(function(item) {
                var opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = item.department ? item.department : '(ไม่มีแผนก)';
                if (String(item.id) === String(selectedDepartmentId)) {
                    opt.selected = true;
                }
                departmentSelect.appendChild(opt);
            });
            if (!selectedDepartmentId && items.length === 1) {
                departmentSelect.value = String(items[0].id);
            }
        }

        function selectDivision(name, selectedDepartmentId) {
            search.value = displayDivision(name);
            fillDepartments(name, selectedDepartmentId);
            hideSuggest();
        }

        function renderSuggest(q) {
            var query = (q || '').toLowerCase();
            var matched = divisions.filter(function(name) {
                return displayDivision(name).toLowerCase().indexOf(query) !== -1;
            }).slice(0, 30);

            if (!matched.length) {
                suggest.innerHTML = '<div class="suggest-item">ไม่พบฝ่าย</div>';
                suggest.style.display = 'block';
                return;
            }

            suggest.innerHTML = matched.map(function(name) {
                return '<button type="button" class="suggest-item" data-name="' + encodeURIComponent(name) +
                    '" role="option"><strong>' + escapeHtml(displayDivision(name)) + '</strong><br><small>' +
                    (data[name].length) + ' แผนก</small></button>';
            }).join('');
            suggest.style.display = 'block';
        }

        search.addEventListener('input', function() {
            departmentSelect.innerHTML = '<option value="">เลือกแผนก</option>';
            renderSuggest(search.value.trim());
        });

        search.addEventListener('focus', function() {
            renderSuggest(search.value.trim());
        });

        suggest.addEventListener('click', function(e) {
            var btn = e.target.closest('.suggest-item');
            if (!btn || btn.getAttribute('data-name') === null) {
                return;
            }
            selectDivision(decodeURIComponent(btn.getAttribute('data-name')));
            if (autoSubmit && form && departmentSelect.value) {
                form.submit();
            }
        });

        document.addEventListener('click', function(e) {
            if (!search.contains(e.target) && !suggest.contains(e.target)) {
                hideSuggest();
            }
        });

        departmentSelect.addEventListener('change', function() {
            if (autoSubmit && form && departmentSelect.value) {
                form.submit();
            }
        });

        if (selectedId) {
            var found = findById(selectedId);
            if (found) {
                selectDivision(found.division, found.item.id);
            }
        }

        window.setDepartmentDivisionSelection = function(id) {
            var found = findById(id);
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
})();
