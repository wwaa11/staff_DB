@php
    $type = $type ?? 'search';
    $placeholder = $placeholder ?? 'ค้นหา';
    $value = $value ?? '';
    $wrapperClass = $wrapperClass ?? '';
    $inputClass = $inputClass ?? '';
    $attrs = $attrs ?? '';
@endphp
<label class="input input-bordered search-field w-full {{ $wrapperClass }}">
    <svg class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
            d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
    </svg>
    <input type="{{ $type }}" class="{{ $inputClass }}"
        @isset($id) id="{{ $id }}" @endisset
        @isset($name) name="{{ $name }}" @endisset
        value="{{ $value }}" placeholder="{{ $placeholder }}" data-clearable autocomplete="off"
        {!! $attrs !!}>
    <button type="button" class="search-clear" hidden aria-label="ล้างข้อความ">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</label>
