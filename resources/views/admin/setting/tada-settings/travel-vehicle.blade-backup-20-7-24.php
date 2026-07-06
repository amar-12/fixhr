@extends('admin.layout.master')

@section('title', 'Travel Vehicle')

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Travel Vehicle</b></span></li>
        </ol>
    </div>
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Travel Vehicle</div>
            <p class="text-muted">Add and activate Travel Vehicle</p>
        </div>
    </div>
    {{-- @php
        dd($policyCategory);
    @endphp --}}
    <div class="card">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Travel Vehicle List</span></h4>
            </div>

            <div class="ms-auto">
                <button class="btn text-white btn-info btn-sm" id="addTravelVehicleFieldBtn">
                    <i class="fe fe-plus bold"></i></button>
            </div>
        </div>
        <!-- Add this line to your blade file to check if errors exist -->

        <div class="card-body">

            <form method="POST" id="addTravelVehicleForm">
                @csrf

                <div class="card-body">
                    <div id="appendContainer"></div>

                </div>
                <div class="d-flex justify-content-end">
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary " id="saveUptBtn">Save & Update</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

@endsection


@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function(){
            var count = 1;
            //addTravelVehicle(count);

            @if(count($travelVehicles))
                @foreach($travelVehicles as $index => $tv)
                    addTravelVehicle({{$index + 1}}, {
                        tv_id: '{{$tv->pttv_id}}',
                        tt_id: '{{$tv->fh_policy_tada_travel_mode->pttm_pttt_id}}',
                        travel_mode_id: '{{$tv->pttv_pttm_id}}',
                        vehicle_id: '{{$tv->pttv_vehicle_id}}',
                        vehicle_class_id: '{{$tv->pttv_class_id}}',
                        vehicle_owner_id: '{{$tv->pttv_owner_id}}',
                        claim_type: '{{$tv->pttv_claim_type_id}}'
                    });
                    count = {{ $index + 1 }};
                @endforeach
            @else
                addTravelVehicle(count, data={});
            @endif

            function addTravelVehicle(count, data={})
            {
                html = `<div class="row appendTravelVehicleFieldRow">
                    <div class="col-md-12 col-xl-4">
                        <input hidden value="${data.tv_id}" id="travel_vehicle_id_${count}" name="travel_vehicle_id[]">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Select Travel Type:</label>
                                    <select name="travel_type_id[]" class="form-control custom-select select2 travelType" id="travel_type_id_${count}" data-count="${count}" data-placeholder="Travel Type" required>
                                        <option label="Travel Type"></option>
                                        @foreach ($travelTypes as $ttype)
                                        <option value="{{$ttype->pttt_id}}" ${data.tt_id == '{{$ttype->pttt_id}}' ? 'selected' : ''}>{{$ttype->fh_travel_type->m_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Select Mode:</label>
                                    <select name="travel_mode_id[]" id="travel_mode_${count}" class="form-control custom-select select2 traveMode" data-count="${count}" data-placeholder="Select Travel Mode" required>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label">Select Vehicle:</label>
                            <select name="vehicle_id[]" id="vehicle_${count}" class="form-control custom-select select2 vehicle" data-count="${count}" data-placeholder="Select Vehicle" required>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label">Select Claim Type:</label>
                            <select name="claim_type[]" id="claim_type_${count}" class="form-control custom-select select2" data-count="${count}" data-placeholder="Select Claim Type" required>
                                <option label="Claim Type"></option>
                                <option value="154" ${data.claim_type == '154' ? 'selected' : ''}>Actual</option>
                                <option value="155" ${data.claim_type == '155' ? 'selected' : ''}>Policy</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-3">
                        <div class="row">
                            <div class="" id="vehicle_class_div_${count}">
                            </div>

                            <div class="" id="vehicle_owner_div_${count}">
                            </div>
                        </div>
                    </div>`;

                    if (Object.keys(data).length === 0 && data.constructor === Object && count >= 1)
                    {
                        html += `<div class="col-md-12 col-xl-1">
                            <div class="form-group mt-4">
                                <button type="button" class="btn btn-outline-danger  btn-sm mt-3 float-right removeTravelVehicleFieldBtn">
                                    <i class="feather feather-trash"></i>
                                </button>
                            </div>
                        </div>`;
                    }
                html += `</div>`
                $('#appendContainer').append(html);
                $('.select2').select2();

                if (data.travel_mode_id) {
                    populateModeDropdown(data.tt_id, count, data.travel_mode_id);
                }

                if (data.vehicle_id) {
                    populateVehicleDropdown(data.travel_mode_id, count, data.vehicle_id);
                }

                if (data.vehicle_id) {
                    getVehicleClassAndOwner(data.vehicle_id, count, data.vehicle_class_id, data.vehicle_owner_id);
                }
            }

            $(document).on('click', '#addTravelVehicleFieldBtn', function(){
                count++;
                addTravelVehicle(count);
            });

            $(document).on('click', '.removeTravelVehicleFieldBtn', function(){
                count--;
                $(this).closest('.appendTravelVehicleFieldRow').remove();
            });
        });


        $(document).on('change', '.travelType', function() {
            var travelTypeId = $(this).val();
            var rowCount = $(this).data('count');
            populateModeDropdown(travelTypeId, rowCount);
        });

        $(document).on('change', '.traveMode', function() {
            var selectedMode = $(this).val();
            var rowCount = $(this).data('count');
            populateVehicleDropdown(selectedMode, rowCount);
        });

        $(document).on('change', '.vehicle', function() {
            var vehicle_id = $(this).val();
            var vehicle_mode = $(this).find('option:selected').data('vehicle_mode');
            var rowCount = $(this).data('count');

            getVehicleClassAndOwner(vehicle_id, rowCount);
        });

        function populateModeDropdown(travelTypeId, rowCount, selectedModeId = null) {
            $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'TRAVEL_MODE',
                    travelType:  travelTypeId,
                },
                dataType: 'json',
                cache: true,
                success: function(data) {
                    if(data.status == true){
                        $('#travel_mode_'+rowCount).html('');
                        $('#vehicle_class_'+rowCount).html('');
                        var defaultOption = $('<option>').val('').text('Select Travel Mode').attr('selected', true);
                        $('#travel_mode_'+rowCount).append(defaultOption);
                        $('#travel_mode_'+rowCount).attr('required', true);
                        data.result.forEach(function(element) {
                            var option = $('<option>').val(element.pttm_id).text(element.fh_travel_mode.m_name);
                            if (selectedModeId && selectedModeId == element.pttm_id) {
                                option.attr('selected', 'selected');
                            }
                            $('#travel_mode_' + rowCount).append(option);
                        });
                    }else{
                        $('#travel_mode_'+rowCount).html('');
                    }
                },
                error: function(error) {
                    console.error('Error fetching travel mode:', error);
                }
            });
        }

        function populateVehicleDropdown(modeTypeId, rowCount, selectedVehicleId = null) {
            $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'VEHICLE',
                    mode_type_id:  modeTypeId,
                },
                dataType: 'json',
                cache: true,
                success: function(data) {
                    if(data.status == true){
                        $('#vehicle_class_'+rowCount).html('');
                        $('#vehicle_class_div_'+rowCount).html('');
                        $('#vehicle_owner_div_'+rowCount).html('');
                        $('#vehicle_'+rowCount).html('');
                        var defaultOption = $('<option>').val('').text('Select Vehicle').attr('selected', true);
                        $('#vehicle_'+rowCount).append(defaultOption);
                        $('#vehicle_'+rowCount).attr('required', true);
                        data.result.forEach(function(element) {
                            var option = $('<option>').val(element.m_id).text(element.m_name).attr('data-vehicle_mode', element.m_description);
                            if (selectedVehicleId && selectedVehicleId == element.m_id) {
                                option.attr('selected', 'selected');
                            }
                            $('#vehicle_'+rowCount).append(option);
                        });
                        // if (selectedVehicleId) {
                        //     getVehicleClassAndOwner(selectedVehicleId, rowCount, selectedClassId, selectedOwnerId);
                        // }
                    }
                },
                error: function(error) {
                    console.error('Error fetching vehicle:', error);
                }
            });
        }

        function getVehicleClassAndOwner(vehicle_id, rowCount, selectedClassId = null, selectedOwnerId = null) {
            return $.ajax({
                url: "{{ route('admin.get.travel.vehicle') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    REQUEST_TYPE: 'TRAVEL_CLASS_TRAVEL_OWNER',
                    vehicle_id:  vehicle_id,
                },
                dataType: 'json',
                cache: true,
            }).then(data => {
                if(data.status == true){
                    $('#vehicle_class_div_'+rowCount).html('');
                    $('#vehicle_owner_div_'+rowCount).html('');

                    if (data.result.some(item => item.m_group == 'TRAVEL_CLASS')) {
                        $('#vehicle_owner_div_'+rowCount).removeClass('col-6');
                        $('#vehicle_class_div_'+rowCount).addClass('col-6');
                        var classSelectHtml = `<div class="form-group">
                                <label class="form-label">Select Vehicle Class:</label>
                                <select name="vehicle_class_id[]" id="vehicle_class_${rowCount}" class="form-control custom-select select2 vehicleClasss" data-count="${rowCount}" data-placeholder="Select Vehicle Class" required>
                                    <option label="Vehicle Class"></option>
                                </select>
                            </div>`;
                        $('#vehicle_class_div_'+rowCount).append(classSelectHtml);
                        data.result.forEach(function(element) {
                            if (element.m_group == 'TRAVEL_CLASS') {
                                var option = $('<option>').val(element.m_id).text(element.m_name);
                                if (selectedClassId && selectedClassId == element.m_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#vehicle_class_'+rowCount).append(option);
                            }
                        });
                    }

                    if (data.result.some(item => item.m_group == 'VEHICLE_OWNER')) {
                        $('#vehicle_class_div_'+rowCount).removeClass('col-6');
                        $('#vehicle_owner_div_'+rowCount).addClass('col-6');
                        var ownerSelectHtml = `<div class="form-group">
                                <label class="form-label">Select Vehicle Owner:</label>
                                <select name="vehicle_owner_id[]" id="vehicle_owner_${rowCount}" class="form-control custom-select select2 vehicleOwner" data-count="${rowCount}" data-placeholder="Select Vehicle Owner" required>
                                    <option label="Vehicle Owner"></option>
                                </select>
                            </div>`;
                        $('#vehicle_owner_div_'+rowCount).append(ownerSelectHtml);
                        data.result.forEach(function(element) {
                            if (element.m_group == 'VEHICLE_OWNER') {
                                var option = $('<option>').val(element.m_id).text(element.m_name);
                                if (selectedOwnerId && selectedOwnerId == element.m_id) {
                                    option.attr('selected', 'selected');
                                }
                                $('#vehicle_owner_'+rowCount).append(option);
                            }
                        });
                    }
                    $('.select2').select2();
                }else{
                    $('#vehicle_class_div_'+rowCount).html('');
                    $('#vehicle_owner_div_'+rowCount).html('');
                    // $('#vehicle_class_div_'+rowCount).html('<option value="" selected>Select Vehicle Class</option>');
                    // $('#vehicle_owner_div_'+rowCount).html('<option value="" selected>Select Vehicle Owner</option>');
                }
            }).catch(error => {
                console.error('Error fetching vehicle class and owner:', error);
            });
        }

        $('#appendContainer').on('change', '.travelType, .traveMode, .vehicle, .vehicleClass, .vehicleOwner', function() {
            checkForDuplicatesAndAlert();
        });

        function checkForDuplicatesAndAlert() {
            var count = $('.travelType').length;
            var duplicateIndices = [];
            var isDuplicate = false;
            var combinationSet = new Set();

            for(var i = 1; i <= count; i++) {
                var mode = $('#travel_mode_' + i).val();
                var vehicle = $('#vehicle_' + i).val();
                var vehicleClass = $('#vehicle_class_' + i).val() || null;
                var owner = $('#vehicle_owner_' + i).val() || null;

                var combination = `${mode}-${vehicle}-${vehicleClass}-${owner}`;

                if (combinationSet.has(combination)) {
                    isDuplicate = true;
                    duplicateIndices.push(i);
                }
                combinationSet.add(combination);
            }

            if (isDuplicate) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Duplicate combination of Mode, Vehicle, Vehicle Class, and Vehicle Owner found.'
                });

                duplicateIndices.forEach(function(index) {
                    $('#travel_type_id_' + index).val('').trigger('change');
                    $('#travel_mode_' + index).val('').trigger('change');
                    $('#vehicle_' + index).val('').trigger('change');
                    $('#vehicle_class_' + index).val('').trigger('change');
                    $('#vehicle_owner_' + index).val('').trigger('change');
                    $('#claim_type_' + index).val('').trigger('change');
                });
                return false;
            }
            return true;
        }

        $('#addTravelVehicleForm').on('submit', function(event){
            if (!checkForDuplicatesAndAlert()) {
                // If duplicates found, prevent form submission
                event.preventDefault();
                return false;
            }

            var count = $('.travelType').length;
            var data = [];

            for (var i = 1; i <= count; i++) {
                data.push({
                    'tv_id': $('#travel_vehicle_id_' + i).val(),
                    'mode': $('#travel_mode_' + i).val(),
                    'vehicle': $('#vehicle_' + i).val(),
                    'class': $('#vehicle_class_' + i).val() || null,
                    'owner': $('#vehicle_owner_' + i).val() || null,
                    'claim_type': $('#claim_type_' + i).val(),
                });
            }
            event.preventDefault();
            $.ajax({
                url: '{{ route("admin.save.travel.vehicle") }}',
                method: 'POST',
                data: {
                    _token : '{{ csrf_token() }}',
                    data : data
                },
                dataType: 'json',
                beforeSend:function() {
                    $('#saveUptBtn').attr('disabled','disabled');
                },
                success:function(data) {
                    if(data.status == true){
                        Swal.fire({
                            icon: 'success',
                            text: data.message,
                        });
                    } else{
                        Swal.fire({
                            icon: 'warning',
                            text: data.message,
                        });
                    }
                    $('#saveUptBtn').attr('disabled', false);
                    window.location.href = '{{ route('admin.travel.vehicle') }}';
                },error: function (xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        text: error,
                    });
                }
            });
        });
    </script>
@endsection
