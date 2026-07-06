<style>
    .text-error-danger {
        font-size: 10px;
        color: red;
    }

    .custom-switch-indicator {
        background: red;
        /* Change this to the desired red color */
    }

    .profile-pic-wrapper {
        width: 100%;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .pic-holder {
        text-align: center;
        position: relative;
        border-radius: 50%;
        width: 120px;
        /* //150px; */
        height: 120px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .pic-holder .pic {
        /* height: 100%;
        width: 100%; */
        -o-object-fit: cover;
        object-fit: cover;
        -o-object-position: center;
        object-position: center;
    }

    .text-danger {
        color: red;
        /* or any other red color you prefer */
    }

    .pic-holder .upload-file-block,
    .pic-holder .upload-loader {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background-color: rgba(90, 92, 105, 0.7);
        color: #f8f9fc;
        font-size: 12px;
        font-weight: 600;
        opacity: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .pic-holder .upload-file-block {
        cursor: pointer;
    }

    .pic-holder:hover .upload-file-block,
    .uploadProfileInput:focus~.upload-file-block {
        opacity: 1;
    }

    .pic-holder.uploadInProgress .upload-file-block {
        display: none;
    }

    .pic-holder.uploadInProgress .upload-loader {
        opacity: 1;
    }

    /* Snackbar css */
    .snackbar {
        visibility: hidden;
        min-width: 250px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 2px;
        padding: 16px;
        position: fixed;
        z-index: 1;
        left: 50%;
        bottom: 30px;
        font-size: 14px;
        transform: translateX(-50%);
    }

    .snackbar.show {
        visibility: visible;
        -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }

    @-webkit-keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30px;
            opacity: 1;
        }
    }

    @keyframes fadein {
        from {
            bottom: 0;
            opacity: 0;
        }

        to {
            bottom: 30px;
            opacity: 1;
        }
    }

    @-webkit-keyframes fadeout {
        from {
            bottom: 30px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }

    @keyframes fadeout {
        from {
            bottom: 30px;
            opacity: 1;
        }

        to {
            bottom: 0;
            opacity: 0;
        }
    }
</style>

<div class="modal fade" id="addEmpDetaiForms" tabindex="-1" aria-labelledby="addEmpDetaiFormsLabel"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmpDetaiFormsLabel">Add New Employee</h5>
                <a aria-label="Close" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"><span
                        aria-hidden="true">&times;</span>
                </a>
            </div>
            <div class="modal-body">

                <form  enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="step-one">

                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">

                                        <h4 class="mb-2 font-weight-bold">Personal Details</h4>
                                        <div class="row profile-image">
                                            <div class="col-md-4">

                                            </div>

                                            <div class="col-md-4 text-center ">

                                                <div class="profile-pic-wrapper">
                                                    <div class="pic-holder">

                                                            <img class="pic rounded-circle"
                                                                src="#" height="150px"
                                                                width="150px" alt="upload profile image new"
                                                                class="mt-2">

                                                            <img class="pic rounded-circle"
                                                                src="#"
                                                                alt="profile image">


                                                        <label for="newProfilePhoto" class="upload-file-block">
                                                            <div class="text-center">
                                                                <div class="mb-2">
                                                                    <i class="fa fa-camera fa-2x"></i>
                                                                </div>
                                                                <div class="text-uppercase">
                                                                    Update <br /> Profile Photo
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 text-center">






                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">First Name<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name=""
                                                    placeholder="Enter first name" >


                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Middle Name</label>
                                                <input id="" type="text"
                                                    class="update_mname_sddd form-control" placeholder="Middle Name"
                                                    name="new_middle_name">


                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Last
                                                    Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name=""
                                                    placeholder="Enter last name">


                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Contact Number<span
                                                        class="text-danger">*</span></label>
                                                <input id="" type="number"
                                                    class=" form-control"
                                                    placeholder="Enter 10-digit phone number">


                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Email ID<span
                                                        class="text-danger">*</span></label>
                                                <input class=" form-control" wire:model="new_email"
                                                    placeholder="Enter email" id=""
                                                     name="">


                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Date Of Birth<span
                                                        class="text-danger">*</span></label>
                                                <input type="date" class=" form-control fc-datepicker"
                                                    placeholder="DD-MM-YYY" id="dateofbirth_sd">


                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Gender<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control " aria-label="Type" id=""
                                                    name="" wire:model="new_gender"
                                                    >
                                                    <option value="">Select Gender</option>
                                                    @foreach ($genders as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach

                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_gender')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Marital Status<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control marital_satatu_sddd" aria-label="Type"
                                                    id="" name=""
                                                    wire:model.live="new_martial_status"
                                                    wire:model="new_martial_status"
                                                    >
                                                    <option value="">Select Marital Status</option>
                                                    @foreach ($maritalStatuses as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_martial_status')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Date Of Joining<span
                                                        class="text-danger">*</span></label>
                                                <input type="date" class="form-control fc-datepicker "
                                                    id="" placeholder="DD-MM-YYYY" name=""
                                                     wire:model="new_doj">
                                                <span class="text-error-danger">
                                                    @error('new_doj')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Nationality<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control " name=""
                                                    id="" wire:model="new_nationality">

                                                <span class="text-error-danger">
                                                    @error('new_nationality')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Religion<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control" aria-label="Type" id=""
                                                    name="" wire:model="religion"
                                                    wire:model.live="new_religion"
                                                    >
                                                    <option value="">Select Religion</option>
                                                    @foreach ($religions as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_religion')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Category</label>
                                                <select class="form-control update_caste_sddd"
                                                    wire:model="new_category"
                                                     aria-label="Type"
                                                    id="" name="">
                                                    <option value="">Select Category</option>
                                                    @foreach ($casts as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                {{-- <span class="text-error-danger">
                                                    @error('new_category')
                                                        {{ $message }}
                                                    @enderror
                                                </span> --}}

                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Blood Group<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control " aria-label="Type" id=""
                                                    name="" wire:model="new_blood_group"
                                                    >
                                                    <option value="">Select Blood Group</option>
                                                    @foreach ($bloodGroups as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_blood_group')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Select Government Id<span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control" aria-label="Type" id="select_id_dd"
                                                    name="" wire:model="new_govt_id"
                                                    >
                                                    <option value="">Select Any Government Id</option>
                                                    @foreach ($govtIds as $key=>$val)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_govt_id')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Id Number<span
                                                        class="text-danger">*</span></label>
                                                <input type="text" class="form-control "
                                                    wire:model="new_govt_id_no" name=""
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_govt_id_no')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                            {{-- <div class="col-md-6 p-3 ">
                                        <label class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="example-checkbox1"
                                                value="option1">
                                            <span class="custom-control-label"><b>Send SMS
                                                    Employee</b></span>
                                            <span class="fs-11">By continuing you agree to <b><a href="#"
                                                        class="text-primary">Tearm &
                                                        Conditions</a></b>
                                            </span>
                                        </label>

                                        </select>
                                    </div> --}}
                                            <div class="col-md-4">
                                            </div>
                                            <div class="col-md-4">

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                    </div>

                    {{-- STEP 2 --}}
                    <div class="step-two">


                            <div class="card">
                                <div class="card-body">
                                    <div class=" row ">
                                        <h4 class=" font-weight-bold">Company Details</h4>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Branch<span
                                                    class="text-danger">*</span></label>
                                            <select name="" id="" wire:model.live="new_branch"
                                                wire:model="new_branch" class=" form-control"
                                                >
                                                <option value="">Select Branch Name</option>
                                                @foreach ($branches as $key=>$val)
                                                    <option value="{{ $key }}">
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_branch')
                                                    {{ $message }}
                                                @enderror
                                            </span>

                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Department<span
                                                    class="text-danger">*</span></label>
                                            <select name="" wire:model.live="new_department"
                                                class=" form-control"
                                                wire:model="new_department">
                                                <option value="">
                                                    Select Deparment Name</option>
                                                @foreach ($departments as $key=>$val)
                                                    <option value="{{ $key }}">
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_department')
                                                    {{ $message }}
                                                @enderror
                                            </span>

                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Designation<span
                                                    class="text-danger">*</span></label>
                                            <select name="" class="form-control"
                                                wire:model.live="new_designation"

                                                wire:model="new_designation">
                                                <option value="">Select Designation Name</option>
                                                @foreach ($designations as $key=>$val)
                                                    <option value="{{ $key }}">
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_designation')
                                                    {{ $message }}
                                                @enderror
                                            </span>

                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Employee ID<span
                                                    class="text-danger">*</span>
                                                {{-- <a wire:click="getGenerateEmpID"
                                                class="p-0 btn btn-primary btn-sm">EmpId
                                                A.I.</a>  --}}
                                                {{-- $generate_emp_id != null ? 'IT001' : ' not create module' --}}
                                            </label>
                                            <input name="" id="" type="text"
                                                wire:keyup="newEmployeeValidation"
                                                 class=" form-control"
                                                wire:model.live="generate_emp_id" wire:model="generate_emp_id"
                                                placeholder="Employee ID Like: IT001">
                                            <span class="text-error-danger">
                                                @error('generate_emp_id')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assign Attendance Method<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control custom-select"
                                                wire:model.live="new_attendance_method"

                                                wire:model="new_attendance_method">
                                                <option value="">Select Attendance
                                                    Method
                                                </option>
                                                @foreach ($attendanceMethod as $key=>$val)
                                                    <option value="{{ $key }}">
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_attendance_method')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>


                                        {{-- <div class="col-md-4" wire:ignore>
                                            <label class="form-label mb-0 mt-2">Reporting Manager</label>
                                            <select wire:model='new_report_manager' wire:change="getReportingList"
                                                wire:keyup="validateData" placeholder="Select Employee"
                                                class=" form-control search_test">
                                                <option selected value="">Select Reporting Manager</option>
                                                @if (!empty($listMode))
                                                    @foreach ($listMode as $option)
                                                        <option value="{{ $option->emp_id }}">
                                                            {{ $option->first_name . '' . $option->last_name }}
                                                            | {{ $option->designation_name }}
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <div class="list-item">No Result</div>
                                                @endif
                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_report_manager')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                            <script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.min.js"></script>
                                            <script src="https://www.unpkg.com/datatable-customizer/assets/jquery.sumoselect.js"></script>
                                            <script>
                                                $('.search_test').SumoSelect({
                                                    search: true,
                                                    searchText: 'Enter here.'
                                                });
                                            </script>

                                        </div> --}}




                                        <h4 class="pt-5 font-weight-bold">Communication Details</h4>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Country<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control w-100 border rounded"
                                                wire:model.live="new_country" wire:model="new_country"
                                                >
                                                <option value="">Select Country</option>
                                                @foreach ($countries  as $key)
                                                    <option value="{{ $key }}">{{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <span class="text-error-danger">
                                                @error('new_country')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">State<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control w-100 border rounded" wire:model="new_state"
                                                wire:model.live="new_state"
                                                >
                                                <option value="">Select State</option>

                                                    <option value="">
                                                    </option>

                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_state')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">City<span
                                                    class="text-danger">*</span></label>
                                            <select wire:model.live="new_city"
                                                class="form-control w-100 border rounded" name=""
                                                wire:model="new_city" >
                                                <option value="">Select City</option>

                                                    <option value="">
                                                    </option>

                                            </select>
                                            <span class="text-error-danger">
                                                @error('new_city')
                                                    {{ $message }}
                                                @enderror
                                            </span>

                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Pin Code<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class=" form-control" placeholder="Postal PIN"
                                                name="" wire:keyup="newEmployeeJoiningValidation"
                                                wire:model="new_pin_code">
                                            <span class="text-error-danger">
                                                @error('new_pin_code')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label mb-0 mt-2">Address<span
                                                    class="text-danger">*</span></label>
                                            <textarea id="" type="text" wire:model="new_address" class=" form-control"
                                                wire:keyup="newEmployeeJoiningValidation" placeholder="Address" name="" cols="30" rows="2"></textarea>
                                            <span class="text-error-danger">
                                                @error('new_address')
                                                    {{ $message }}
                                                @enderror
                                            </span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 p-3">

                                                <label class="custom-switch">
                                                    Active &nbsp;
                                                    <input type="checkbox" wire:model="new_isEnabled"
                                                        class="custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                    <span class="custom-switch-description">Enable/Disable</span>
                                                </label>
                                            </div>

                                        </div>

                                        {{-- <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="example-checkbox2"
                                        value="option2">
                                    <span class="custom-control-label">I agree terms &
                                        Conditions</span>
                                </label> --}}
                                    </div>
                                </div>
                            </div>


                    </div>
                    <div class="step-three">


                            <div class="card">
                                <div class="card-body">

                                    <div class="form-group">

                                        <h4 class="mb-2 font-weight-bold">Add Bank Details</h4>
                                        <div class="row">

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">IFSC Code</label>
                                                <input type="text" class="form-control" name="new_ifsc_code"
                                                    placeholder="Enter Ifsc Code" wire:model="new_ifsc_code"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_ifsc_code')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Bank Name</label>
                                                <input type="text" class="form-control" name="new_bank_name"
                                                    placeholder="Enter Bank Name" wire:model="new_bank_name"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_bank_name')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Branch Name</label>
                                                <input type="text" class="form-control" name="new_branch_name"
                                                    placeholder="Enter Branch Name" wire:model="new_branch_name"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_branch_name')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Branch Code</label>
                                                <input type="text" class="form-control" name="new_branch_code"
                                                    placeholder="Enter Branch Code" wire:model="new_branch_code"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_branch_code')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Bank Account No.</label>
                                                <input type="number" class="form-control" name="new_bank_accountno"
                                                    placeholder="Enter Bank Account No."
                                                    wire:model="new_bank_accountno"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_bank_accountno')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">MICR Code</label>
                                                <input type="number" class="form-control" name="new_micr_code"
                                                    placeholder="Enter Micr Code" wire:model="new_micr_code"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_micr_code')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label mb-0 mt-2">Address (Line 1)</label>
                                                <textarea type="text" class="form-control" name="new_bank_address_line1"
                                                    placeholder="Enter Address Line 1 without State,City & Pincode" wire:model="new_bank_address_line1"
                                                    wire:keyup="newEmployeeJoiningValidation"></textarea>
                                                <span class="text-error-danger">
                                                    @error('new_bank_address_line1')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-0 mt-2">Address (Line 2)</label>
                                                <textarea type="text" class="form-control" name="new_bank_address_line2"
                                                    placeholder="Enter Address Line 2 without State,City & Pincode" wire:model="new_bank_address_line2"
                                                    wire:keyup="newEmployeeJoiningValidation"></textarea>
                                                <span class="text-error-danger">
                                                    @error('new_bank_address_line2')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group">

                                        <h4 class="mb-2 font-weight-bold">Add Other Details</h4>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Grade Type</label>
                                                <select wire:model="new_grade"

                                                    wire:model.live="new_grade" class="form-control">
                                                    <option value="">Select Grade Type</option>
                                                    @foreach ($genders as $key)
                                                        <option value="{{ $key }}">
                                                            {{ $val }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="text-error-danger">
                                                    @error('new_grade')
                                                        {{ $message }}
                                                    @enderror
                                                </span>

                                            </div>

                                            {{-- <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Grade<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="new_grade"
                                                placeholder="Enter Grade " wire:model="new_grade"
                                                wire:keyup="validateData">
                                            <span class="text-error-danger">
                                                @error('new_grade')
                                                    {{ $message }}
                                        @enderror
                                        </span>
                                    </div> --}}

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Budget Code (SAP)</label>
                                                <input type="number" class="form-control" name="new_budget_code"
                                                    placeholder="Enter Budget Code" wire:model="new_budget_code"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_budget_code')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Account Code</label>
                                                <input type="number" class="form-control" name="new_account_code"
                                                    placeholder="Enter Account Code" wire:model="new_account_code"
                                                    wire:keyup="newEmployeeJoiningValidation">
                                                <span class="text-error-danger">
                                                    @error('new_account_code')
                                                        {{ $message }}
                                                    @enderror
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                    </div>
                     <div class="action-buttons d-flex justify-content-between   bg-white p-2 ">

                     <button type="button" class="btn btn-md btn-primary" >Next</button>
                     <button type="button" class="btn btn-md btn-danger">Back</button>
                    <button type="submit" class="btn btn-md btn-primary">Submit</button>


                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<div  class="modal fade" id="empTypeComponent" data-bs-backdrop="static">

    <div class="modal-dialog modal-dialog-centered " role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h5 style="color: #fff!important">Add New Employee</h5>
                <a aria-label="Close" class="btn-close" data-bs-dismiss="modal" ><span
                        aria-hidden="true">&times;</span>
                </a>
            </div>
            <div class="modal-body  justify-content-around">

                <p class="form-label">Select Employee Type</p>
                <div class="form-group">
                    <select  name="employee_type" class="form-control form-select" id="employeeType">
                        <option value="" selected >Select Employee Type</option>
                        @foreach ($getEmpType as $key=>$value)
                            <option value="{{$key}}">{{$value}}</option>
                        @endforeach

                    </select>
                </div>

            </div>

            <div>


                    <div class="col-sm-12 text-center d-none" id="regularEmpEdiv">
                        <div>
                            <h5><b style="color:#1877f2">Regular Employee</b></h5>
                        </div>
                        <div>
                            <a type="button" class="btn btn-outline-primary my-2 border-0" data-bs-toggle="modal"
                                data-effect="effect-scale" data-bs-target="#addEmpDetaiForms"><b> Add New
                                    Employee</b></a>

                            <a href="#" class="btn btn-outline-primary my-2 border-0"
                                data-bs-toggle="modal" data-bs-target="{{ url('admin/employee/export_file') }}"><b><i
                                        class="fa fa-file-excel-o me-1"></i>Download
                                    Sample Template</b>
                            </a>
                            <form action="#" method="POST"
                                enctype="multipart/form-data" wire:ignore>
                                @csrf

                                <input type="text" id="emp_type" name="emp_type" value="1" hidden>
                                <input type="file" name="csv_file" class="load" data-height="90"
                                    data-allowed-file-extensions="xlsx">
                                <button type="submit" class="btn btn-outline-primary my-2 border-0"> <b>Upload
                                        Employees</b></button>
                                <script src="{{ asset('assets/plugins/formwizard/jquery.smartWizard.js?v1.3') }}"></script>
                                <script src="{{ asset('assets/plugins/formwizard/fromwizard.js?v3.80') }}"></script>
                                <script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
                                <script src="{{ asset('assets/js/filupload.js?v=10') }}"></script>

                                <script>
                                    LoaderPackageDropify('load', 'Employee Bulk Upload Select Regular Excel File');
                                    LoaderPackageDropify('load2', 'Employee Bulk Upload Select Contractual Excel File');
                                </script>
                            </form>

                        </div>
                    </div>

                    <div class=" justify-content-center d-none" id="ContractualEmpDiv">

                        <div class="px-4 justify-content-center">
                            <p class="form-label">Select Contractual Type</p>
                            <div class="form-group ">
                                <select id="ContractType" wire:model="employee_contractual_type"
                                    name="contractualtype" class="form-control form-select">
                                    <option value="" selected>Select Contractual Type</option>
                                    @foreach ($getContractualType as $key=>$value)
                                        <option value="{{$key}}">
                                            {{$value}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h5 class="text-center"><b style="color:#1877f2">Contractual Employee</b></h5>

                        <div class="text-center">
                            <button type="button" class="modal-effect btn btn-primary my-2 border-0"
                                data-bs-toggle="modal" data-effect="effect-scale" data-bs-target="#addEmpDetaiForms"
                                ><b>
                                    Add
                                    Employee</b></button>
                            <a href="{{ url('admin/employee/export_file/2') }}"
                                class="btn btn-outline-primary my-2 border-0 " data-bs-toggle="modal"
                                data-bs-target="{{ url('admin/employee/export_file/2') }}"><b><i
                                        class="fa fa-file-excel-o me-1"></i>Download
                                    Sample Template</b>
                            </a>
                            <form action="{{ url('admin/employee/import_file') }}" method="POST"
                                enctype="multipart/form-data" wire:ignore>
                                @csrf
                                <input type="text" id="emp_type" name="emp_type" value="2" hidden>
                                <input type="file" name="csv_file" class="load2" data-height="90"
                                    data-allowed-file-extensions="xlsx">
                                <button type="submit" class="btn btn-outline-primary my-2 border-0"> <b>Upload
                                        Employees</b></button>

                                <script src="{{ asset('assets/plugins/formwizard/jquery.smartWizard.js?v1.3') }}"></script>
                                <script src="{{ asset('assets/plugins/formwizard/fromwizard.js?v3.80') }}"></script>
                                <script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
                                <script src="{{ asset('assets/js/filupload.js?v=10') }}"></script>

                                <script>
                                    LoaderPackageDropify('load', 'Employee Bulk Upload Select Regular Excel File');
                                    LoaderPackageDropify('load2', 'Employee Bulk Upload Select Contractual Excel File');
                                </script>
                            </form>

                        </div>

                    </div>



            </div>


        </div>
    </div>
</div>
