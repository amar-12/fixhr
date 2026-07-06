@extends('admin.layout.master')

@section('title', 'Travel Allowance')

@section('content')

    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Travel Allowance</b></span></li>
        </ol>
    </div>
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Travel Allowance</div>
            <p class="text-muted">Create and activate Travel Allowance</p>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Travel Allowance List</span></h4>
            </div>
            <div class="ms-auto">
                <button type="button" name="add" id="add" class="btn btn-info btn-sm"><i
                        class="fe fe-plus bold"></i></button>
            </div>
        </div>

        <form id="travelAllowanceForm" action="{{ route('creatOrUpdate.travel.allowance') }}" method="POST">
            <div class="card-body pt-0 pb-2 px-3">
                @csrf
                <div class="table-responsive">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <tr>
                                <th>Policy Category</th>
                                <th>Travel Type</th>
                                <th>Travel Mode</th>
                                <th>Travel Vehicle List</th>
                                <th>Eligibility Amount/Km</th>
                                <th>Remarks</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $index => $row)
                                <tr>
                                    <td>
                                        <select name="dynamic[{{ $index }}][ptta_ptc_id]" class="form-control form-select policy-category" required>
                                            <option value="">Select Policy Category</option>
                                            @foreach ($policyCategory as $item)
                                                <option value="{{ $item->ptc_id }}"
                                                    {{ $item->ptc_id == $row->ptta_ptc_id ? 'selected' : '' }}>
                                                    {{ $item->ptc_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td>
                                        <select name="dynamic[{{ $index }}][ptta_pttt_id]" class="form-control form-select travel-type" required>
                                            <option value="">Select Travel Type</option>
                                            @foreach ($travelType as $item)
                                                <option value="{{ $item->pttt_id }}"
                                                    {{ $item->pttt_id == $row->ptta_pttt_id ? 'selected' : '' }}>
                                                    {{ $item->fh_travel_type->m_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td>
                                        <select name="dynamic[{{ $index }}][ptta_pttm_id]" class="form-control form-select travel-mode" required>
                                            <option value="">Select Travel Mode</option>
                                            <!-- Options will be populated dynamically based on selected Travel Type -->
                                            @foreach ($travelMode as $item)
                                                @if ($item->pttm_pttt_id == $row->ptta_pttt_id)
                                                    <option value="{{ $item->pttm_id }}"
                                                        {{ $item->pttm_id == $row->ptta_pttm_id ? 'selected' : '' }}>
                                                        {{ $item->fh_travel_mode->m_name }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td>
                                        <select name="dynamic[{{ $index }}][ptta_pttv_id]" class="form-control form-select vehicle-type" required>
                                            <option value="">Select Vehicle List</option>
                                            @foreach ($vehicleList as $item)
                                                @if ($item->pttv_pttm_id == $row->ptta_pttm_id)
                                                    <option value="{{ $item->pttv_id }}" {{ $item->pttv_id == $row->ptta_pttv_id ? 'selected' : '' }}>
                                                        ({{ isset($item->fh_vehicle->m_name) ? $item->fh_vehicle->m_name : '' }}
                                                         - {{ isset($item->fh_travel_class->m_name) ? $item->fh_travel_class->m_name : '' }}
                                                         {{ isset($item->fh_vehicle_owner->m_name) ? $item->fh_vehicle_owner->m_name : '' }}
                                                         - {{ isset($item->fh_claim_type->m_name) ? $item->fh_claim_type->m_name : '' }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td>
                                        <input type="number" name="dynamic[{{ $index }}][ptta_eligibility]" placeholder="{{($row->fh_claim_type()->exists() && $row->fh_claim_type->m_id == 154) ?  $row->fh_claim_type->m_name : 'Enter Eligibility'}}" class="form-control " value="{{$row->ptta_eligibility }}" {{($row->fh_claim_type()->exists() && $row->fh_claim_type->m_id == 154) ?  'readOnly' : ''}} required />
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td>
                                        <input type="text" name="dynamic[{{ $index }}][ptta_remarks]" placeholder="Enter Remarks" class="form-control" value="{{ $row->ptta_remarks }}" />
                                        <div class="invalid-feedback">This field is required.</div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-outline-danger  remove-tr btn-sm" data-row-id="{{ $index }}"><i class="feather feather-trash"></i></button>
                                        <input type="hidden" name="dynamic[{{ $index }}][ptta_id]" value="{{ $row->ptta_id }}" />
                                        <input type="hidden" name="dynamic[{{ $index }}][_delete]" value="0" class="delete-marker" />
                                        <input type="hidden" name="dynamic[{{ $index }}][_index]" value="{{ $index }}" />
                                    </td>
                                    <div class="duplicated-error"></div>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-2 text-danger" id="duplicated_id"></div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <button id="sumbitButton" type="submit" class="btn btn-outline-primary">Save & Continue</button>
            </div>
        </form>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            var i = {{ count($data) }};


            $('#add').click(function() {
                var newRow = `
                    <tr>
                        <td>
                            <select name="dynamic[${i}][ptta_ptc_id]" class="form-control form-select policy-category" required>
                                <option value="">Select Policy Category</option>
                                @foreach ($policyCategory as $item)
                                    <option value="{{ $item->ptc_id }}">{{ $item->ptc_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td>
                            <select name="dynamic[${i}][ptta_pttt_id]" class="form-control form-select travel-type" required>
                                <option value="">Select Travel Type</option>
                                @foreach ($travelType as $item)
                                    <option value="{{ $item->pttt_id }}">{{ $item->fh_travel_type->m_name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td>
                            <select name="dynamic[${i}][ptta_pttm_id]" class="form-control form-select travel-mode" required>
                                <option value="">Select Travel Mode</option>
                                <!-- Options will be populated dynamically based on selected Travel Type -->
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td>
                            <select name="dynamic[${i}][ptta_pttv_id]" class="form-control form-select vehicle-type" required>
                                <option value="">Select Vehicle List</option>
                            </select>
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td>
                            <input type="number" name="dynamic[${i}][ptta_eligibility]" class="form-control"
                                placeholder="Enter Eligibility" required />
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td>
                            <input type="text" name="dynamic[${i}][ptta_remarks]" class="form-control"
                                placeholder="Enter Remarks" />
                            <div class="invalid-feedback">This field is required.</div>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-outline-danger  remove-tr btn-sm"><i
                                    class="feather feather-trash"></i></button>
                            <input type="hidden" name="dynamic[${i}][_delete]" value="0" class="delete-marker" />
                            <input type="hidden" name="dynamic[${i}][_index]" value="${i}" />
                        </td>
                    </tr>
                `;
                $('#dynamicTable tbody').append(newRow);
                i++;
            });

            if(i==0){
                $('#add').trigger('click');
            }

            $(document).on('click', '.remove-tr', function() {
                var rowIndex = $(this).data('row-id');
                $('input[name="dynamic[' + rowIndex + '][_delete]"]').val(1);
                $(this).closest('tr').remove();
            });

            $(document).on('change', '.travel-type', function() {
                var $row = $(this).closest('tr');
                var travelTypeId = $(this).val();
                var url = '{{ route('getTravelModes', ':id') }}';
                url = url.replace(':id', btoa(travelTypeId));
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        var options = '<option value="">Select Travel Mode</option>';
                        response.forEach(function(item) {
                            options += `<option value="${item.pttm_id}">${item.fh_travel_mode.m_name}</option>`;
                        });
                        $row.find('select[name*="ptta_pttm_id"]').html(options);
                    }
                });
            });

            $(document).on('change', '.travel-mode', function() {
                var $row = $(this).closest('tr');
                var travelMode = $(this).val();
                $.ajax({
                    url: "{{ route('get.travelLists') }}",
                    type: "GET",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id:  btoa(travelMode),
                        REQUEST_TYPE: 'GET_VEHICLE',
                    },
                    dataType: 'json',
                    cache: true,
                    success: function(data) {
                        var vehicleSelect = $row.find('select[name*="[ptta_pttv_id]"]');
                        vehicleSelect.empty().append('<option value="">Select Vehicle List</option>');
                        $.each(data, function(key, value) {
                            vehicleSelect.append('<option value="' + value.pttv_id + '">' + value.name + '</option>');
                        });
                    }
                });
            });

            $(document).on('change', '.vehicle-type', function() {
                var $row = $(this).closest('tr');
                var vehicleTypeId = $(this).val();
                $.ajax({
                    url: "{{ route('get.travelLists') }}",
                    type: "GET",
                    data: {
                        _token: '{{ csrf_token() }}',
                        id:  btoa(vehicleTypeId),
                        REQUEST_TYPE: 'GET_VEHICLE_TYPE',
                    },
                    dataType: 'json',
                    cache: true,
                    success: function(data) {
                        var eligibility = $row.find('[name*="[ptta_eligibility]"]');
                        if(data.vehicleClaimId == 154){ // 154 == 'By Actual'
                            eligibility.prop('readonly', true);
                            eligibility.attr('placeholder', 'By Actual');
                            eligibility.val('');
                        } else {
                            eligibility.prop('readonly', false);
                            eligibility.attr('placeholder', 'Enter Eligibility');
                            eligibility.val('');
                        }
                    }
                });
            });

            $('#travelAllowanceForm').submit(function(e) {
                e.preventDefault(); // Prevent default form submission
                $('#sumbitButton').prop('disabled', true);
                var formData = $(this).serializeArray();
                var isValid = true;
                // Create an array to track combinations and errors
                var combinations = [];
                var errorRows = [];
                if (formData.length === 1) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Data is empty',
                        timer: 3000,
                    }).then(() => {
                        $('#sumbitButton').prop('disabled', false);
                    });
                    return;
                }

                // Iterate through form data
                formData.forEach(function(field) {
                    if (field.name.includes('dynamic') && field.name.includes('ptta_ptc_id')) {
                        var currentRow = $('[name="' + field.name + '"]').closest('tr');
                        var currentRowIndex = currentRow.index();
                        var policyCategoryId = field.value;
                        var travelTypeId = $('[name="dynamic[' + currentRowIndex + '][ptta_pttt_id]"]').val();
                        var travelModeId = $('[name="dynamic[' + currentRowIndex + '][ptta_pttm_id]"]').val();
                        var travelVehicleId = $('[name="dynamic[' + currentRowIndex + '][ptta_pttv_id]"]').val();
                        var combination = policyCategoryId + '-' + travelTypeId + '-' + travelModeId + '-' + travelVehicleId;
                        // Check if combination already exists in the combinations array
                        if (combinations.includes(combination)) {
                            isValid = false;
                            errorRows.push(currentRowIndex);
                        } else {
                            combinations.push(combination);
                        }
                    }
                });

                // Display errors for rows with duplicate combinations
                $('#dynamicTable tbody tr').each(function(index) {
                    if (errorRows.includes(index)) {
                        // $(this).css('border', '2px solid red');
                        // $(this).find('.policy-category, .travel-type, .travel-mode')
                        //     .addClass('is-invalid');
                        // $(this).find('.invalid-feedback').show().text(
                        //     'Combination already exists.');
                        document.getElementById('duplicated_id').innerHTML =
                            "Duplicate combinations of Policy Category, Travel Type, and Travel Mode have been detected.";
                        $('#sumbitButton').prop('disabled', false);
                    } else {
                        document.getElementById('duplicated_id').innerHTML = '';
                        $(this).find('.policy-category, .travel-type, .travel-mode').removeClass('is-invalid');
                        $(this).find('.invalid-feedback').hide().text('This field is required.');
                    }
                });

                if (isValid) {
                    e.preventDefault();
                    $.ajax({
                        url: $(this).attr('action'),
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    text: response.message,
                                    timer: 3000,
                                }).then(() => {
                                    // Redirect the user to the desired route
                                    window.location.href = '{{ route('travel-allowance.index') }}';
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    text: 'An error occurred',
                                    timer: 3000,
                                });
                                $('#sumbitButton').prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                icon: 'warning',
                                text: 'An error occurred' + error,
                                timer: 3000,
                            });
                            $('#sumbitButton').prop('disabled', false);
                        }
                    });
                }
            });
        });
    </script>
@endsection
