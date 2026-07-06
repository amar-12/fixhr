@extends('admin.layout.master')

@section('title', 'Policy Category')

<style>
    .disable-alt{
        background-color: #eee !important;
        pointer-events: none !important;
    }

    .select2-container.read-only .select2-selection {
        pointer-events: none;
        background-color: #eee;
    }
</style>

@section('content')
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
            <li class="active"><span><b>Policy Category</b></span></li>
        </ol>
    </div>
    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Policy Category</div>
            <p class="text-muted">Create and activate Policy Category</p>
        </div>
    </div>
    {{-- @php
        dd($policyCategory);
    @endphp --}}
    <div class="card">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Policy Category List</span></h4>
            </div>
            <div class="ms-auto">
                <button class="btn text-white btn-info btn-sm" id="addPolicyCategoryFieldBtn"><i class="fe fe-plus bold"></i></button>
            </div>
        </div>
        <!-- Add this line to your blade file to check if errors exist -->

        <div class="card-body">

            <form method="POST" id="addPolicyCategoryFrm">
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
        // //     foreach ($policyCategory as $category){
        // //         $ptc_grade_id[] = $category->ptc_grade_id;
        // //     }
        // @endphp

        $(document).ready(function() {
            var count = 1;
            // var ptc_grade_id = {//!! json_encode($ptc_grade_id) !!};
            var grades = {!! json_encode($grades) !!};
            // addPolicyCategoryFields();

            // function addPolicyCategoryFields() {
            //     $('#appendContainer').empty();

                // Loop through each policy category data and add fields
                @if(count($policyCategory))
                    @foreach($policyCategory as $index => $category)
                        addPolicyCategoryField({{$index+1}}, {
                            id : '{{$category->ptc_id}}',
                            name : '{{$category->ptc_name}}',
                            grade : '{{$category->ptc_grade_id}}',
                            department : '{{$category->ptc_d_id}}',
                            designation : '{{$category->ptc_dg_id}}',
                            travelTypeIds :  '{{$category->ptc_pttt_id}}',
                            status :  '{{$category->ptc_status}}',
                        });
                        count = {{ $index + 1 }};
                    @endforeach
                @else
                    addPolicyCategoryField(count, data={});
                @endif
            // }

            function addPolicyCategoryField(number, data={}) {
                // Iterate over existing rows to collect selected options
                // $('.appendPolicyCategoryFieldDiv').each(function() {
                //     const selectedGrade = $(this).find('select[name="grade[]"]').val();
                //     if (selectedGrade && !selectedGrades.includes(selectedGrade)) selectedGrades.push(selectedGrade);
                // });
                let cnt = number;
                html = `<div class="row appendPolicyCategoryFieldDiv">
                    <div class="col-md-12 col-xl-4">
                        <div class="row">
                            <input name="id[]" type="hidden" value="${data.id !== undefined ? data.id : ''}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"> Category Name :</label>
                                    <input name="name[]" id="name_${number}" class="form-control CategoryName" value="${data.name !== undefined ? data.name : ''}" placeholder="Enter Category Name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"> Grade :</label>
                                    <select name="grade[]" id="grade_${number}" class="form-control custom-select select2 grade" data-placeholder="Select Grade" ${data.grade !== undefined ? '' : ''} required>
                                        <option label="Select Grade"></option>`;
                                        if(data.grade !== undefined) {
                                            @foreach($grades as $key => $val)
                                                html += `<option value="{{$key}}" ${data.grade == '{{$key}}' ? 'selected' : ''} >{{$val}}</option>`;
                                            @endforeach
                                        } else {
                                            Object.keys(grades).forEach(function(key) {
                                                if (!selectedGrades.includes(key)) {
                                                    html += `<option value="${key}" ${data.grade == key ? 'selected' : ''}>${grades[key]}</option>`; // val = grades[key]
                                                }
                                            });
                                        }
                            html += `</select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"> Department :</label>
                                    <select name="department[]" id="department_${number}" class="form-control custom-select select2 department" data-placeholder="Select Department" ${data.department !== undefined ? '' : ''} required>
                                        <option label="Select department"></option>
                                        @foreach($departments as $key => $val)
                                            <option value="{{$key}}" ${data.department == '{{$key}}' ? 'selected' : ''} >{{$val}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label"> Designation :</label>
                            <select ${data.designation !== undefined ? 'name="designation[]"' : 'name="designation[${cnt}][]" multiple'}  id="designation_${number}"  class="form-control custom-select select2 designation" data-placeholder="Select Designation" required>
                                <option label="Select Designation"></option>
                                @foreach($designations as $key=>$val)
                                    <option value="{{$key}}" ${data.designation == '{{$key}}' ? 'selected' : ''} >{{$val}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-3">
                        <div class="form-group">
                            <label class="form-label"> Travel Types :</label>
                            <select name="travelTypeId[${cnt}][]" id="travel_type_${number}" multiple class="form-control custom-select select2 travelType" data-placeholder="Select Travel Types" required>
                                @foreach($travelTypes as $key=>$val)
                                    <option value="{{$val->pttt_id}}" ${data.travelTypeIds && data.travelTypeIds.includes('{{$val->pttt_id}}') ? 'selected' : ''}>{{$val->fh_travel_type->m_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-2">
                        <div class="form-group">
                            <label class="form-label"> Status :</label>
                            <select name="status[]" id="status_${number}" class="form-control custom-select select2" data-placeholder="Select Status" required>
                                <option label="Select Status"></option>
                                <option value="1" ${data.status == '1' ? 'selected' : ''} >Active</option>
                                <option value="0" ${data.status == '0' ? 'selected' : ''} >Inactive</option>
                            </select>
                        </div>
                    </div>`;

                if (Object.keys(data).length === 0 && data.constructor === Object && number >= 1)
                {
                    html += `<div class="col-md-12 col-xl-1">
                        <div class="form-group mt-4">
                            <button type="button" class="btn btn-outline-danger  btn-sm mt-3 float-right removePolicyCategoryFieldBtn">
                                <i class="feather feather-trash"></i>
                            </button>
                        </div>
                    </div>`;
                }
                html += `</div>`;
                $('#appendContainer').append(html);
                $('.select2').select2();
                // $('#mySelect2').next('.select2-container').addClass('read-only');
                // $('#addPolicyCategoryFieldBtn').prop('disabled', true);
            }

            $(document).on('click', '#addPolicyCategoryFieldBtn', function() {
                count++;
                addPolicyCategoryField(count);
                $('#addPolicyCategoryFieldBtn').prop('disabled', true);
            });

            $(document).on('click', '.removePolicyCategoryFieldBtn', function() {
                count--;
                $(this).closest('.appendPolicyCategoryFieldDiv').remove();
                checkAllFieldsFilled();
            });

            function checkAllFieldsFilled() {
                let allFilled = true;
                $('.appendPolicyCategoryFieldDiv').each(function() {
                    const grade = $(this).find('.grade').val();
                    const department = $(this).find('.department').val();
                    const designation = $(this).find('.designation').val();
                    const travelType = $(this).find('.travelType').val();

                    if (!grade || !department || !designation || !travelType.length) {
                        allFilled = false;
                        return false;  // Break out of the loop
                    }
                });

                // Enable or disable the add button based on whether all fields are filled
                $('#addPolicyCategoryFieldBtn').prop('disabled', !allFilled);
            }

            $('#appendContainer').on('change', '.grade, .department, .designation, .travelType', function() {
                checkAllFieldsFilled();
                checkForDuplicatesAndAlert();
            });

            function checkForDuplicatesAndAlert() {
                var count = $('.appendPolicyCategoryFieldDiv').length;
                var duplicateIndices = [];
                var isDuplicate = false;
                var combinationSet = new Set();
                for(var i = 1; i <= count; i++) {
                    // var name = $('#name_' + i).val();
                    var grade = $('#grade_' + i).val();
                    var department = $('#department_' + i).val();
                    var designation = $('#designation_' + i).val();
                    // var travelType = $('#travel_type_' + i).val();
                    // var status = $('#status_' + i).val();

                    var combination = `${grade}-${department}-${designation}`;//-${travelType}`;

                    if (combinationSet.has(combination)) {
                        isDuplicate = true;
                        duplicateIndices.push(i);
                    }
                    combinationSet.add(combination);
                }

                if (isDuplicate) {
                    Swal.fire({
                        icon: 'warning',
                        text: 'Duplicate combination of Grade, Department and Designation found.',
                        timer: 3000,
                    });
                    // $('.select2').select2('destroy');
                    duplicateIndices.forEach(function(index) {
                        // $('#name_' + index).val('').trigger('change');
                        $('#grade_' + index).val('').trigger('change');
                        $('#department_' + index).val('').trigger('change');
                        $('#designation_' + index).val('').trigger('change');
                        $('#travel_type_' + index).val('').trigger('change');
                        // $('#status_' + index).val('').trigger('change');
                    });
                    return false;
                }
                $('.select2').select2();
                return true;
            }

            $('#addPolicyCategoryFrm').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:'{{ route("admin.create.update.policy.category") }}',
                    method:'post',
                    data:$(this).serialize(),
                    dataType:'json',
                    beforeSend:function() {
                        $('#saveUptBtn').attr('disabled','disabled');
                    },
                    success:function(data)
                    {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Policy category saved successfully.',
                            timer: 3000,
                        }).then(() => {
                            window.location.href =
                                '{{ route('admin.travel.policy.category') }}';
                        });
                        $('#saveUptBtn').attr('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        // console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to save policy category.',
                            timer: 3000,
                        });
                    }
                });
            });
        });
    </script>
@endsection
