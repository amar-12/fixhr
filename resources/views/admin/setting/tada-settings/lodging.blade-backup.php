@extends('admin.layout.master')

@section('title', 'Daily Allowance & Lodging')

<style>
    .disable-alt {
        background-color: #eee !important;
        pointer-events: none !important;
    }
</style>

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Daily Allowance & Lodging </b></span></li>
        </ol>
    </div>
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Daily Allowance & Lodging</div>
            <p class="text-muted">Set Eligiblity for Daily Allowance & Lodging</p>
        </div>
    </div>
    {{-- @php
        dd($Lodging);
    @endphp --}}
    <div class="card">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Daily Allowance & Lodging List</span></h4>
            </div>

            <div class="ms-auto">
                <button class="btn text-white btn-info btn-sm" id="addLodgingFieldBtn"><i class="fe fe-plus bold"></i></button>
            </div>
        </div>

        <div class="card-body">

            <form method="POST" id="addLodgingForm">
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
    <script>
        var selectedGrades = [];
        // @php
        // //     $ptc_grade_id = [];
        // //     foreach ($Lodging as $item){
        // //         $ptc_grade_id[] = $item->ptc_grade_id;
        // //     }
        //
        @endphp

        function numericOnly(event) {
            return (event.charCode >= 48 && event.charCode <= 57);
        }

        $(document).ready(function() {
            var count = 1;
            // var ptc_grade_id = {//!! json_encode($ptc_grade_id) !!};
            // var grades = {//!! json_encode($grades) !!};
            addLodgingFields();

            function addLodgingFields() {
                $('#appendContainer').empty();

                // Loop through each policy category data and add fields
                @foreach($lodging as $index => $item)
                    addLodgingField({{$index + 1}}, {
                        id : '{{$item->ptdal_id}}',
                        policy_cat : '{{$item->ptdal_ptc_id}}',
                        travel_type : '{{$item->ptdal_pttt_id}}',
                        city_type : '{{$item->ptdal_ct_type_id}}',
                        da_eligible_amt : '{{$item->ptdal_da_per_day_elig}}',
                        day_eligible_amt : '{{$item->ptdal_da_same_day_ret_elig}}',
                        eligiblity_bill :  '{{$item->ptdal_lodg_sngl_w_bill_elig}}',
                        eligiblity_no_bill :  '{{$item->ptdal_lodg_sngl_wo_bill_elig}}'
                    });
                    count = {{ $index + 1 }};
                @endforeach
            }

            function addLodgingField(number, data = {}) {
                // Iterate over existing rows to collect selected options

                html = `<div class="row appendLodgingFieldDiv">
                    <input name="id[]" type="hidden" value="${data.id !== undefined ? data.id : ''}">
                    <div class="col-md-12 col-xl-3">
                        <div class="form-group">
                            <label class="form-label">Policy Category :</label>
                            <select name="policy_cat[]" class="form-control custom-select select2" data-placeholder="Policy Category" required>
                                <option label="Select Policy Category"></option>
                                @foreach($policyCategory as $key => $val)
                                    <option value="{{$key}}" ${data.policy_cat == '{{$key}}' ? 'selected' : ''} >{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label">Travel Type :</label>
                            <select name="travel_type[]" class="form-control custom-select select2" data-placeholder="Travel Type" required>
                                <option label="Select Travel Type"></option>
                                @foreach($travelTypes as $key => $ttype)
                                    <option value="{{$ttype->pttt_id}}" ${data.travel_type == '{{$ttype->pttt_id}}' ? 'selected' : ''} >{{ $ttype->fh_travel_type->m_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label">City Type :</label>
                            <select name="city_type[]" class="form-control custom-select select2" data-placeholder="Select City Type" required>
                                <option label="Select City Type"></option>
                                @foreach($cityType as $key => $val)
                                    <option value="{{$key}}" ${data.city_type == '{{$key}}' ? 'selected' : ''} >{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-1">
                        <div class="form-group">
                            <label class="form-label">DA :</label>
                            <input name="da_eligible_amt[]" class="form-control" value="${data.da_eligible_amt !== undefined ? data.da_eligible_amt : ''}" placeholder="Enter Amount" onkeypress="return numericOnly(event)" maxlength="10" required>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-1">
                        <div class="form-group">
                            <label class="form-label">Same Day DA :</label>
                            <input name="day_eligible_amt[]" class="form-control" value="${data.day_eligible_amt !== undefined ? data.day_eligible_amt : ''}" placeholder="Enter Amount" onkeypress="return numericOnly(event)" maxlength="10" required>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-1">
                        <div class="form-group">
                            <label class="form-label">Lodging Bill :</label>
                            <input name="eligiblity_bill[]" class="form-control" value="${data.eligiblity_bill !== undefined ? data.eligiblity_bill : ''}" placeholder="Enter Amount" onkeypress="return numericOnly(event)" maxlength="10" required>
                        </div>
                    </div>

                    <div class="col-md-12 col-xl-1">
                        <div class="form-group">
                            <label class="form-label">No Bill (Amt):</label>
                            <input name="eligiblity_no_bill[]" class="form-control" value="${data.eligiblity_no_bill !== undefined ? data.eligiblity_no_bill : ''}" placeholder="Enter Amount" onkeypress="return numericOnly(event)" maxlength="10" required>
                        </div>
                    </div>`;

                if (Object.keys(data).length === 0 && data.constructor === Object && number >= 1) {
                    html += `<div class="col-md-12 col-xl-1">
                        <div class="form-group mt-4">
                            <button type="button" class="btn btn-outline-danger  btn-sm mt-3 float-right removeLodgingFieldBtn">
                                <i class="feather feather-trash"></i>
                            </button>
                        </div>
                    </div>`;
                }
                html += `</div>`
                $('#appendContainer').append(html);
                $('.select2').select2();
            }

            $(document).on('click', '#addLodgingFieldBtn', function() {
                count++;
                addLodgingField(count);
            });

            $(document).on('click', '.removeLodgingFieldBtn', function() {
                count--;
                $(this).closest('.appendLodgingFieldDiv').remove();
            });

            function checkDuplicates() {
                let policyCategories = [];
                let travelTypes = [];
                let cityTypes = [];
                let hasDuplicates = false;

                $('#appendContainer .appendLodgingFieldDiv').each(function() {
                    let policyCat = $(this).find('select[name="policy_cat[]"]').val();
                    let travelType = $(this).find('select[name="travel_type[]"]').val();
                    let cityType = $(this).find('select[name="city_type[]"]').val();

                    if (policyCat && travelType && cityType) {
                        let key = policyCat + '-' + travelType + '-' + cityType;

                        if (policyCategories.includes(key)) {
                            hasDuplicates = true;
                        } else {
                            policyCategories.push(key);
                        }
                    }
                });

                return hasDuplicates;
            }

            $('#addLodgingForm').on('submit', function(event) {
                event.preventDefault();

                if (checkDuplicates()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Entries',
                        text: 'Duplicate Policy Category and City Type combinations found. Please correct them before submitting.',
                        timer: 3000,
                    });
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.create.update.lodging') }}',
                    method: 'post',
                    data: $(this).serialize(),
                    dataType: 'json',
                    beforeSend: function() {
                        $('#saveUptBtn').attr('disabled', 'disabled');
                    },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                text: data.message || 'Lodging saved successfully.',
                                timer: 3000,
                            }).then(() => {
                                location.reload();
                                window.location.href = '{{ route('admin.travel.lodging') }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                text: data.error || 'There was a problem saving the lodging.',
                                timer: 3000,
                            });
                        }
                        $('#saveUptBtn').attr('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        // console.error(xhr.responseText);
                        var response = xhr.responseText;
                        try {
                            var json = JSON.parse(response);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: json.error || 'An unexpected error occurred.',
                                timer: 3000,
                            });
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid response from server.',
                                timer: 3000,
                            });
                        }
                        $('#saveUptBtn').attr('disabled', false);
                    }
                });
            });
            var i = {{ count($lodging) }};
            if (i == 0) {
                // Add initial row when no data is available
                i = 1;
                addLodgingField(i);
            }
        });
    </script>
@endsection
