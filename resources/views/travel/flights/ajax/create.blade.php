<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-sm-0">
                        <div class="ct-header-icon mr-3">
                            <i class="fa fa-plane"></i>
                        </div>
                        <h4 class="mb-0 f-21 font-weight-normal">@lang('app.flight')</h4>
                    </div>
                    <span id="flight-route-badge" class="badge badge-light f-12"></span>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="flight_number" :fieldLabel="__('modules.flight.flightNumber')" fieldName="flight_number" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="airline_id" :fieldLabel="__('app.airline')" fieldName="airline_id">
                            <option value="">--</option>
                            @foreach($airlines as $airline)
                                <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-6 mt-3 position-relative">
                        <x-forms.text fieldId="origin" :fieldLabel="__('app.origin')" fieldName="origin" fieldRequired="true"></x-forms.text>
                        <i class="fa fa-long-arrow-alt-right ct-route-arrow d-none d-md-block"></i>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.text fieldId="destination" :fieldLabel="__('app.destination')" fieldName="destination" fieldRequired="true"></x-forms.text>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.time fieldId="departure_time" :fieldLabel="__('modules.flight.departureTime')" fieldName="departure_time"></x-forms.time>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.time fieldId="arrival_time" :fieldLabel="__('modules.flight.arrivalTime')" fieldName="arrival_time"></x-forms.time>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('flights.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<style>
    .ct-card { animation: ctFadeIn .2s ease-out; }
    @keyframes ctFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ct-header-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .08);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    #save-form {
        transition: transform .12s ease, box-shadow .12s ease;
    }
    #save-form:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
    }
    .ct-route-arrow {
        position: absolute;
        right: -22px;
        top: 38px;
        color: #adb5bd;
        font-size: 14px;
        z-index: 1;
    }
    #flight-route-badge:empty {
        display: none;
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('flights.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: shows "ORIGIN → DESTINATION · dep–arr" as you type.
    // Purely a read-only summary — never blocks or alters submission.
    (function() {
        var $origin = $('#origin');
        var $destination = $('#destination');
        var $dep = $('#departure_time');
        var $arr = $('#arrival_time');
        var $badge = $('#flight-route-badge');

        function updateBadge() {
            var o = $.trim($origin.val());
            var d = $.trim($destination.val());
            var dep = $.trim($dep.val());
            var arr = $.trim($arr.val());

            if (o === '' && d === '') {
                $badge.text('');
                return;
            }
            var text = (o || '?') + ' → ' + (d || '?');
            if (dep || arr) {
                text += '  ·  ' + (dep || '?') + '–' + (arr || '?');
            }
            $badge.text(text.toUpperCase());
        }

        $origin.add($destination).add($dep).add($arr).on('input change', updateBadge);
        updateBadge();
    })();
</script>