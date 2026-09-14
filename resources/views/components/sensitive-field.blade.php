@props([
    'passengerId',
    'field',         // 'passport_no' | 'birth_date'
    'masked',        // pre-masked value e.g. "AB*****CD"
    'value',         // optional real value if already known (will skip the reveal action)
])

@php
    use Illuminate\Support\Str;
    $elementId = 'sf-' . Str::random(8);
@endphp

<span
    class="sensitive-field d-inline-flex align-items-center"
    id="{{ $elementId }}"
    data-passenger-id="{{ $passengerId }}"
    data-field="{{ $field }}"
    data-reveal-url="{{ route('sensitive-fields.reveal') }}"
>
    <span class="sf-value font-monospace" data-state="masked">
        {{ $value !== null && $value !== '' ? $value : $masked }}
    </span>
    @if($value === null || $value === '')
        <button
            type="button"
            class="sf-reveal btn btn-link btn-sm p-0 ml-1 align-baseline"
            title="@lang('app.clickToReveal')"
            aria-label="@lang('app.clickToReveal')"
        >
            <i class="fa fa-eye"></i>
        </button>
    @endif
</span>

@once
    @push('scripts')
    <script>
    $(document).on('click', '.sf-reveal', function(e) {
        e.preventDefault();
        const $wrap  = $(this).closest('.sensitive-field');
        const id     = $wrap.data('passenger-id');
        const field  = $wrap.data('field');
        const url    = $wrap.data('reveal-url');
        const $value = $wrap.find('.sf-value');

        if ($value.data('state') === 'visible') return;

        $.easyAjax({
            url: url,
            type: 'GET',
            data: { model: 'passenger', id, field },
            blockUI: true,
            success: function(response) {
                if (response.status === 'success' && response.data && response.data.value !== undefined) {
                    $value
                        .text(response.data.value)
                        .data('state', 'visible')
                        .removeClass('font-monospace');
                    $wrap.find('.sf-reveal').remove();
                } else {
                    Swal.fire('Error', response.message || 'Could not reveal field', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Reveal failed', 'error');
            }
        });
    });
    </script>
    @endpush
@endonce
