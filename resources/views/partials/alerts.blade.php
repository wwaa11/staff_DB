@php
    $flashSuccess = session('success');
    $flashError = session('error');
    $flashValidation = $errors->any() ? $errors->first() : null;
@endphp
@if ($flashSuccess || $flashError || $flashValidation)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if ($flashSuccess)
                    AppAlert.success(@json($flashSuccess));
                @elseif ($flashError)
                    AppAlert.error(@json($flashError));
                @elseif ($flashValidation)
                    AppAlert.error(@json($flashValidation));
                @endif
            });
        </script>
    @endpush
@endif
