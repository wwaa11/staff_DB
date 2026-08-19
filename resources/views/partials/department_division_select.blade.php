<script type="application/json" id="department-data">{!! json_encode($groupedDepartments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
<div class="department-division-select grid gap-4 md:grid-cols-2" data-selected-id="{{ $selectedId ?? '' }}"
    data-auto-submit="{{ ! empty($autoSubmit) ? '1' : '0' }}">
    <div>
        <label class="label" for="division-search">ฝ่าย</label>
        <div class="relative">
            <input type="text" class="input" id="division-search" data-division-search autocomplete="off"
                placeholder="ค้นหาชื่อฝ่าย" aria-label="ค้นหาฝ่าย">
            <div class="suggest-list" data-division-suggest role="listbox"></div>
        </div>
    </div>
    <div>
        <label class="label" for="department-select">แผนก</label>
        <select class="select" id="department-select" name="department_id" data-department-select
            aria-label="เลือกแผนก">
            <option value="">เลือกแผนก</option>
        </select>
    </div>
</div>
