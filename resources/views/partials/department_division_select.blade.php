<script type="application/json" id="department-data">{!! json_encode($groupedDepartments, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
<div class="department-division-select grid gap-4 md:grid-cols-2"
    data-selected-id="{{ $selectedId ?? '' }}"
    data-selected-division="{{ $selectedDivision ?? '' }}"
    data-has-division="{{ ! empty($hasDivisionFilter) ? '1' : '0' }}"
    data-auto-submit="{{ ! empty($autoSubmit) ? '1' : '0' }}"
    data-allow-empty-department="{{ ! empty($allowEmptyDepartment) ? '1' : '0' }}"
    data-empty-department-label="{{ $emptyDepartmentLabel ?? 'เลือกแผนก' }}">
    <input type="hidden" name="division" value="{{ $selectedDivision ?? '' }}" data-division-value @if (empty($hasDivisionFilter)) disabled @endif>
    <div>
        <label class="field-label" for="division-search">ฝ่าย</label>
        <div class="relative z-30">
            @include('partials.search_input', [
                'id' => 'division-search',
                'placeholder' => 'ค้นหาชื่อฝ่าย',
                'attrs' => 'data-division-search aria-label="ค้นหาฝ่าย"',
            ])
            <div class="suggest-list" data-division-suggest role="listbox"></div>
        </div>
    </div>
    <div>
        <label class="field-label" for="department-select">แผนก</label>
        <select class="select select-bordered w-full" id="department-select" name="department_id"
            data-department-select aria-label="เลือกแผนก">
            <option value="">{{ $emptyDepartmentLabel ?? 'เลือกแผนก' }}</option>
        </select>
    </div>
</div>
