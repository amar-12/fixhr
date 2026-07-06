@extends('admin.layout.master')
@section('title')
    Travel Mode
@endsection

@section('content')
    <div>
        <div class="p-0 pt-md-2">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                <li class="active"><span><b>Travel Mode</b></span></li>
            </ol>
        </div>
        <div class="page-header d-md-flex d-block pt-2 pt-md-0">
            <div class="page-leftheader">
                <div class="page-title">Travel Mode</div>
                <p class="text-muted">Activate or deactivate your mode of travel</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="travel-form">
                @foreach ($travelTypes as $pttm)
                    <div class="form-group">
                        <div class="d-flex justify-content-between">
                            <div class="my-auto">
                                <a class="font-weight-semibold fs-18 ms-3">{{ $pttm->fh_travel_type->m_name }}</a>
                            </div>
                            <div class="d-flex">
                                <label class="custom-switch ms-auto">
                                    <input type="checkbox" class="custom-switch-input travelTypeCheckbox"
                                        name="travel_type[{{ $pttm->pttt_id }}]" value="{{ $pttm->pttt_id }}"
                                        {{ in_array($pttm->pttt_id, $travelModeToggle) ? 'checked' : '' }}>
                                    <span class="custom-switch-indicator"></span>
                                </label>
                            </div>
                        </div>
                        <div class="sub-options m-5"
                            style="display: {{ in_array($pttm->pttt_id, $travelModeToggle) ? 'block' : 'none' }}">
                            @foreach ($travelModes as $key2 => $item2)
                                @php
                                    $arrayTravelMode = $travelModeStoredData
                                        ->where('pttm_pttt_id', $pttm->pttt_id)
                                        ->where('pttm_status', 1)
                                        ->pluck('pttm_by_mode_id')
                                        ->toArray();
                                    $idtravelmode = $travelModeStoredData
                                        ->where('pttm_pttt_id', $pttm->pttt_id)
                                        ->where('pttm_by_mode_id', $key2)
                                        ->select('pttm_id')
                                        ->first();
                                @endphp
                                <div class="row">
                                    <label>
                                        <input type="checkbox" name="by_mode[{{ $pttm->pttt_id }}][]" value="{{ $key2 }}"
                                            data-idtravelmode="{{ $idtravelmode ? $idtravelmode['pttm_id'] : null }}"
                                            {{ is_array($arrayTravelMode) && in_array($key2, $arrayTravelMode) ? 'checked' : '' }}>
                                        {{ $item2 }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="d-flex justify-content-end">
                    <button type="submit" id="travelModeSubmitBtn" class="btn btn-outline-primary">Save & Continue</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script>
        $(document).ready(function() {
            "{{url('admin/settings/tada-settings')}}";
            var travelModeStoredDatas = @json($travelModeStoredData);
            var countValue = Array.isArray(travelModeStoredDatas) ? travelModeStoredDatas.length : Object.keys(
                travelModeStoredDatas).length;
            $('input[name^="travel_type"]').change(function() {
                var id = $(this).val();
                var isChecked = $(this).is(':checked');
                $(this).closest('.form-group').find('.sub-options').toggle(isChecked);
                // If checked, ensure at least one by_mode checkbox is checked
                if (isChecked) {
                    var subOptions = $(this).closest('.form-group').find('.sub-options');
                    var atLeastOneChecked = subOptions.find('input[name^="by_mode"]:checked').length > 0;
                    if (!atLeastOneChecked) {
                        // subOptions.find('input[name^="by_mode"]').first().prop('checked', true);
                    }
                }
            });

            $('#travel-form').submit(function(e) {
                e.preventDefault();
                $('#travelModeSubmitBtn').prop('disabled', true);
                var dataToSend = {};
                var isValid = true;
                var atLeastOneTravelTypeChecked = false;
                $('input[name^="travel_type"]').each(function() {
                    var id = $(this).val();
                    var status = $(this).is(':checked') ? 1 : 0;
                    var by_mode = [];
                    var pid = [];
                    if ($(this).is(':checked')) {
                        atLeastOneTravelTypeChecked = true;
                    }

                    $(this).closest('.form-group').find('input[name^="by_mode"]:checked').each(
                        function() {
                            var idtravelmode = $(this).data('idtravelmode');
                            pid.push(idtravelmode);
                            by_mode.push($(this).val());
                        }
                    );

                    if ($(this).is(':checked') && by_mode.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            text: "Please select at least one option if Travel Mode On",
                            timer: 3000,
                        });
                        $('#travelModeSubmitBtn').prop('disabled', false);
                        // alert('Please select at least one option for ' + $(this).closest(
                        //     '.form-group').find('label').text().trim());
                        isValid = false;
                        return false; // Break out of each loop
                    }
                    dataToSend[id] = {
                        status: status,
                        by_mode: by_mode,
                        pid: pid
                    };

                });

                if (!atLeastOneTravelTypeChecked && countValue == 0) {
                    Swal.fire({
                        icon: 'error',
                        text: "Please enable at least one Travel Mode",
                        timer: 3000,
                    });
                    $('#travelModeSubmitBtn').prop('disabled', false);
                    isValid = false;
                    return false;
                }

                if (isValid) {
                    $.ajax({
                        url: '{{ route('admin.save.travel.modes') }}',
                        method: 'POST',
                        data: {
                            travel_data: dataToSend,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                text: response.success,
                                timer: 3000,
                            });
                            window.location.href = "{{ url('admin/settings/tada-settings/travel-modes') }}";

                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'error',
                                text: "Failed to update data",
                                timer: 3000,
                            });
                            $('#travelModeSubmitBtn').prop('disabled', false);
                            window.location.href = "{{ url('admin/settings/tada-settings/travel-modes') }}";
                        }
                    });
                }
            });
        });
    </script>
@endsection
