@extends('admin.layout.master')
@section('title')
    @if (isset($employee))
        Update Employee
    @else
        Add New Employee
    @endif
@endsection
@php
    $ActiveTap = 1;
@endphp
@section('css')
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <style>
        /* Example CSS adjustments */
        .select2-container {
            width: 100% !important;
        }

        .form-step {
            display: flex;
            flex-direction: column;
        }

        .form-step .form-group {
            margin-bottom: 1rem;
        }

        .date-placeholder {
            color: #BCC0E2;
            /* Change this to the color you prefer */
        }

        .pac-container {
            z-index: 10000 !important;
        }

        .rotate {
            transition: 500ms;
            transform: rotate(90deg);
        }

        .star-dot {
            color: red;
        }

        /* Set the map's size */
        #map,
        #tempmap {
            height: 400px;
            width: 100%;
        }

        /* Adjust the search input style */
        #permanentSearchInput {
            width: 100%;
            margin-bottom: 10px;
        }

        .pac-container {
            z-index: 10000 !important;
        }
        /* Avatar styling */
        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-size: cover;
            background-position: center;
            cursor: pointer;
            position: relative;
            display: inline-block;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .avatar-xxl {
            width: 150px;
            height: 150px;
        }

        .brround {
            border-radius: 50%;
        }

        .avatar-status {
            position: absolute;
            bottom: 6px;
            right: 6px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .bg-green {
            background-color: #38cb89;
        }

        /* Shared modal styling for both modals */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }

        .modal-content {
            background-color: #fff;
            border-radius: 16px;
            width: 90%;
            max-width: 500px;
            padding: 0;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Modal Tabs */
        .tab-container {
            margin: 0;
            padding: 24px;
        }

        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
            background-color: #f5f7fa;
            border-radius: 10px;
            padding: 4px;
        }

        .tab-button {
            flex: 1;
            padding: 12px 20px;
            cursor: pointer;
            text-align: center;
            font-weight: 500;
            color: #596780;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .tab-button.active {
            background-color: white;
            color: #4361ee;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .cam-tab-content {
            display: none;
            padding: 20px 0 5px;
        }

        .cam-tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .camera-container,
        .preview-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        #video {
            max-width: 100%;
            border-radius: 14px;
            background-color: #f0f2f5;
            height: 260px;
            object-fit: cover;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        #gallery-preview,
        #camera-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 14px;
            object-fit: contain;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .button-row {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 10px;
            width: 100%;
        }

        .upload-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 180px;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            margin-bottom: 20px;
            cursor: pointer;
            background-color: #f9fafb;
            transition: all 0.2s ease;
        }

        .upload-icon:hover {
            border-color: #4361ee;
            background-color: rgba(67, 97, 238, 0.03);
        }

        .upload-icon svg {
            width: 48px;
            height: 48px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .upload-icon p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        #profileInput {
            display: none;
        }

        /* Footer */
        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 16px 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            background-color: #fcfcfc;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background-color: #4361ee;
            color: white;
            box-shadow: 0 2px 5px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            background-color: #3a56d4;
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background-color: #f0f2f5;
            color: #596780;
        }

        .btn-secondary:hover {
            background-color: #e4e7ed;
        }

        .btn-success {
            background-color: #38cb89;
            color: white;
            box-shadow: 0 2px 5px rgba(56, 203, 137, 0.3);
        }

        .btn-success:hover {
            background-color: #2eb67d;
            box-shadow: 0 4px 8px rgba(56, 203, 137, 0.4);
        }

        .btn-danger {
            background-color: #f95555;
            color: white;
            box-shadow: 0 2px 5px rgba(249, 85, 85, 0.3);
        }

        .btn-danger:hover {
            background-color: #f73a3a;
            box-shadow: 0 4px 8px rgba(249, 85, 85, 0.4);
        }

        /* Crop Modal */
        .cropModal {
            display: none;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1100;
        }

        .cropModal > div {
            background: white;
            padding: 20px;
            max-width: 400px;
            width: 100%;
            border-radius: 10px;
        }

        .cropModal img {
            max-width: 100%;
            border-radius: 10px;
        }

        .font-weight-semibold{
            font-size: 12px;

        }

        .emp_lable_size{
            font-size: 12px;
        }
        #tempmap {
            display: none;
        }
        #map {
            display: none;
        }
        .accordion-item {
            border: none;
        }

        .accordion-button {
            border: none;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-body {
            border-top: none;
        }

        .hide_display
        {
            display: none;

        }

        .icon-tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .icon-tooltip .tooltip-text {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(10%, -35%);
            background-color: #000;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            white-space: nowrap;
        }

        
        .icon-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.3/dist/css/select2.min.css" rel="stylesheet" />
@endsection


@section('content')
@if(session('success'))
    <script>
        // alert("{{ session('success') }}");
        location.reload(); // Reload page after showing message
    </script>
@endif
    <div class="side-app main-container">
        <!-- PAGE HEADER -->
        <div class="page-header d-xl-flex d-block">
            <div class="page-leftheader">
                <div class="page-title">
                    @if (isset($employee))
                        Update Employee
                    @else
                        Add Employee
                    @endif
                </div>
            </div>
        </div>
        <!-- END PAGE HEADER -->

        <div>
            <div class="row p-2 ">
                <ul class="nav nav-pills">
                    @php
                        $arr = [
                            '1' => 'any',
                            '2' => 'any',
                            '3' => 'any',
                            '4' => 'any',
                            '5' => 'admin_only',
                            '6' => 'admin_only',
                            '7' => 'admin_only',
                            '8' => 'admin_only',
                            '9' => 'admin_only',
                            '10' => 'admin_only',
                        ];
                    @endphp
                    @foreach ($arr as $i => $access_permission)
                        <li class="nav-item p-1">
                            <a class="nav-link" id="tab-{{ $i }}"
                                style="background-color: {{ $access_permission === 'any' ? '#eeeeee' : '#e0e0e0' }};"
                                @if (isset($employee)) onclick="showTab({{ $i }})" @endif>
                                <span class="fs-15">
                                    <i class="fa fa-circle" id="icon-{{ $i }}"></i>
                                    {{ ['About', 'Personal', 'Identity', 'Bank', 'Organization', 'Attendance & Leave', 'Joining', 'PF & ESIC', 'Separation', 'Finish'][$i - 1] }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-md-12 col-lg-12" id="sidebard-employee-card">
                <div class="card user-pro-list overflow-hidden" id="avtarDiv">
                    <div class="card-body">
                        <div class="user-pic text-center">
                            <!-- First Modal (Working) -->
                            <span class="avatar avatar-xxl brround emp-avatar" data-target="1" onclick="openModal('uploadModal')"
                                style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');">
                             @if (isset($employee))
                                    @if ($employee->emp_status == 71)
                                        @if (!empty($employee->emp_last_working_date))
                                            <span class="avatar-status bg-yellow"></span>
                                        @else
                                            <span class="avatar-status bg-green"></span>
                                        @endif
                                    @else
                                        <span class="avatar-status bg-red"></span>
                                    @endif
                                @endif
                            </span>
                            <input type="file" id="profileInput" style="display:none;" accept="image/*" onchange="handleFileSelect(this, 'uploadModal')" />

                            <div id="uploadModal" class="modal uploadModal">
                                <div class="modal-content">
                                    <div id="cropModal1" class="crop-modal"
                                        style="display: none; justify-content: center; align-items: center; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 1100;">
                                        <div style="background: white; padding: 20px; max-width: 400px; width: 100%; border-radius: 10px;">
                                            <img id="cropperImage1" style="max-width: 100%; border-radius: 10px;" />
                                            <div style="margin-top: 10px; text-align: right;">
                                                <button onclick="cropImage('uploadModal')" class="btn btn-outline-primary">Crop & Use</button>
                                                <button onclick="closeCropModal('uploadModal')" class="btn btn-outline-danger">Cancel</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-container">
                                        <div class="tab-buttons">
                                            <div class="tab-button active" onclick="switchTab('gallery', 'uploadModal')">Drive</div>
                                            <div class="tab-button" onclick="switchTab('camera', 'uploadModal')">Camera</div>
                                        </div>

                                        <div id="gallery-tab-1" class="tab-content cam-tab-content active" data-modal="uploadModal">
                                            <div class="camera-container">
                                                <label for="profileInput" class="upload-icon" id="upload-area-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <p>Click to browse image</p>
                                                </label>

                                                <div id="gallery-preview-container-1" style="display: none;">
                                                    <img id="gallery-preview-1" src="" style="max-width: 100%; max-height: 300px;" />
                                                </div>
                                            </div>
                                        </div>

                                        <div id="camera-tab-1" class="tab-content cam-tab-content" data-modal="uploadModal">
                                            <div id="camera-container-1" class="camera-container">
                                                <video id="video-1" width="100%" height="auto" autoplay playsinline></video>
                                                <div class="button-row">
                                                    <button id="startCameraBtn-1" class="btn btn-success"
                                                        onclick="startCamera('uploadModal')">Start Camera</button>
                                                    <button id="captureBtnCamera-1" class="btn btn-success"
                                                        onclick="capturePhoto('uploadModal')" style="display: none;">Capture
                                                        Image</button>
                                                </div>
                                            </div>

                                            <canvas id="canvas-1" style="display:none;"></canvas>

                                            <div id="camera-preview-container-1" class="preview-container" style="display: none;">
                                                <img id="camera-preview-1" src="" style="max-width: 100%; max-height: 300px;" />
                                                <div class="button-row">
                                                    <button class="btn btn-danger" onclick="retakePhoto('uploadModal')">Retake</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-danger" onclick="closeModal('uploadModal')">Cancel</button>
                                        <button type="button" class="btn btn-outline-primary" id="saveBtn-1" name="action"
                                            value="ajaxcapsave" onclick="saveImage('uploadModal')" disabled>Save</button>
                                    </div>
                                </div>
                            </div>

                            {{-- <span class="avatar avatar-xxl brround" id="empAvtar" onclick="inputClick(event)"
                                style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? asset('uploads/employee_profile/' . $employee->emp_profile_photo) : asset('assets/imgs/user.png') }}');">
                                <input type="file" id="profileInput" style="display:none;" value="{{ isset($employee) && $employee->emp_profile_photo ? asset('uploads/employee_profile/' . $employee->emp_profile_photo) : asset('assets/imgs/user.png') }}" onchange="profileSet()" />
                                <span class="avatar-status bg-green"></span>
                            </span> --}}
                            <div class="pro-user mt-3">
                                <h5 class="pro-user-username text-dark mb-1 fs-16" id="employeeName">Employee Name</h5>
                                <h6 class="pro-user-desc text-muted fs-12" id="emailText">-</h6>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="card tabCard" id="tabCard1">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">About Employee</h4>
                        <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                           {{-- <hr style="border: 1px solid ;">    --}}

                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Employee Code </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="EmpIDText">
                                            {{ isset($employee) ? $employee->emp_code : '---' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Contact No.</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ContactText">
                                            {{ isset($employee) ? $employee->emp_phone : '---' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Date Of Birth</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="birthdayText">
                                     {{ isset($employee) ? \Carbon\Carbon::parse($employee->emp_dob)->format('d F Y') : '---' }}

                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Gender </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="genderText">
                                            {{ isset($employee->fh_gender) ? $employee->fh_gender->m_name : '---' }}</td>
                                    </tr>
                                    <tr class="hide_display">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Marital Status </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="martialText">
                                            {{ isset($employee->fh_marital_status) ? $employee->fh_marital_status->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="hide_display">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Blood Group</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="bloodGrouptext">
                                            {{ isset($employee->fh_blood_group) ? $employee->fh_blood_group->m_name : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard7">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Joining Details</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Status </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="activeText">
                                            {{ isset($employee->fh_employee_status) ? $employee->fh_employee_status->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Employee Type </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="contractText">
                                            {{ isset($employee->fh_employee_type) ? $employee->fh_employee_type->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Joining Date </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="joiningDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_joining : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_joining ? \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Job Status </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="jobStatusText">
                                            {{ isset($employee->fh_job_status) ? $employee->fh_job_status->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Group Joining </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="groupJoiningText">
                                            {{-- {{ isset($employee) ? $employee->emp_group_date_of_joining : '' }} --}}
                                            {{ isset($employee) && $employee->emp_group_date_of_joining ? \Carbon\Carbon::parse($employee->emp_group_date_of_joining)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Gratuity Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="gratuityDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_gratuity : '' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_gratuity ? \Carbon\Carbon::parse($employee->emp_date_of_gratuity)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Transfer Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="transferDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_transfer : '' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_transfer ? \Carbon\Carbon::parse($employee->emp_date_of_transfer)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Expected Confirmation Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="expectedConfirmationText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_expected_confirmation : '' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_expected_confirmation ? \Carbon\Carbon::parse($employee->emp_date_of_expected_confirmation)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Probation Days</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="probationDateText">
                                            {{ isset($employee) ? $employee->emp_probation_period : '' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Confirmation Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="confirmationDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_confirmation : '' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_confirmation ? \Carbon\Carbon::parse($employee->emp_date_of_confirmation)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Pay Structure Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="payStructureDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_date_of_pay_structure : '' }} --}}
                                            {{ isset($employee) && $employee->emp_date_of_pay_structure ? \Carbon\Carbon::parse($employee->emp_date_of_pay_structure)->format('d F Y') : '' }}

                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard5">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Organization Details</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    {{-- <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC  Enable </span>
                                        </td>
                                        <td class="py-2 px-0" id="esicLimitText">
                                            {{ isset($employee->fh_esic_limit) ? $employee->fh_esic_limit->m_name : '---' }}
                                        </td>
                                    </tr> --}}
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Branch </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="branchText">
                                            {{ isset($employee->fh_branch) ? $employee->fh_branch->br_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Department </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="departmentText">
                                            {{ isset($employee->fh_department) ? $employee->fh_department->d_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Designation </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="designationText">
                                            {{ isset($employee->fh_designation) ? $employee->fh_designation->dg_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Grade </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="gradeText">
                                            {{ isset($employee->fh_grade) ? $employee->fh_grade->g_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Role </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="roleText">
                                            {{ isset($employee->fh_role) ? $employee->fh_role->role_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Reporting Manager </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="reportManagerText">
                                            @foreach ($supervisor as $roleitem)
                                                @if ((isset($employee->emp_supervisor_id) ? $employee->emp_supervisor_id : '') == $roleitem->emp_id)
                                                    {{ $roleitem->emp_full_name }}
                                                @endif
                                            @endforeach
                                            @if (!isset($employee->emp_supervisor_id) || !$employee->emp_supervisor_id)
                                                ---
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Budget Code (SAP)</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="budgetCodeText">
                                            {{ isset($employee) ? $employee->emp_sap_budget_code : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
               <!-- Assigned Projects -->
            <div class="card-body">
                 <h4 class="card-title  text-primary" style="margin: 10px;">Projects </h4>
                  <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
            </div>
            <div class="col-md-12 mb-3">
                @if(!empty($employee) && !empty($employee->emp_project_id))
                    @php
                        $empProjectIds = is_array($employee->emp_project_id)
                            ? $employee->emp_project_id
                            : explode(',', $employee->emp_project_id);

                        $projectNames = [];
                        foreach($emp_projects as $project) {
                            if(in_array($project->ps_id, $empProjectIds)) {
                                $projectNames[] = $project->ps_name;
                            }
                        }
                    @endphp

                    @if(count($projectNames) > 0)
                        @foreach($projectNames as $name)
                            <span class="badge rounded-pill text-white bg-dark">{{ $name }}</span>
                        @endforeach
                    @else
                        <span class="text-muted ms-3">No projects assigned</span>
                    @endif
                @else
                    <span class="text-muted ms-3">No projects assigned</span>
                @endif

                <span class="text-muted ms-3">-</span>
            </div>

            <!-- Assigned Assets -->
       
             <div class="card-body">
                 <h4 class="card-title  text-primary" style="margin: 10px;">Assigned Assets </h4>
                  <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
            </div>
            <div class="col-md-12 mb-3">
                @php
                    // Use optional() to safely handle null $employee
                    $empAssetIds = is_array(optional($employee)->emp_assets_id)
                        ? optional($employee)->emp_assets_id
                        : ( !empty(optional($employee)->emp_assets_id)
                            ? explode(',', optional($employee)->emp_assets_id)
                            : [] );
                @endphp

                @if(count($empAssetIds) > 0)
                    <ul class="list-group list-group-flush ms-3">
                        @foreach($emp_assets as $asset)
                            @if(in_array($asset->id, $empAssetIds))
                                <li class="list-group-item py-1 px-2">
                                    <span class="fw-semibold">{{ $asset->assetType->name }}</span> -
                                    <span class="text-secondary">{{ $asset->asset_tag }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <span class="text-muted ms-3">-</span>
                @endif

            </div>

     
                </div>
                <div class="card tabCard d-none" id="tabCard6">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Attendance Details</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Attendance Mode </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="assignMethodText">
                                            {{ isset($employee->fh_work_mode) ? $employee->fh_work_mode->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0" hidden>
                                            <span class="font-weight-semibold w-50">Check In Method </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="checkInMethodText" hidden>
                                            {{ isset($employee->fh_work_mode)? $checkInMethod->whereIn('m_id', $employee->emp_checkin_method_id ?? [])->pluck('m_name')->implode(', '): '---' }}
                                        </td>
                                    </tr>
                                    <tr hidden>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Assign Setup </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="assignSetupText">---</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Shift Policy</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="shiftPolicyText">
                                            {{ isset($employee->fh_shift_type) ? $employee->fh_shift_type->pst_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Attendance Policy </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="attendancePolicyText">
                                            {{ isset($employee->fh_attendance_policy) ? $employee->fh_attendance_policy->ap_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Geofencing </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="geofencingText">
                                            {{ isset($employee->fh_geofencing) ? $employee->fh_geofencing->m_name : '---' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">WeekOff </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="weekOffText">
                                            {{ isset($employee->fh_week_off_policy) ? $employee->fh_week_off_policy->pwo_name : '---' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Attendance Preference</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="attendancePreferenceText">
                                            {{ isset($employee->fh_attendance_preference) ? $employee->fh_attendance_preference->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Leave Policy</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="leavePolicyText">
                                            {{ isset($employee->fh_attendance_mode) ? $employee->fh_attendance_mode->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Joining Leave</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="joiningLeaveText">
                                            {{ isset($employee->emp_allow_joining_leave) ? $employee->emp_allow_joining_leave : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Leave Calculation Method</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="calculationMethodText">
                                            {{ isset($employee->emp_joining_leave_calc_type) ? $employee->emp_joining_leave_calc_type : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Leave Applicable Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="applicableDateText">
                                            {{ isset($employee->emp_joining_leave_before_date) ? $employee->emp_joining_leave_before_date : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Probation Leave </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="probationLeaveText">
                                            {{ isset($employee->emp_allow_probation_leave) ? $employee->emp_allow_probation_leave : '---' }}
                                        </td>
                                    </tr>
                                        <tr>
                                            <td class="py-2 px-0">
                                                <span class="font-weight-semibold w-50">GeoWork</span>
                                            </td>
                                            <td class="py-2 px-0 emp_lable_size" id="geoWorkText">
                                                {{ isset($employee->emp_is_geowork_active) ? ($employee->emp_is_geowork_active ? 'Active' : 'Inactive') : '---' }}
                                            </td>
                                        </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard3">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Update & Uploads</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Aadhar Number </span>
                                        </td>
                                        <td class="py-2 px-0" id="aadhardNumberText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->aadhar_number) ? json_decode($employee->emp_documents_ref_file)->aadhar_number : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Driving License </span>
                                        </td>
                                        <td class="py-2 px-0" id="drivingLicenseText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->driving_license_number) ? json_decode($employee->emp_documents_ref_file)->driving_license_number : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Election Card </span>
                                        </td>
                                        <td class="py-2 px-0" id="electionCardText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->voterUpload) ? json_decode($employee->emp_documents_ref_file)->voterUpload : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Passport Number </span>
                                        </td>
                                        <td class="py-2 px-0" id="passportText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->passport_number) ? json_decode($employee->emp_documents_ref_file)->passport_number : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Bank A/c Number </span>
                                        </td>
                                        <td class="py-2 px-0" id="bankAcNumText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->account_number) ? json_decode($employee->emp_documents_ref_file)->account_number : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PAN Number </span>
                                        </td>
                                        <td class="py-2 px-0" id="panNumText">
                                            {{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->pan_number) ? json_decode($employee->emp_documents_ref_file)->pan_number : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard8">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">PF & ESIC Details</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>

                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PF Enable</span>
                                        </td>

                                        <td class="py-2 px-0 emp_lable_size" id="pfLimitText">
                                            {{ isset($employee->fh_pf_master) ? $employee->fh_pf_master->m_name : '---' }}
                                        </td>
                                    </tr>


                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC Enable </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esicLimitText">
                                            {{ isset($employee->fh_esic_limit) ? $employee->fh_esic_limit->m_name : '---' }}
                                        </td>
                                    </tr>


                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PF Trust Code </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="pftrustCodeText">
                                            {{ isset($employee) ? $employee->emp_pf_trust_code ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Pension Fund Member </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="pensionMemberText">
                                            {{ isset($employee) ? $employee->emp_pf_found_member ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PF Number </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="pfNumText">
                                            {{ isset($employee) ? $employee->emp_pf_no ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Universal Acount Number </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="uniAcNumText">
                                            {{ isset($employee) ? $employee->emp_pf_universal_ac_no ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">VPF (%) </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="vpfPercText">
                                            {{ isset($employee->fh_pf_master) ? $employee->fh_pf_master->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PF Date of Joining </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="pfJoinDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_pf_joining_date ?? '---' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_pf_joining_date ? \Carbon\Carbon::parse($employee->emp_pf_joining_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">PF Date of Leaving</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="pfLeaveDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_pf_leaving_date ?? '---' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_pf_leaving_date ? \Carbon\Carbon::parse($employee->emp_pf_leaving_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0 ">
                                            <span class="font-weight-semibold w-50">Reason of Leaving PF</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="PfLeaveReasonText">
                                            {{ isset($employee) ? $employee->emp_pr_leaving_reason ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC Number</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esicNumText">
                                            {{ isset($employee) ? $employee->emp_esi_no ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC Dispensary</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esiDesperacyText">
                                            {{ isset($employee) ? $employee->emp_esi_dispensary ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC Date of Joining</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esicDateOfJoiningText">
                                            {{-- {{ isset($employee) ? $employee->emp_esic_joining_date ?? '---' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_esic_joining_date ? \Carbon\Carbon::parse($employee->emp_esic_joining_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">ESIC Date of Leaving</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esicDateOfLeaveText">
                                            {{-- {{ isset($employee) ? $employee->emp_esic_leaving_date ?? '---' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_esic_leaving_date ? \Carbon\Carbon::parse($employee->emp_esic_leaving_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Reason of Leaving ESIC</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="esicLeaveReasonText">
                                            {{ isset($employee) ? $employee->fh_employee_esic_reson->m_name ?? '---' : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard2">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Permanent Address</h4>
                        <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Permanent Address</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="permanentAddressText">
                                            {{ isset($employee) ? $employee->emp_permanent_address ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Permanent Longitude</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="permanentLongitudeText">
                                            {{ isset($employee) ? $employee->emp_permanent_longitude ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Permanent Latitude</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="permanentLatitudeText">
                                            {{ isset($employee) ? $employee->emp_permanent_latitude ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Zip Code</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="permanentPinCodeText">
                                            {{ isset($employee) ? $employee->emp_permanent_pin_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                </tbody>

                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Temporary Address</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="temporaryAddressText">
                                            {{ isset($employee) ? $employee->emp_temporary_address ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Temporary Longitude</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="temporaryLongitudeText">
                                            {{ isset($employee) ? $employee->emp_temporary_longitude ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Temporary Latitude</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="temporaryLatitudeText">
                                            {{ isset($employee) ? $employee->emp_temporary_latitude ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Zip Code</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="temporaryPinCodeText">
                                            {{ isset($employee) ? $employee->emp_temporary_pin_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                </tbody>

                                <tbody style="display: none">
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Qualification </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="qualificationText">
                                            @foreach ($qualification as $qua)
                                                {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_qualification_id == $qua->qua_id ? $qua->qua_name : '' }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Stream </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="streamText">
                                            @foreach ($stream as $stm)
                                                {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_stream_id == $stm->stm_id ? $stm->stm_name : '' }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Course Type </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="courseTypeText">
                                            @foreach ($qualificationCourseType as $qct)
                                                {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_course_type_id == $qct->m_id ? $qct->m_name : '' }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Specialization </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="SpecializationText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_specialization : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Nature of Course </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NatureofCourseText">
                                            @foreach ($natureCourse as $noc)
                                                {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_course_nature == $noc->m_id ? $noc->m_name : '' }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Qualification Status </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="QualificationStatusText">
                                            @foreach ($qualificationStatus as $qc)
                                                {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_qualification_status == $qc->m_id ? $qc->m_name : '' }}
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Institute Name </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="InstituteNameText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_institution_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">University Name </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="UniversityNameText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_university_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">From Date </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="FromDateText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_from_date : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">To Date </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ToDateText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_to_date : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Passing Date </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="PassingDateText">
                                            {{-- {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_passing_date : '---' }} --}}
                                            {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_passing_date ? \Carbon\Carbon::parse($employee->fh_employee_qualifications->eq_passing_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Percentage </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="PercentageText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_percentage : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Grade </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="GradeText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_grade : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Duration of Course </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="DurationofCourseText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_duration : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Year </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="YearText">
                                            {{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_year : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard9">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;">Separation Details</h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Year of Service</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="YearofServiceText">
                                            {{ isset($employee) ? $employee->emp_year_of_service ?? $formatted : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Retirement Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="RetirementDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_retirement_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_retirement_date ? \Carbon\Carbon::parse($employee->emp_retirement_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Separation Submit On</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="SeprationSubmitOnText">
                                            {{-- {{ isset($employee) ? $employee->emp_separation_submit_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_separation_submit_date ? \Carbon\Carbon::parse($employee->emp_separation_submit_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Expected Leaving Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ExpectedLeavingDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_expected_leaving_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_expected_leaving_date ? \Carbon\Carbon::parse($employee->emp_expected_leaving_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Leaving Date Period</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="LeavingDatePeriodText">
                                            {{-- {{ isset($employee) ? $employee->emp_leaving_date_as_per_notice_period ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_leaving_date_as_per_notice_period ? \Carbon\Carbon::parse($employee->emp_leaving_date_as_per_notice_period)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr >
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Notice Period Days</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NoticePeriodDaysText">
                                            {{ isset($employee) ? $employee->emp_notice_period_req_days ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Reason For Leaving</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ReasonForLeavingText">
                                       {{ isset($employee->fh_employee_reson) ? $employee->fh_employee_reson->m_name : '---' }}
                                        </td>
                                    </tr>
                                    <tr >
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Leaving Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="LeavingDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_leave_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_leave_date ? \Carbon\Carbon::parse($employee->emp_leave_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Notice Period Days</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NoticeServedDaysText">
                                            {{ isset($employee) ? $employee->emp_notice_period_serve_days ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Settlement From</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="SettlementFromText">
                                            {{-- {{ isset($employee) ? $employee->emp_settlement_from_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_settlement_from_date ? \Carbon\Carbon::parse($employee->emp_settlement_from_date)->format('d F Y') : '---' }}

                                            
                                        </td>
                                    </tr>
                                    <tr >
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Final Settlement Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="FinalSettlementDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_final_settlement_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_final_settlement_date ? \Carbon\Carbon::parse($employee->emp_final_settlement_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Notice Period Shorftfall Days</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NoticePeriodShorftfallDaysText">
                                            {{ isset($employee) ? $employee->emp_notice_period_shortfall_days ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Exit Interview Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ExitInterviewDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_exit_interview_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_exit_interview_date ? \Carbon\Carbon::parse($employee->emp_exit_interview_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Last Working Date</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="LastWorkingDateText">
                                            {{-- {{ isset($employee) ? $employee->emp_last_working_date ?? '----' : '---' }} --}}
                                            {{ isset($employee) && $employee->emp_last_working_date ? \Carbon\Carbon::parse($employee->emp_last_working_date)->format('d F Y') : '---' }}

                                        </td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Remark</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="RemarkText">
                                            {{ isset($employee) ? $employee->emp_remark ?? '----' : '---' }}</td>
                                    </tr>
                                    <tr class="d-none">
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Notice Period For Employer</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NoticePeriodForEmployerText">
                                            {{ isset($employee) ? $employee->emp_notice_period_day_for_employer ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr class="d-none"> 
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Notice Period For Employee</span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="NoticePeriodForEmployeeText">
                                            {{ isset($employee) ? $employee->emp_notice_period_day_for_employee ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card tabCard d-none" id="tabCard4">
                    <div class="card-body">
                        <h4 class="card-title  text-primary" style="margin: 10px;"> Payment Method </h4>
                                                <hr style="border: none; border-top:2px solid #a19e9ec7; width: 100%; margin: auto;">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Account Code (SAP) </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="accountCodeText">
                                            {{ isset($employee) ? $employee->emp_bank_ifsc_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">IFSC Code </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="ifscText">
                                            {{ isset($employee) ? $employee->emp_bank_ifsc_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Bank Name </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="BankNameText">
                                            {{ isset($employee) ? $employee->emp_bank_name ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Branch Name </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="BranchNameText">
                                            {{ isset($employee) ? $employee->emp_bank_branch_name ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">MICR </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="MICRText">
                                            {{ isset($employee) ? $employee->emp_bank_micr_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Branch Code </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="BankCodeText">
                                            {{ isset($employee) ? $employee->emp_bank_branch_code ?? '----' : '---' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 px-0">
                                            <span class="font-weight-semibold w-50">Bank A/c Number </span>
                                        </td>
                                        <td class="py-2 px-0 emp_lable_size" id="BankAccountNumberText">
                                            {{ isset($employee) ? $employee->emp_bank_account_no ?? '----' : '---' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-9 col-md-12 col-lg-12">
                <div class="panel-body tabs-menu-body hremp-tabs1 p-0">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="card-body">
                                <h4 class="card-title mb-1 text-primary p-2">About Employee</h4>
                                <div class="form-group">
                                    <div class="row">
                                   <div class="col-md-6">
                                    <label class="form-label mb-1">Employee Code <span class="text-danger">*</span></label>
                                    <div style="display: flex;justify-content: space-between;">
                                        <div>
                                            <input type="hidden" id="primary_id"
                                                value="{{ isset($employee) ? $employee->emp_id : '' }}">
                                            <input id="employee_id" type="text" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_code : $newEmpCode }}"
                                                {{ $newEmpCode ? 'readonly' : '' }}
                                                placeholder="Employee Code Like: IT001" onchange="checkEmployeeId(this)">
                                            <span class="text-danger small" id="EmpIdError"></span>
                                        </div>
                                        <div class="mt-2" style="display: flex; align-items: center;">
                                            <input type="hidden" name="is_approval_manager" id="is_approval_manager" value="{{ isset($employee) && $employee->is_approval_manager == 1 ? '1' : '0' }}">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                    id="offlineSyncCheckbox" 
                                                    value="1"
                                                    {{ isset($employee) && $employee->is_approval_manager == 1 ? 'checked' : '' }}
                                                                    onchange="document.getElementById('is_approval_manager').value = this.checked ? '1' : '0'; handleChange(event);">
                                                <span class="custom-control-label">Approval Manager</span>
                                            </label>
                                        </div>
                                    </div>
                                        
                                    </div>
                                                    </div>
                                                    <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="row">
                                                            <div class="col-3 pe-0">
                                                                <label class="form-label mb-0 mt-2">Prefix</label>
                                                                <select class="form-control form-select select2 update_gender_sddd"
                                                                    onchange="namePrint(this); namePrefix(this);" aria-label="Type"
                                                                    id="prefix" data-placeholder="prefix" required>
                                                                    @foreach ($prefix as $prefixitem)
                                                                        <option value="{{ $prefixitem->m_id }}"
                                                                            {{ isset($employee) && $employee->emp_prefix == $prefixitem->m_id ? 'selected' : '' }}>
                                                                            {{ $prefixitem->m_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-9">
                                                                <label class="form-label mb-0 mt-2">First Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" id="firstName" class="form-control"
                                                                    value="{{ isset($employee) ? $employee->emp_fname : '' }}"
                                                                    onchange="namePrint(0)" oninput="validAlpha(this)" maxlength="50"
                                                                    placeholder="First Name" name="name" required>
                                                                <span class="text-danger" id="firstNameErrorShow"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Middle Name</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" id="middleName" class="form-control"
                                                                    value="{{ isset($employee) ? $employee->emp_mname : '' }}"
                                                                    onchange="namePrint(1)" oninput="validAlpha(this)"
                                                                    placeholder="Middle Name" name="mName">
                                                                <span class="text-danger" id="middleNameError"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label id="last_id" class="form-label mb-0 mt-2">Last Name <span
                                                                class="text-danger"></span></label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" id="lastName" class=" form-control"
                                                                    value="{{ isset($employee) ? $employee->emp_lname : '' }}"
                                                                    onchange="namePrint(2)" oninput="validAlpha(this)"
                                                                    placeholder="Last Name" name="lName" required>
                                                                <span class="text-danger" id="lastNameError"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-12 col-md-4">
                                                        <label class="form-label mb-0 mt-2">Gender <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-control form-select select2 update_gender_sddd"
                                                            aria-label="Type" onchange="handleChange(event)" id="gender"
                                                            data-placeholder="Select Gender" required>
                                                            <option class="text-muted" value="" label="Select Gender"></option>
                                                            @foreach ($staticGender as $gender)
                                                                <option value="{{ $gender->m_id }}"
                                                                    {{ isset($employee) && $employee->emp_gender_id == $gender->m_id ? 'selected' : '' }}>
                                                                    {{ $gender->m_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger" id="genderError"></span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Marital Status </label>
                                                        <select class="form-control form-select select2" id="mariteStatus"
                                                            name="marital_satatus_dd" data-placeholder="Select Marital Status"
                                                            aria-label="Type" onchange="handleChange(event)" required>
                                                            <option class="text-muted" value="" label="Select Marital Status">
                                                            </option>
                                                            @foreach ($maritalStatus as $martial)
                                                                <option value="{{ $martial->m_id }}"
                                                                    {{ isset($employee) && $employee->emp_marital_status_id == $martial->m_id ? 'selected' : '' }}>
                                                                    {{ $martial->m_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger" id="mariteStatusError"></span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Date Of Birth <span
                                                                class="text-danger">*</span></label>
                                                      <input type="date"  max="2099-12-31" id="dateOfBirth" class="form-control"
                                                            value="{{ isset($employee) ? $employee->emp_dob : '' }}" name="dob_dd"
                                                            required>
                                                        <span class="text-danger" id="dateOfBirthError"></span>
                                                    </div>

                                                 <h4 class="card-title mb-1 mt-4 text-primary">Contact Info</h4>
                                                    
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Personal Mobile <span
                                                                class="text-danger">*</span></label>
                                                    <input type="tel" id="contact" class="form-control"
                                                            data-primary-emp-id="{{ isset($employee) ? $employee->emp_id : '' }}"
                                                            value="{{ isset($employee) ? $employee->emp_phone : '' }}"
                                                            placeholder="xxxxxxxxxx" name="mobile_number"
                                                            oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10); checkPhoneEmail(this, 0);"
                                                            maxlength="10">


                                                        <span class="text-danger" id="contactError"></span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Personal Email <span
                                                                class="text-danger">*</span></label>
                                                        <input name="email_sd" type="text" id="email" class="form-control"
                                                            data-primary-emp-id="{{ isset($employee) ? $employee->emp_id : '' }}"
                                                            value="{{ isset($employee) ? $employee->emp_email : '' }}"
                                                            placeholder="Email" name="email" oninput="checkPhoneEmail(this,1)"
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');"
                                                            required>
                                                        <span class="text-danger" id="emailError"></span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Official Mobile </label>
                                                        <input type="tel" id="emp_official_contact" class="form-control"
                                                            data-primary-emp-id="{{ isset($employee) ? $employee->emp_id : '' }}"
                                                            value="{{ isset($employee) ? $employee->emp_official_contact : '' }}"
                                                            placeholder="xxxxxxxxxx" name="emp_official_contact"
                                                            oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);"
                                                            maxlength="10">
                                                        <span class="text-danger" id="emp_official_contact_error"></span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Official Email </label>
                                                        <input type="email" id="emp_official_email" class="form-control"
                                                            data-primary-emp-id="{{ isset($employee) ? $employee->emp_id : '' }}"
                                                            value="{{ isset($employee) ? $employee->emp_official_email : '' }}"
                                                            placeholder="official email" name="emp_official_email"
                                                            oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, ''); "
                                                            >
                                                        <span class="text-danger" id="emp_official_email_error"></span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Emergency Contact Number <span
                                                                class="text-danger">*</span></label>
                                                        <input type="tel" id="emp_emergency_contact" class="form-control"
                                                            data-primary-emp-id="{{ isset($employee) ? $employee->emp_id : '' }}"
                                                            value="{{ isset($employee) ? $employee->emp_emergency_contact : '' }}"
                                                            placeholder="xxxxxxxxxx" name="emp_emergency_contact"
                                                            oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);"
                                                            maxlength="10" >
                                                        <span class="text-danger" id="emp_emergency_contact_error"></span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Emergency Contact Relation</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                    <select id="emp_emergency_relation" class="form-control select2" name="emp_emergency_relation" onchange="namePrint(1)">
                                                                    <option value="">-- Select Relation --</option>
                                                                    <option value="Father" {{ isset($employee) && $employee->emp_emergency_relation == 'Father' ? 'selected' : '' }}>Father</option>
                                                                    <option value="Mother" {{ isset($employee) && $employee->emp_emergency_relation == 'Mother' ? 'selected' : '' }}>Mother</option>
                                                                    <option value="Spouse" {{ isset($employee) && $employee->emp_emergency_relation == 'Spouse' ? 'selected' : '' }}>Spouse</option>
                                                                    <option value="Husband" {{ isset($employee) && $employee->emp_emergency_relation == 'Husband' ? 'selected' : '' }}>Husband</option>
                                                                    <option value="Wife" {{ isset($employee) && $employee->emp_emergency_relation == 'Wife' ? 'selected' : '' }}>Wife</option>
                                                                    <option value="Son" {{ isset($employee) && $employee->emp_emergency_relation == 'Son' ? 'selected' : '' }}>Son</option>
                                                                    <option value="Daughter" {{ isset($employee) && $employee->emp_emergency_relation == 'Daughter' ? 'selected' : '' }}>Daughter</option>
                                                                    <option value="Brother" {{ isset($employee) && $employee->emp_emergency_relation == 'Brother' ? 'selected' : '' }}>Brother</option>
                                                                    <option value="Sister" {{ isset($employee) && $employee->emp_emergency_relation == 'Sister' ? 'selected' : '' }}>Sister</option>
                                                                    <option value="Uncle" {{ isset($employee) && $employee->emp_emergency_relation == 'Uncle' ? 'selected' : '' }}>Uncle</option>
                                                                    <option value="Aunt" {{ isset($employee) && $employee->emp_emergency_relation == 'Aunt' ? 'selected' : '' }}>Aunt</option>
                                                                    <option value="Friend" {{ isset($employee) && $employee->emp_emergency_relation == 'Friend' ? 'selected' : '' }}>Friend</option>
                                                                    <option value="Colleague" {{ isset($employee) && $employee->emp_emergency_relation == 'Colleague' ? 'selected' : '' }}>Colleague</option>
                                                                    <option value="Other" {{ isset($employee) && $employee->emp_emergency_relation == 'Other' ? 'selected' : '' }}>Other</option>
                                                                    </select>


                                                                <span class="text-danger" id="emp_emergency_relation_error"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                        <div class="accordion-item" style="  margin-top: 10px;">
                                            <h2 class="accordion-header" id="headingTwo">
                                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                    aria-expanded="false" aria-controls="collapseTwo">
                                                       <h4 class="card-title mb-1 text-primary">Optional Info</h4>
                                              
                                                </button>
                                            </h2>
                                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                                data-bs-parent="#accordionExample">
                                                <div class="accordion-body">

                                                <div class="row">

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Nationality</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" id="emp_nationality" class="form-control"
                                                                    value="{{ isset($employee) ? $employee->emp_nationality : '' }}" onchange="namePrint(1)"
                                                                    oninput="validAlpha(this)" placeholder=" Nationality" name="emp_nationality">
                                                                <span class="text-danger" id="emp_nationality_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Religion </label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <input type="text" id="emp_religion" class="form-control"
                                                                    value="{{ isset($employee) ? $employee->emp_religion : '' }}" onchange="namePrint(1)"
                                                                    oninput="validAlpha(this)" placeholder=" Religion" name="emp_religion">
                                                                <span class="text-danger" id="emp_religion_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Cast</label>
                                                        <select class="form-control form-select select2" id="emp_category" onchange="handleChange(event)"
                                                            name="emp_category" aria-label="Category">
                                                            <option class="text-muted" value="">Select Cast Category</option>
                                                            @foreach ($emp_cast as $category)
                                                                <option value="{{ $category->m_id }}"
                                                                    {{ isset($employee) && $employee->emp_category_id == $category->m_id ? 'selected' : '' }}>
                                                                    {{ $category->m_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger" id="emp_category_error"></span>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label mb-0 mt-2">Blood Group </label>
                                                        <select class="form-control form-select select2" id="bloodGroup" onchange="handleChange(event)"
                                                            name="blood_group_dd" aria-label="Type" data-placeholder="Select Blood Group" required>
                                                            <option class="text-muted" value="" label="Select Blood Group">
                                                            </option>
                                                            @foreach ($bloodGroupList as $bloodgroup)
                                                                <option value="{{ $bloodgroup->m_id }}"
                                                                    {{ isset($employee) && $employee->emp_blood_group_id == $bloodgroup->m_id ? 'selected' : '' }}>
                                                                    {{ $bloodgroup->m_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <span class="text-danger" id="bloodGroupError"></span>
                                                    </div>

                                                    <div class="col-md-8">
                                                        <label for="emp_body_mark" class="form-label mb-0 mt-2">Identification</label>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <textarea id="emp_body_mark" name="emp_body_mark" class="form-control" onchange="namePrint(1)"
                                                                    oninput="validAlpha(this)" placeholder=" body mark (e.g., scar on left arm)" autocomplete="off"
                                                                    autocapitalize="words" rows="2">{{ isset($employee) ? $employee->emp_body_mark : '' }}</textarea>
                                                                <span class="text-danger" id="emp_body_mark_error"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-end" id="tab1btns">
                            <a href="javascript:void(0);" class="btn btn-outline-primary" id="nextBtn"
                                onclick="saveData('1','1')">Next</a>
                        </div>

                        <div class="tab-pane active d-none" id="tab2">
                            <div class="card-body">
                                {{-- <h4 class="card-title mb-1  text-primary">Qualification</h4> --}}
                                <input type="text" id="qualification_primary_id"
                                    value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_id : '' }}"
                                    hidden>

                        {{-- for Qualification  --}}
                                <div class="form-group" style="display:none">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Stream<span
                                                    class="text-danger"></span></label>
                                            <select class="form-control form-select select2 w-100 border rounded"
                                                id="stream" onchange="getQualification(this)"
                                                data-placeholder="Select Stream">
                                                <option value="">Select Stream</option>
                                                @foreach ($stream as $stm)
                                                    <option value="{{ $stm->stm_id }}"
                                                        {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_stream_id == $stm->stm_id ? 'selected' : '' }}>
                                                        {{ $stm->stm_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Qualification<span
                                                    class="text-danger"></span></label>
                                            <select class="form-control form-select select2 w-100 border rounded"
                                                id="qualification" data-placeholder="Select Qualification"
                                                onchange="handleChange(event)"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_qualification_id : '' }}">
                                                <option class="text-muted" value="" label="Select Qualification">
                                                    Select Qualification</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Qualification Course Type<span
                                                    class="text-danger"></span></label>
                                            <select class="form-control form-select select2 w-100 border rounded"
                                                id="cource_type" data-placeholder="Select Cource Type"
                                                onchange="handleChange(event)">
                                                <option class="text-muted" value="" label="Select Course">Select
                                                    Course</option>
                                                @foreach ($qualificationCourseType as $qct)
                                                    <option value="{{ $qct->m_id }}"
                                                        {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_course_type_id == $qct->m_id ? 'selected' : '' }}>
                                                        {{ $qct->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Specialization<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                placeholder=" Specialization" id="specalization"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_specialization : '' }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Nature of Course<span
                                                    class="text-danger"></span></label>
                                            <select class="form-control form-select select2 w-100 border rounded"
                                                id="cource_nature" data-placeholder="Select Nature of Course"
                                                onchange="handleChange(event)">
                                                <option class="text-muted" value="" label="Select Course">Select
                                                    Nature of Course</option>
                                                @foreach ($natureCourse as $noc)
                                                    <option value="{{ $noc->m_id }}"
                                                        {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_course_nature == $noc->m_id ? 'selected' : '' }}>
                                                        {{ $noc->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Qualification Status<span
                                                    class="text-danger"></span></label>
                                            <select class="form-control form-select select2 w-100 border rounded"
                                                id="quali_status" data-placeholder="Select Qualification Status"
                                                onchange="handleChange(event)">
                                                <option class="text-muted" value=""
                                                    label="Select Qualification Status">Select Qualification Status
                                                </option>
                                                @foreach ($qualificationStatus as $qc)
                                                    <option value="{{ $qc->m_id }}"
                                                        {{ isset($employee->fh_employee_qualifications) && $employee->fh_employee_qualifications->eq_qualification_status == $qc->m_id ? 'selected' : '' }}>
                                                        {{ $qc->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Institute Name<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                placeholder=" Institute Name" id="institute_name"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_institution_name : '' }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">University Name<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                placeholder=" University Name" id="universityName"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_university_name : '' }}">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">From Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_from_date : '' }}"
                                                placeholder="DD-MM-YYY" id="edu_from_date" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">To Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_to_date : '' }}"
                                                placeholder="DD-MM-YYY" id="edu_to_date" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Passing Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_passing_date : '' }}"
                                                placeholder="DD-MM-YYY" id="passing_date" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Percentage</label>
                                            <input type="text" class=" form-control" placeholder=" Percentage"
                                                oninput="validatePositiveNumber(this)"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_percentage : '' }}"
                                                id="percentage" onkeypress="numericOnly(event)" maxlength="2"
                                                min="0">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Grade<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control" placeholder=" Grade"
                                                min="0" oninput="validatePositiveNumber(this)"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_edu_grade : '' }}"
                                                id="grade" onkeypress="numericOnly(event)" maxlength="4">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Duration of Course<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                oninput="validatePositiveNumber(this)"
                                                placeholder=" Duration of Course" min="0"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_duration : '' }}"
                                                id="duration" onkeypress="numericOnly(event)" maxlength="4">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Year<span
                                                    class="text-danger"></span></label>
                                            <input type="text" min="0" class=" form-control"
                                                placeholder=" Year" maxlength="4"
                                                value="{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_year : '' }}"
                                                id="year" oninput="validatePositiveNumber(this)"
                                                onkeypress="numericOnly(event)">
                                        </div>
                                    </div>
                                </div>
                        {{-- Qualification End  --}}


                                <div class="col-lg p-0">
                                    {{-- <h4 class="card-title mb-1 p-2  text-primary m-0">Add Details</h4>
                                        <div class="row mt-4 gx-3">
                                            <div class="col-lg-4 col-md-4 mb-3">
                                                <a href="{{ route('family.create') }}" class="btn btn-outline-info" target="_blank">
                                                    <i class="bi bi-people-fill me-2"></i> Add Family Details
                                                </a>
                                            </div>
                                        </div> --}}

                                    <h4 class="card-title mb-1 p-2  text-primary m-0">Permanent Address</h4>
                                    <label class="form-label mb-0 mt-2" for="permanentSearchInput">Permanent Address
                                        <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="permanentSearchInput"
                                        name="permanent_location"
                                        value="{{ isset($employee) ? $employee->emp_permanent_address : '' }}"
                                        placeholder="Search Your location">
                                    <span class="text-danger" id="permanentSearchInputError"></span>

                                    <div class="row">
                                        <div class="col-4">
                                            <label class="form-label mb-0 mt-2" for="permanentLongitude">Longitude
                                            </label>
                                            <input class="form-control" type="number" id="permanentLongitude"
                                                name="permanent_longitude"
                                                value="{{ isset($employee) ? $employee->emp_permanent_longitude : '' }}"
                                                placeholder="Longitude" readonly>
                                            <span class="text-danger" id="permanentLongitudeError"></span>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0 mt-2" for="permanentLatitude">Latitude </label>
                                            <input class="form-control" type="text" id="permanentLatitude"
                                                name="latitude"
                                                value="{{ isset($employee) ? $employee->emp_permanent_latitude : '' }}"
                                                placeholder="Latitude" readonly>
                                            <span class="text-danger" id="permanentLatitudeError"></span>
                                        </div>
                                        <div class="col-2">
                                            <label class="form-label mb-0 mt-2" for="permanentPinCode">Zip Code <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="permanentPinCode"
                                                name="pinCode" oninput="validatePositiveNumber(this)" maxlength="6"
                                                onkeypress="numericOnly(event)"
                                                value="{{ isset($employee) ? $employee->emp_permanent_pin_code : '' }}"
                                                placeholder="Zip Code">
                                            <span class="text-danger" id="permanentPinCodeError"></span>
                                        </div>

                                     <div class="col-2 d-flex justify-content-center align-items-center" style="margin-top: 27px;">
                                        <img src="{{ url('assets/images/location.png') }}" alt="Location Icon"  class="clickable-input"  onclick="toggleFilterstwo()" style="width: 20px;">
                                        </a>
                                    </div>



                                    </div>
                                    <!-- Display the map -->
                                    <div class="m-1" id="map"></div>


                                    <div class="col-12 p-0 py-2">
                                        <label class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="example-checkbox1"
                                                id="tempSameAsParmanant" onchange="sameAddressFun(this)"
                                                {{ isset($employee) ? ($employee->emp_is_temporary_add_same == 1 ? 'checked' : '') : '' }}>
                                            <span class="custom-control-label"></span>Same as Permanent Address
                                        </label>
                                    </div>
                                    <h4 class="card-title mb-1  text-primary m-0">Temporary Address</h4>
                                    <label class="form-label mb-0 mt-2" for="temporarySearchInput">Temporary Address
                                        <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text" id="temporarySearchInput"
                                        name="temp_location"
                                        value="{{ isset($employee) ? $employee->emp_temporary_address : '' }}"
                                        placeholder="Search Your location">
                                    <span class="text-danger" id="temporarySearchInputError"></span>

                                    <div class="row">
                                        <div class="col-4">
                                            <label class="form-label mb-0 mt-2"
                                                for="temporaryLongitudetemporaryLongitude">Longitude </label>
                                            <input class="form-control" type="text" id="temporaryLongitude"
                                                name="templongitude"
                                                value="{{ isset($employee) ? $employee->emp_temporary_longitude : '' }}"
                                                placeholder="Longitude" readonly>
                                            <span class="text-danger" id="temporaryLongitudeError"></span>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label mb-0 mt-2" for="temporaryLatitude">Latitude </label>
                                            <input class="form-control" type="text" id="temporaryLatitude"
                                                name="templatitude"
                                                value="{{ isset($employee) ? $employee->emp_temporary_latitude : '' }}"
                                                placeholder="Latitude" readonly>
                                            <span class="text-danger" id="temporaryLatitudeError"></span>
                                        </div>
                                        <div class="col-2">
                                            <label class="form-label mb-0 mt-2" for="tempPinCode">Zip Code <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" id="tempPinCode"
                                                name="temppinCode" oninput="validatePositiveNumber(this)"
                                                maxlength="6" onkeypress="numericOnly(event)"
                                                value="{{ isset($employee) ? $employee->emp_temporary_pin_code : '' }}"
                                                placeholder="Zip Code">
                                          <span class="text-danger" id="tempPinCodeError"></span>
                                        </div>

                                        <style>
                                            .clickable-input {
                                                cursor: pointer;
                                            }
                                        </style>

                                        <div class="col-2 d-flex justify-content-center align-items-center" style="margin-top: 27px;">
                                            <img src="{{ url('assets/images/location.png') }}" alt="Location Icon" class="clickable-input" onclick="toggleFilters()" style="width: 20px;">
                                            </a>
                                        </div>



                                    </div>
                                    <!-- Display the map -->
                                    <div class="m-1" id="tempmap"></div>

                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-none" id="tab2btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('2','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="saveData('2','1'), {{ $ActiveTap = 2 }}">Next</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active d-none" id="tab3">
                            <div class="card-body">
                                
                                @if(isset($employee) && $employee->emp_id)
                                <h4 class="card-title mb-1 p-2  text-primary">Add Details</h4>
                                @endif

                                @php
                                    $employeeDocuments = isset($employee->emp_documents_ref_file)
                                        ? json_decode($employee->emp_documents_ref_file)
                                        : '';
                                @endphp

                                @php
                                    use Illuminate\Support\Facades\Crypt;
                                @endphp

                                @if(isset($employee) && $employee->emp_id)
                                    <div class="row mt-4 gx-3">
                                        <div class="col-lg-4 col-md-4 mb-3">
                                            <form action="{{ route('family.create') }}" method="GET" target="_blank">
                                                <input type="hidden" name="emp_id" value="{{ Crypt::encrypt($employee->emp_id) }}">
                                                <button type="submit" class="btn btn-outline-info">
                                                    <i class="bi bi-people-fill me-2"></i> Add Family Details
                                                </button>
                                            </form>
                                        </div>

                                        <div class="col-lg-4 col-md-4 mb-3">
                                            <form action="{{ route('academic.create') }}" method="GET" target="_blank">
                                                <input type="hidden" name="emp_id" value="{{ Crypt::encrypt($employee->emp_id) }}">
                                                <button type="submit" class="btn btn-outline-info">
                                                    <i class="bi bi-journal-text me-2"></i> Add Academic Details
                                                </button>
                                            </form>
                                        </div>

                                        <div class="col-lg-4 col-md-4 mb-3">
                                            <form action="{{ route('uniform_index.create') }}" method="GET" target="_blank">
                                                <input type="hidden" name="emp_id" value="{{ Crypt::encrypt($employee->emp_id) }}">
                                                <button type="submit" class="btn btn-outline-info">
                                                    <i class="bi bi-box-seam me-2"></i> Add Kit Details
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#update_and_uplode" aria-expanded="false" aria-controls="update_and_uplode">
                                      <h4 class="card-title mb-1  text-primary">Update & Uploads</h4>
                                    </button>
                                    </h2>
                                    <div id="update_and_uplode" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                         <div class="row">
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Aadhar Number <span id="invalid_aadhar"
                                                                class="text-danger ms-2"></span></label>
                                                    <div class="d-flex align-items-center gap-2">        
                                                        <input id="aadhar_number" type="text"  class="form-control" onkeypress="numericOnly(event)" 
                                                        oninput="validatePositiveNumber(this)"
                                                            placeholder="1234 5678 9012"        minlength="12"        maxlength="14"        pattern="\d{4}\s?\d{4}\s?\d{4}" 
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->aadhar_number) ? json_decode($employee->emp_documents_ref_file)->aadhar_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                        
                                                    <span class="text-danger" id="aadhar_numberError"></span>
                                                    <input type="file" class="d-none extract-img" id="upload_aadhar"
                                                        data-default-file="{{ isset($employeeDocuments->aadharUpload) ? asset($employeeDocuments->aadharUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_aadhar_file"
                                                        name="existing_aadhar_file"
                                                        value="{{ isset($employeeDocuments->aadharUpload) ? $employeeDocuments->aadharUpload : '' }}" />
                                                </div>

                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                  
                                                    <button id="aadharUploadBtn" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_aadhar').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                    </button>
            
                                                    @if(isset($employee->emp_aadhar_file) && !empty($employee->emp_aadhar_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_aadhar_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_aadhar_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>

                                                <!-- Driving License Number and Upload -->
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Driving License Number <span
                                                            id="invalid_drivng_license" class="text-danger ms-2"></span></label>
                                                    <div class="d-flex align-items-center gap-2">   
                                                        <input id="drivng_license_number" type="text" class="form-control"
                                                            placeholder="MH12 20210012345"      minlength="13"        maxlength="16" 
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->driving_license_number) ? json_decode($employee->emp_documents_ref_file)->driving_license_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                    <span class="text-danger" id="drivng_license_numberError"></span>

                                                    <input type="file" class="d-none extract-img"  id="upload_drivng_license"
                                                        data-default-file="{{ isset($employeeDocuments->drivingUpload) ? asset($employeeDocuments->drivingUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_driving_license_file"
                                                        name="existing_driving_license_file"
                                                        value="{{ isset($employeeDocuments->passportUpload) ? $employeeDocuments->passportUpload : '' }}" />
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label mb-0 mt-2">Valid Thru</label>
                                                  <div class="d-flex align-items-center gap-2">
                                                    <input type="date"  max="2099-12-31" class="form-control"   value="{{ isset($employee) ? $employee->emp_drivng_license_valid : '' }}"  placeholder="DD-MM-YYY" id="emp_drivng_license_valid">
                                                  </div>
                                                </div>
                                                
                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                        <button id="driving_license_UploadBtn" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_drivng_license').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                        </button>
            
                                                    @if(isset($employee->emp_driving_license_file) && !empty($employee->emp_driving_license_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_driving_license_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_driving_license_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>

                                                <!-- Election Card Number and Upload -->
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Election Card Number <span
                                                            id="invalid_voter_id" class="text-danger ms-2"></span></label>

                                                    <div class="d-flex align-items-center gap-2">       
                                                        <input id="voter_id_number" type="text" class="form-control"
                                                            placeholder="XYZ1234567"      minlength="10"        maxlength="10" 
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->voter_id_number) ? json_decode($employee->emp_documents_ref_file)->voter_id_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                    <span class="text-danger" id="voter_id_numberError"></span>

                                                    <input type="file" class="d-none extract-img" id="upload_voter_id"
                                                        data-default-file="{{ isset($employeeDocuments->voterUpload) ? asset($employeeDocuments->voterUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_voter_id_file"
                                                        name="existing_voter_id_file"
                                                        value="{{ isset($employeeDocuments->voterUpload) ? $employeeDocuments->voterUpload : '' }}" />
                                                </div>

                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                        <button id="upload_voter" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_voter_id').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                        </button>
            
                                                    @if(isset($employee->emp_voter_id_file) && !empty($employee->emp_voter_id_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_voter_id_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_voter_id_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>
                                           
                                                <!-- Passport Number and Upload -->
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Passport Number <span
                                                            id="invalid_passport" class="text-danger ms-2"></span></label>
                                                    <div class="d-flex align-items-center gap-2">       
                                                        <input id="passport_number" type="text" class="form-control"
                                                            placeholder="M1234567"      minlength="8"        maxlength="8" 
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->passport_number) ? json_decode($employee->emp_documents_ref_file)->passport_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                    <span class="text-danger" id="passport_numberError"></span>

                                                    <input type="file" class="d-none extract-img" id="upload_passport"
                                                        data-default-file="{{ isset($employeeDocuments->passportUpload) ? asset($employeeDocuments->passportUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_passport_file"
                                                        name="existing_passport_file"
                                                        value="{{ isset($employeeDocuments->passportUpload) ? $employeeDocuments->passportUpload : '' }}" />
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label mb-0 mt-2">Valid Thru</label>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <input type="date"  max="2099-12-31" class="form-control"   value="{{ isset($employee) ? $employee->emp_passport_valid : '' }}"  placeholder="DD-MM-YYY" id="emp_passport_valid">
                                                    </div>
                                                </div>

                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                        <button id="uplode_passport_file" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_passport').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                        </button>
            
                                                    @if(isset($employee->emp_passport_file) && !empty($employee->emp_passport_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_passport_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_passport_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>

                                                <!-- Bank A/c Number and Upload -->
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Cancelled Cheque <span
                                                            id="invalid_account" class="text-danger ms-2"></span></label>

                                                    <div class="d-flex align-items-center gap-2">       
                                                        <input id="account_number" type="text" class="form-control"
                                                            placeholder=" Cancelled Cheque Number"minlength="9"maxlength="18"  onkeypress="numericOnly(event)"oninput="validatePositiveNumber(this)"
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->account_number) ? json_decode($employee->emp_documents_ref_file)->account_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                    <span class="text-danger" id="account_numberError"></span>

                                                    <input type="file" class="d-none extract-img" id="upload_passbook"
                                                        data-default-file="{{ isset($employeeDocuments->passbookUpload) ? asset($employeeDocuments->passbookUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_passbook_file"
                                                        name="existing_passbook_file"
                                                        value="{{ isset($employeeDocuments->passbookUpload) ? $employeeDocuments->passbookUpload : '' }}" />

                                                </div>

                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                        <button id="uplode_passbook_file" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_passbook').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                        </button>
            
                                                    @if(isset($employee->emp_passbook_file) && !empty($employee->emp_passbook_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_passbook_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_passbook_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>

                                                <!-- PAN Number and Upload -->
                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">PAN Number <span id="invalid_pan"
                                                            class="text-danger ms-2"></span></label>

                                                    <div class="d-flex align-items-center gap-2">       
                                                        <input id="pan_number" type="text" class="form-control"
                                                            placeholder="ABCDE1234F"      minlength="10"        maxlength="10" 
                                                            value="{{ isset($employee) && isset(json_decode($employee->emp_documents_ref_file)->pan_number) ? json_decode($employee->emp_documents_ref_file)->pan_number : '' }}"
                                                            placeholder="">
                                                    </div>
                                                    <span class="text-danger" id="pan_numberError"></span>

                                                    <input type="file" class="d-none extract-img" id="upload_pan"
                                                        data-default-file="{{ isset($employeeDocuments->panUpload) ? asset($employeeDocuments->panUpload) : '' }}"
                                                        data-height="180" accept=".jpg,.jpeg,.png,.pdf,.xlsx,.xls,.doc,.docx" />

                                                    <input type="hidden" id="existing_pan_file" name="existing_pan_file"
                                                        value="{{ isset($employeeDocuments->passbookUpload) ? $employeeDocuments->passbookUpload : '' }}" />
                                                </div>
                                                
                                                <dive  class="col-md-2" style="margin-top: 2.5%;">
                                                    <div class="d-flex">
                                                        <!-- Upload Button -->
                                                        <button id="uploade_pan_file" type="button" class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;"
                                                        onclick="document.getElementById('upload_pan').click();">
                                                        <i class="bi bi-upload" style="font-size: 16px;"></i>
                                                        </button>
            
                                                    @if(isset($employee->emp_pan_file) && !empty($employee->emp_pan_file))

                                                        <!-- View Button -->
                                                        <button type="button" onclick="window.open('{{ asset($employee->emp_pan_file) }}', '_blank')"
                                                                class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                                style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-eye" style="font-size: 16px;"></i>
                                                        </button>

                                                        <!-- Download Button -->
                                                        <a href="{{ asset($employee->emp_pan_file) }}" download
                                                        class="btn btn-outline-info d-flex align-items-center justify-content-center"
                                                        style="width: 36px; height: 36px; padding: 0; border-radius: 10px; box-shadow: 0 0 8px #e0f0ff;">
                                                            <i class="bi bi-download" style="font-size: 16px;"></i>
                                                        </a>
                                                    @endif
                                                    </div>
                                                </dive>
                                            </div>
                                    </div>
                                    </div>
                                </div>

                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-none" id="tab3btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('3','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <button class="btn btn-outline-primary" id="thirdNextBtn"
                                        onclick="saveData('3','1')">Next</button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active d-none" id="tab4">
                            <div class="card-body">
                                <h4 class="card-title mb-1  text-primary">Payment Method</h4> <br>
                                <div class="row mt-3">
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" id="bank" value="bank"
                                                {{ isset($employee) && $employee->emp_paymentmode === 'bank' ? 'checked' : '' }}>
                                             <h4 class="card-title mb-1" for="bank">Bank</h4>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" id="cash" value="cash"
                                                {{ isset($employee) && $employee->emp_paymentmode === 'cash' ? 'checked' : '' }}>
                                             <h4 class="card-title mb-1" for="cash">Cash</h4>

                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment" id="cheque" value="cheque"
                                                {{ isset($employee) && $employee->emp_paymentmode === 'cheque' ? 'checked' : '' }}>
                                             <h4 class="card-title mb-1" for="cheque">Cheque</h4>

                                        </div>
                                    </div>

                                    <span class="text-danger" id="payment_method"></span>
                                </div>



                                <div id="bankDetailsDiv" style="display: none;"> 
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Account Code </label>
                                                <input type="text" class=" form-control"  placeholder=" Account Code" value="{{ isset($employee) ? $employee->emp_account_code : '' }}" id="accountCode" >
                                                <span class="text-danger" id="accountCodeError"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">IFSC Code <span></span></label>
                                                <input type="text" class=" form-control ifsc_api_check"  placeholder=" IFSC Code" value="{{ isset($employee) ? $employee->emp_bank_ifsc_code : '' }}" id="ifsc" onchange="checkIFSC(this.value)" required maxlength="11"> 
                                                <span class="text-danger" id="IFSCError"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Bank Name<span></span></label>
                                                <input type="text" class=" form-control"  placeholder=" Bank Name" value="{{ isset($employee) ? $employee->emp_bank_name : '' }}" id="bankName" >
                                                <span class="text-danger" id="bankNameError"></span>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Branch Name<span></span></label>
                                                <input type="text" class=" form-control"  placeholder=" Branch Name" value="{{ isset($employee) ? $employee->emp_bank_branch_name : '' }}" id="branchName" >
                                                <span class="text-danger" id="branchNameError"></span>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">MICR<span></span></label>
                                                <input type="text" class=" form-control"  placeholder=" MICR" id="micr" value="{{ isset($employee) ? $employee->emp_bank_micr_code : '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Branch Code<span></span></label>
                                                <input type="text" class="form-control"  placeholder=" Branch Code" id="branch_code" value="{{ isset($employee) ? $employee->emp_bank_branch_code : '' }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Bank A/c Number<span></span></label>
                                                <input  type="text"class="form-control"placeholder="Bank A/C Number"maxlength="20"value="{{ isset($employee) ? $employee->emp_bank_account_no : '' }}"id="bank_account_number"pattern="[0-9]+"inputmode="numeric"required/>

                                                <span class="text-danger" id="bankaccountnumberError"></span>
                                            </div>

                                            <script>
                                                const bankInput = document.getElementById('bank_account_number');
                                                const errorSpan = document.getElementById('bankaccountnumberError');

                                                bankInput.addEventListener('input', function () {
                                                    // Remove non-numeric characters
                                                    this.value = this.value.replace(/[^0-9]/g, '');

                                                    if (!this.value) {
                                                        errorSpan.innerHTML = "Bank A/C Number is required.";
                                                    } else {
                                                        errorSpan.innerHTML = "";
                                                    }
                                                });
                                            </script>

                                            <div class="col-md-4">
                                                <label class="form-label mb-0 mt-2">Account Type <span></span></label>
                                                <select class="form-control form-select select2" id="account_type" name="account_type">
                                                    <option value="" disabled {{ !isset($employee) || !$employee->emp_accountpurpose ? 'selected' : '' }}>
                                                        -- Select Account Type --
                                                    </option>
                                                    <option value="salary" {{ (isset($employee) && $employee->emp_accountpurpose == 'salary') ? 'selected' : '' }}>
                                                        Salary Account
                                                    </option>
                                                    <option value="reimbursement" {{ (isset($employee) && $employee->emp_accountpurpose == 'reimbursement') ? 'selected' : '' }}>
                                                        Reimbursement Account
                                                    </option>
                                                    <option value="both" {{ (isset($employee) && $employee->emp_accountpurpose == 'both') ? 'selected' : '' }}>
                                                        Salary / Reimbursement Account
                                                    </option>
                                                </select>
                                                    <span class="text-danger" id="account_typeError"></span>
                                            </div>
                                            <input type="text" class=" form-control" placeholder="" id="branch_code" value="{{ isset($employee) ? $employee->emp_bank_branch_code : '' }}" hidden>
                                        </div>
                                    </div> <br> 
                                </div>

                                {{-- Account Account --}}
                                <div class="accordion-item" style="display: none">
                                    <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#personal_account" aria-expanded="false" aria-controls="personal_account">
                                               <h4 class="card-title mb-1  text-primary">Personal Account</h4>
                                    </button>
                                    </h2>
                                    <div id="personal_account" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                         <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Account Code </label>
                                            <input type="text" class="form-control"  placeholder=" Account Code" value="{{ isset($employee) ? $employee->emp_salary_account_code : '' }}" id="emp_salary_accountCode">
                                            <span class="text-danger" id="accountCodeError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">IFSC Code</label>
                                            <input type="text" class="form-control"  placeholder=" IFSC Code" value="{{ isset($employee) ? $employee->emp_salary_bank_ifsc_code : '' }}" id="emp_salary_ifsc"  onchange="salaryifsc(this.value)">
                                            <span class="text-danger" id="emp_salary_IFSCError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Bank Name</label>
                                            <input type="text" class="form-control"  placeholder=" Bank Name" value="{{ isset($employee) ? $employee->emp_salary_bank_name : '' }}" id="emp_salary_bankName">
                                            <span class="text-danger" id="bankNameError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Branch Name</label>
                                            <input type="text" class="form-control"  placeholder=" Branch Name" value="{{ isset($employee) ? $employee->emp_salary_bank_branch_name : '' }}" id="emp_salary_branchName">
                                            <span class="text-danger" id="branchNameError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">MICR</label>
                                            <input type="text" class="form-control"  placeholder=" MICR" id="emp_salary_micr" value="{{ isset($employee) ? $employee->emp_salary_bank_micr_code : '' }}">
                                            <span class="text-danger" id="emp_salary_micrerError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Branch Code</label>
                                            <input type="text" class="form-control"  placeholder=" Branch Code" id="emp_salary_branch_code" value="{{ isset($employee) ? $employee->emp_salary_bank_branch_code : '' }}">
                                            <span class="text-danger" id="emp_salary_branch_codeerError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Bank A/c Number</label>
                                            <input type="text" class="form-control"  placeholder=" Bank A/C Number" maxlength="20" oninput="validatePositiveNumber(this)" value="{{ isset($employee) ? $employee->emp_salary_bank_account_no : '' }}" id="emp_salary_bank_account_number" onkeypress="numericOnly(event)">
                                            <span class="text-danger" id="bank_account_numberError"></span>
                                        </div>
                                    </div>
                                
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer  d-none" id="tab4btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('4','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="saveData('4','1')">Next</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active d-none" id="tab5">
                            <div class="card-body">
                                <h4 class="card-title mb-1  text-primary p-2">Organization Information</h4>
                                <div class="form-group">
                                    <div class="row">
                                        {{-- <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC  Enable</label>
                                            <select class="form-control form-select select2" aria-label="Type"
                                                id="esic_limit" onchange="handleChange(event)"
                                                data-placeholder="Select ESIC  Enable" required>
                                                <option class="text-muted" value="" label="Select ESIC  Enable">
                                                </option>
                                                @foreach ($getEsicLimit as $item)
                                                    <option value="{{ $item->m_id }}"
                                                        {{ isset($employee) && $employee->emp_esic_limit == $item->m_id ? 'selected' : ($item->m_id == 121 ? 'selected' : '') }}>
                                                        {{ $item->m_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div> --}}
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Branch <span
                                                    class="text-danger">*</span></label>
                                            <select id="branch" class="form-control form-select select2"
                                                data-placeholder="Select Branch" onchange="handleChange(event); updateErrorMessages5(); ">
                                                <option class="text-muted" value="" label="Select Branch">
                                                </option>
                                                {{-- @foreach ($BranchList as $branchlist)
                                                    <option value="{{ $branchlist->br_id }}"
                                                        {{ isset($employee) && $employee->emp_br_id == $branchlist->br_id ? 'selected' : '' }}>
                                                        {{ $branchlist->br_name }}</option>
                                                @endforeach --}}

                                                @foreach ($BranchList as $branchlist)
                                                        <option value="{{ $branchlist->br_id }}"
                                                            {{
                                                                (
                                                                    old('branch')
                                                                    ?? ($employee->emp_br_id ?? null)
                                                                    ?? ($BranchList->count() == 1 ? $branchlist->br_id : null)
                                                                ) == $branchlist->br_id
                                                                ? 'selected' : ''
                                                            }}>
                                                            {{ $branchlist->br_name }}
                                                        </option>
                                                @endforeach

                                            </select>
                                            <span class="text-danger" id="branchError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Department <span
                                                    class="text-danger">*</span></label>
                                            <select id="department" class="form-select form-control select2"
                                                data-placeholder="Select Department">
                                                <option class="text-muted" value="" label="Select Deparment">
                                                </option>
                                                {{-- @foreach ($DepartmentList as $department)
                                                    <option value="{{ $department->d_id }}"
                                                        {{ isset($employee) && $employee->emp_d_id == $department->d_id ? 'selected' : '' }}>
                                                        {{ $department->d_name }}</option>
                                                @endforeach --}}

                                                @foreach ($DepartmentList as $department)
                                                    <option value="{{ $department->d_id }}"
                                                        {{
                                                            (
                                                                old('department')
                                                                ?? ($employee->emp_d_id ?? null)
                                                                ?? ($DepartmentList->count() == 1 ? $department->d_id : null)
                                                            ) == $department->d_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $department->d_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="departmentError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Designation <span
                                                    class="text-danger">*</span></label>
                                            <select id="designation" class="form-control form-select select2"
                                                data-placeholder="Select Designation" onchange="handleChange(event); updateErrorMessages5();">
                                                <option class="text-muted" value="" label="Select Designation">
                                                </option>
                                                {{-- @foreach ($DesignationList as $designation)
                                                    <option value="{{ $designation->dg_id }}"
                                                        {{ isset($employee) && $employee->emp_dg_id == $designation->dg_id ? 'selected' : '' }}>
                                                        {{ $designation->dg_name }}</option>
                                                @endforeach --}}

                                                @foreach ($DesignationList as $designation)
                                                    <option value="{{ $designation->dg_id }}"
                                                        {{
                                                            (
                                                                old('designation')
                                                                ?? ($employee->emp_dg_id ?? null)
                                                                ?? ($DesignationList->count() == 1 ? $designation->dg_id : null)
                                                            ) == $designation->dg_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $designation->dg_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="designationError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Grade <span
                                                    class="text-danger">*</span></label>
                                            <select id="gradeTADA" class="form-control form-select select2"
                                                data-placeholder="Select Grade" onchange="handleChange(event); updateErrorMessages5();">
                                                <option class="text-muted" value="" label="Select Grade">
                                                </option>
                                                {{-- @foreach ($Grade as $grade)
                                                    <option value="{{ $grade->g_id }}"
                                                        {{ isset($employee) && $employee->emp_grade_id == $grade->g_id ? 'selected' : '' }}>
                                                        {{ $grade->g_name }}</option>
                                                @endforeach --}}

                                                @foreach ($Grade as $grade)
                                                    <option value="{{ $grade->g_id }}"
                                                        {{
                                                            (
                                                                old('gradeTADA')
                                                                ?? ($employee->emp_grade_id ?? null)
                                                                ?? ($Grade->count() == 1 ? $grade->g_id : null)
                                                            ) == $grade->g_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $grade->g_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="gradeTADAError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Role <span
                                                    class="text-danger">*</span></label>
                                            <select id="role" class="form-control form-select select2"
                                                data-placeholder="Select Role" onchange="handleChange(event); updateErrorMessages5();">
                                                <option class="text-muted" value="" label="Select Role">
                                                </option>
                                                {{-- @foreach ($Role as $roleitem)
                                                    <option value="{{ $roleitem->role_id }}"
                                                        {{ isset($employee) && $employee->emp_role_id == $roleitem->role_id ? 'selected' : '' }}>
                                                        {{ $roleitem->role_name }}</option>
                                                @endforeach --}}

                                                @foreach ($Role as $roleitem)
                                                    <option value="{{ $roleitem->role_id }}"
                                                        {{
                                                            (
                                                                old('role')
                                                                ?? ($employee->emp_role_id ?? null)
                                                                ?? ($Role->count() == 1 ? $roleitem->role_id : null)
                                                            ) == $roleitem->role_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $roleitem->role_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <span class="text-danger" id="roleError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Reporting Manager <span
                                                    class="text-danger">*</span></label>
                                            <select id="reporting_manager" class="form-control form-select select2"
                                                data-placeholder="Select Reporting Manager"
                                                onchange="handleChange(event); updateErrorMessages5();">
                                                <option class="text-muted" value=""
                                                    label="Select Reporting Manager"></option>
                                                {{-- @foreach ($supervisor as $roleitem)
                                                    <option value="{{ $roleitem->emp_id }}"
                                                        {{ isset($employee) && $employee->emp_supervisor_id == $roleitem->emp_id ? 'selected' : '' }}>
                                                        {{ isset($roleitem->emp_code) ? '(' . $roleitem->emp_code . ')' : '' }}
                                                        {{ $roleitem->emp_full_name }}</option>
                                                @endforeach --}}

                                                @foreach ($supervisor as $roleitem)
                                                    <option value="{{ $roleitem->emp_id }}"
                                                        {{
                                                            (
                                                                old('reporting_manager')
                                                                ?? ($employee->emp_supervisor_id ?? null)
                                                                ?? ($supervisor->count() == 1 ? $roleitem->emp_id : null)
                                                            ) == $roleitem->emp_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ isset($roleitem->emp_code) ? '(' . $roleitem->emp_code . ')' : '' }}
                                                        {{ $roleitem->emp_full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="reportManagerError"></span>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label mb-1 d-flex align-items-center">
                                                <span class="me-2">Cost Center</span>
                                            
                                                <span class="icon-tooltip">
                                                    <i class="fa fa-info-circle text-primary"></i>
                                                    <span class="tooltip-text">Primary assignment</span>
                                                </span>
                                            </label>
                                        
                                            <input type="text"
                                                   class="form-control"
                                                   id="costCenter"
                                                   placeholder="Cost Center"
                                                   value="{{ isset($employee) ? $employee->emp_cost_center : '' }}"
                                                   required>
                                        
                                            <span class="text-danger" id="costCenterError"></span>
                                        </div>
        
                                        <div class="col-md-4">
                                            <label class="form-label mb-1 d-flex align-items-center">
                                                <span class="me-2">Profit Center</span>
                                            
                                                <span class="icon-tooltip">
                                                    <i class="fa fa-info-circle text-primary"></i>
                                                    <span class="tooltip-text">For revenue allocation</span>
                                                </span>
                                            </label>
                                            <input type="text" class="form-control" 
                                                value="{{ isset($employee) ? $employee->emp_profit_center : '' }}"
                                                 placeholder=" Profit Center" id="profitCenter" required>
                                            <span class="text-danger" id="profitCenterError"></span>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label mb-1 d-flex align-items-center">
                                                <span class="me-2">Budget Code (SAP)</span>
                                            
                                                <span class="icon-tooltip">
                                                    <i class="fa fa-info-circle text-primary"></i>
                                                    <span class="tooltip-text">For cost tracking</span>
                                                </span>
                                            </label>
                                            <input type="text" class="form-control" min="0"
                                                value="{{ isset($employee) ? $employee->emp_sap_budget_code : '' }}"
                                                 placeholder=" Budget Code" id="budgetCode" required>
                                            <span class="text-danger" id="budgetCodeError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assigned Region</label>
                                            <select id="assignedRegion" class="form-control form-select select2"
                                                data-placeholder="Select Region" onchange="handleChange(event)">
                                                <option class="text-muted" value="" label="Select Region">
                                                </option>
                                                @foreach ($emp_region as $data)
                                                    <option value="{{ $data->m_id  }}"
                                                        {{ isset($employee) && $employee->emp_region_id == $data->m_id ? 'selected' : '' }}>
                                                        {{ $data->m_name }}</option>
                                                @endforeach
                                            </select>
                                           <span class="text-danger" id="assignedRegionError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assign Project</label>
                                            <select class="form-control form-select select2" id="emp_project_assigned"
                                                name="emp_project_id[]" data-placeholder="Select one or more projects" multiple required>
                                                @foreach ($emp_projects as $project)
                                                    <option value="{{ $project->ps_id }}"
                                                        @if(isset($employee) && is_array($employee->emp_project_id) && in_array($project->ps_id, $employee->emp_project_id)) 
                                                            selected 
                                                        @endif>
                                                        {{ $project->ps_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="emp_project_assigned_error"></span>
                                        </div>

                                        <div class="col-md-4" style="display: none">
                                                <label class="form-label mb-0 mt-2">Assets Assign</label>
                                                <select class="form-control form-select select2" 
                                                        id="emp_assets_assigned"
                                                        name="emp_assets_id[]" 
                                                        multiple required
                                                        data-placeholder="Select one or more assets">

                                                    @foreach ($emp_assets as $asset)
                                                        <option value="{{ $asset->id }}"
                                                            @if(isset($employee) && is_array($employee->emp_assets_id) && in_array($asset->id, $employee->emp_assets_id))
                                                                selected
                                                            @endif>
                                                            {{ $asset->assetType->name }} - {{ $asset->asset_tag }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                         </div>

                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#previous_organization" aria-expanded="false" aria-controls="previous_organization">
                                            <h4 class="card-title mb-1  text-primary">Previous Organization</h4>
                                        </button>
                                    </h2>
                                    <div id="previous_organization" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                        data-bs-parent="#accordionExample">
                                        <div class="accordion-body">

                                            {{-- First Previous Organization --}}
                                            <div class="row">
                                                <input type="hidden" id="po_id"
                                                    value="{{ $previousOrganizations[0]->po_id ?? '' }}">

                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Company Name 1</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[0]->po_company_name ?? '' }}"
                                                         placeholder=" Company Name" id="companyName">
                                                    <span class="text-danger" id="companyNameError"></span>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Designation</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[0]->po_designation_name ?? '' }}"
                                                         placeholder=" Designation" id="designationName">
                                                    <span class="text-danger" id="designationNameError"></span>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Joining Date</label>
                                                    <input type="date" class="form-control"
                                                        value="{{ !empty($previousOrganizations[0]->po_from_date) ? \Carbon\Carbon::parse($previousOrganizations[0]->po_from_date)->format('Y-m-d') : '' }}"
                                                        id="joinDatecompany">
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Leaving Date</label>
                                                    <input type="date" class="form-control"
                                                        value="{{ !empty($previousOrganizations[0]->po_to_date) ? \Carbon\Carbon::parse($previousOrganizations[0]->po_to_date)->format('Y-m-d') : '' }}"
                                                        id="leaveDatecompany">
                                                </div>

                                              <div class="col-md-2">
                                                    <label class="form-label">Service Duration</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[0]->po_serviceduration ?? '' }}"
                                                        id="serviceDuration" 
                                                        name="po_serviceduration"  
                                                        readonly>
                                                </div>

                                            </div>

                                            {{-- Second Previous Organization --}}
                                            <div class="row mt-3">
                                                <input type="hidden" id="po_id2"
                                                    value="{{ $previousOrganizations[1]->po_id ?? '' }}">

                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Company Name 2</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[1]->po_company_name ?? '' }}"
                                                         placeholder=" Company Name" id="companyName2">
                                                    <span class="text-danger" id="companyNameError2"></span>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label mb-0 mt-2">Designation</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[1]->po_designation_name ?? '' }}"
                                                         placeholder=" Designation" id="designationName2">
                                                    <span class="text-danger" id="designationNameError2"></span>
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Joining Date</label>
                                                    <input type="date" class="form-control"
                                                        value="{{ !empty($previousOrganizations[1]->po_from_date) ? \Carbon\Carbon::parse($previousOrganizations[1]->po_from_date)->format('Y-m-d') : '' }}"
                                                        id="joinDatecompany1">
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Leaving Date</label>
                                                    <input type="date" class="form-control"
                                                        value="{{ !empty($previousOrganizations[1]->po_to_date) ? \Carbon\Carbon::parse($previousOrganizations[1]->po_to_date)->format('Y-m-d') : '' }}"
                                                        id="leaveDatecompany1">
                                                </div>

                                                <div class="col-md-2">
                                                    <label class="form-label">Service Duration</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $previousOrganizations[1]->po_serviceduration ?? '' }}"
                                                        id="serviceDuration1" 
                                                        name="po_serviceduration2" 
                                                        readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const companyNameField = document.getElementById('companyName');
                                    const otherFields = [
                                        document.getElementById('designationName'),
                                        document.getElementById('joiningPeriod'),
                                    ];

                                    companyNameField.addEventListener('input', function() {
                                        if (companyNameField.value.trim() !== '') {
                                            otherFields.forEach(field => {
                                                field.setAttribute('required', true);
                                            });
                                        } else {
                                            otherFields.forEach(field => {
                                                field.removeAttribute('required');
                                            });
                                        }
                                    });

                                    const joiningPeriodInput = document.getElementById('joiningPeriod');

                                    flatpickr(joiningPeriodInput, {
                                        mode: 'range', // Enables interval (range) selection
                                        dateFormat: 'Y-m-d', // Format of the selected dates
                                        minDate: '2000-01-01', // Optional: Set a minimum date
                                        maxDate: '2099-12-31', // Optional: Set a maximum date
                                        // defaultDate: [ // Optional: Set default dates
                                        //     new Date().toISOString().split('T')[0], // Today's date
                                        //     new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().split('T')[0] // A week from today
                                        // ],
                                        onChange: function(selectedDates, dateStr, instance) {
                                            if (selectedDates.length === 2) {
                                                const startDate = selectedDates[0];
                                                const endDate = selectedDates[1];

                                                // Calculate the difference
                                                const diff = calculateDateDifference(startDate, endDate);
                                                $('#joiningPeriodDuration').text(diff);
                                                // Display the result (e.g., 1 year 5 months 23 days)
                                            }
                                        }
                                    });

                                    const companyNameField2 = document.getElementById('companyName2');
                                    const otherFields2 = [
                                        document.getElementById('designationName2'),
                                        document.getElementById('joiningPeriod2'),
                                    ];

                                    companyNameField2.addEventListener('input', function() {
                                        if (companyNameField2.value.trim() !== '') {
                                            otherFields2.forEach(field => {
                                                field.setAttribute('required', true);
                                            });
                                        } else {
                                            otherFields2.forEach(field => {
                                                field.removeAttribute('required');
                                            });
                                        }
                                    });

                                    const joiningPeriodInput2 = document.getElementById('joiningPeriod2');

                                    flatpickr(joiningPeriodInput2, {
                                        mode: 'range', // Enables interval (range) selection
                                        dateFormat: 'Y-m-d', // Format of the selected dates
                                        minDate: '2000-01-01', // Optional: Set a minimum date
                                        maxDate: '2099-12-31', // Optional: Set a maximum date
                                        // defaultDate: [ // Optional: Set default dates
                                        //     new Date().toISOString().split('T')[0], // Today's date
                                        //     new Date(new Date().setDate(new Date().getDate() + 7)).toISOString().split('T')[0] // A week from today
                                        // ],
                                        onChange: function(selectedDates, dateStr, instance) {
                                            if (selectedDates.length === 2) {
                                                const startDate = selectedDates[0];
                                                const endDate = selectedDates[1];

                                                // Calculate the difference
                                                const diff = calculateDateDifference(startDate, endDate);
                                                $('#joiningPeriodDuration2').text(diff);

                                                // Display the result (e.g., 1 year 5 months 23 days)
                                            }
                                        }
                                    });

                                    // Function to calculate the difference in years, months, and days
                                    function calculateDateDifference(startDate, endDate) {
                                        const years = endDate.getFullYear() - startDate.getFullYear();
                                        let months = endDate.getMonth() - startDate.getMonth();
                                        let days = endDate.getDate() - startDate.getDate();

                                        // If months are negative, we need to adjust the year difference
                                        if (months < 0) {
                                            months += 12;
                                        }

                                        // If days are negative, we need to adjust the month difference
                                        if (days < 0) {
                                            months--;
                                            const prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
                                            days += prevMonth.getDate(); // Get the number of days in the previous month
                                        }

                                        // Construct the result string
                                        let result = '';

                                        if (years > 0) result += `${years} year${years > 1 ? 's' : ''} `;
                                        if (months > 0) result += `${months} month${months > 1 ? 's' : ''} `;
                                        if (days > 0) result += `${days} day${days > 1 ? 's' : ''}`;

                                        return result.trim();
                                    }
                                });
                            </script>
                        </div>

                        <div class="card-footer d-none" id="tab5btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('5','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="saveData('5','1')">Next</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active d-none" id="tab6">
                            <div class="card-body">
                                <h4 class="card-title mb-1  text-primary">Attendance Information</h4>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assign Policy<span
                                                    class="text-danger">*</span></label>
                                            <select id="attendancePolicy" class="form-control form-select select2"
                                                data-placeholder="Select Attendance Policy"
                                                onchange="handleChange(event); updateErrorMessagesstate6();">
                                                <option class="text-muted" value=""
                                                    label="Select Attendance Policy"></option>
                                                {{-- @foreach ($attendancePolicy as $apolicy)
                                                    <option value="{{ $apolicy->ap_id }}" data-checkMethod=""
                                                        {{ isset($employee) && $employee->emp_ap_id == $apolicy->ap_id ? 'selected' : '' }}>
                                                        {{ $apolicy->ap_name }}
                                                    </option>
                                                @endforeach --}}

                                                @foreach ($attendancePolicy as $apolicy)
                                                    <option value="{{ $apolicy->ap_id }}" data-checkMethod=""
                                                        {{
                                                            (
                                                                old('emp_ap_id')
                                                                ?? ($employee->emp_ap_id ?? null)
                                                                ?? ($attendancePolicy->count() == 1 ? $apolicy->ap_id : null)
                                                            ) == $apolicy->ap_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $apolicy->ap_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <span class="text-danger" id="attendancePolicyError"></span>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="row">
                                                <label class="form-label mb-0 mt-2">Assign Check In Method <span
                                                        class="text-danger">*</span></label>
                                                <div class="col">
                                                    @foreach ($checkInMethod as $index => $method)
                                                        <label class="custom-control custom-checkbox d-inline-block me-3">
                                                            <input type="checkbox" class="custom-control-input"
                                                                master="{{ $method->m_id }}" name="checkInMethod[]"
                                                                id="checkInMethodID{{ $index + 1 }}"
                                                                {{ isset($employee) && in_array($method->m_id, $employee->emp_checkin_method_id ?? []) ? 'checked' : '' }}
                                                                 ">
                                                            <span
                                                                class="custom-control-label"></span>{{ $method->m_name }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <span class="text-danger" id="checkInMethodError2"></span>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assign Shift <span
                                                    class="text-danger">*</span></label>
                                            <select id="asignShift" class="form-control form-select select2"
                                                data-placeholder="Select Shift Type" onchange="handleChange(event); updateErrorMessagesstate6();">
                                                <option class="text-muted" value="" label="Select Shift Type">
                                                </option>
                                                {{-- @foreach ($ShiftType as $shift)
                                                    <option value="{{ $shift->pst_id }}"
                                                        {{ isset($employee) && $employee->emp_shift_type_id == $shift->pst_id ? 'selected' : '' }}>
                                                        {{ $shift->pst_name }}
                                                    </option>
                                                @endforeach --}}

                                                @foreach ($ShiftType as $shift)
                                                    <option value="{{ $shift->pst_id }}"
                                                        {{
                                                            (
                                                                old('asignShift')
                                                                ?? ($employee->emp_shift_type_id ?? null)
                                                                ?? ($ShiftType->count() == 1 ? $shift->pst_id : null)
                                                            ) == $shift->pst_id
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $shift->pst_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="asignShiftError"></span>
                                        </div>

                              <div class="col-md-4">
    <label class="form-label mb-0 mt-2">Assign Mode <span class="text-danger">*</span></label>
    <select class="form-control form-select select2 custom-select"
        id="attendanceMethod"
        data-placeholder="Select Mode"
        onchange="updateGeofencing(this); handleChange(event); checkInMethodCheckbox(this); updateErrorMessagesstate6();">
        <option class="text-muted" value="" label="Select Attendance Method"></option>
        @foreach ($attendanceMethod as $method)
            <option value="{{ $method->m_id }}"
                {{ isset($employee) && $employee->emp_work_mode_id == $method->m_id ? 'selected' : '' }}>
                {{ $method->m_name }}
            </option>
        @endforeach
    </select>
    <span class="text-danger" id="attendanceMethodError"></span>
</div>


                                  <div class="col-md-4">
    <label class="form-label mb-0 mt-2">Geofencing <span class="text-danger">*</span></label>
    <select id="geofencingId" class="form-control form-select select2" 
            data-placeholder="Select Geofencing"
            onchange="handleChange(event); updateErrorMessagesstate6();">
        @foreach (['1' => 'Active', '0' => 'Inactive'] as $key => $item)
            <option value="{{ $key }}"
                {{ isset($employee) && $employee->emp_is_geofencing_active == $key ? 'selected' : '' }}>
                {{ $item }}
            </option>
        @endforeach
    </select>
    <span class="text-danger" id="geofencingIdError"></span>
</div>


                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Weekoff<span
                                                    class="text-danger">*</span></label>
                                            <select id="weekOffId" class="form-control form-select select2"
                                                data-placeholder="Select Week off" onchange="handleChange(event); updateErrorMessagesstate6();">
                                                <option class="text-muted" value="" label="Select Week off">
                                                </option>
                                                {{-- @foreach ($weekOffs as $key => $wof)
                                                    <option value="{{ $key }}"
                                                        {{ isset($employee) && $employee->emp_pwo_id == $key ? 'selected' : '' }}>
                                                        {{ $wof }}
                                                    </option>
                                                @endforeach --}}

                                                
                                                @foreach ($weekOffs as $key => $wof)
                                                    <option value="{{ $key }}"
                                                        {{
                                                            (
                                                                old('weekOffId')
                                                                ?? ($employee->emp_pwo_id ?? null)
                                                                ?? (count($weekOffs) == 1 ? $key : null)
                                                            ) == $key
                                                            ? 'selected' : ''
                                                        }}>
                                                        {{ $wof }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="weekOffIdError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">GeoWork <span class="text-danger">*</span></label>
                                            <select id="geoworkId" class="form-control form-select select2" data-placeholder="Select GeoWork"
                                                onchange="handleChange(event); updateErrorMessagesstate6();">
                                                <option class="text-muted" value="" label="Select GeoWork"></option>
                                                @foreach (['1' => 'Active', '0' => 'Inactive'] as $key => $item)
                                                <option value="{{ $key }}" {{ isset($employee) && $employee->emp_is_geowork_active == $key ? 'selected' : '' }}>
                                                    {{ $item }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="geoworkIdError"></span>
                                        </div>

                                        @php
                                            // Normalize employee selected branch IDs
                                            if (isset($employee)) {
                                                // Case 1: If you got a collection of Branch models
                                                if ($employee->emp_assign_geo_branch instanceof \Illuminate\Support\Collection) {
                                                    $selectedBranchIds = $employee->emp_assign_geo_branch->pluck('br_id')->toArray();
                                                }
                                                // Case 2: If stored as JSON in DB
                                                else {
                                                    $selectedBranchIds = json_decode($employee->emp_assign_geo_branch ?? '[]', true);
                                                }
                                            } else {
                                                $selectedBranchIds = [];
                                            }
                                        @endphp

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Assign Geo Branch</label>
                                            <select id="assign_geo_branch"
                                                    class="form-control form-select select2"
                                                    multiple
                                                    data-placeholder="Select Branch"
                                                    onchange="handleChange(event);">
                                                @foreach ($BranchList as $branchlist)
                                                    <option value="{{ $branchlist->br_id }}"
                                                        @if (in_array($branchlist->br_id, $selectedBranchIds))
                                                            selected
                                                        @endif>
                                                        {{ $branchlist->br_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                            <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Offline Sync</label>
                                            <div class="mt-2">
                                                <input type="hidden" name="emp_offline_status" id="emp_offline_status" value="{{ isset($employee) && $employee->emp_offline_status == 1 ? '1' : '0' }}">
                                                <label class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" 
                                                        id="offlineSyncCheckbox" 
                                                        value="1"
                                                        {{ isset($employee) && $employee->emp_offline_status == 1 ? 'checked' : '' }}
                                                                        onchange="document.getElementById('emp_offline_status').value = this.checked ? '1' : '0'; handleChange(event);">
                                                    <span class="custom-control-label">Enable Offline Sync</span>
                                                </label>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <label class="form-label mb-0 mt-2">Preference <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-inline-block d-flex">
                                                {{-- @dd($employee->emp_attendance_preference); --}}
                                                @foreach ($attendancePreference as $key => $item)
                                                    <label class="custom-control custom-radio me-3">
                                                        <input type="radio" class="custom-control-input"
                                                            id="attendancePreferenceID{{ $key }}"
                                                            name="emp_attendance_preference"
                                                            value="{{ $key }}"
                                                            {{ $key === (isset($employee) ? $employee->emp_attendance_preference : 367) ? 'checked' : '' }}
                                                            onchange="handleChange(event); updateErrorMessagesstate6();">

                                                        {{-- <input type="radio" class="custom-control-input" name="attendance_preference" value="{{ $key }}" {{ $key === (isset($employee) && $employee->emp_attendance_preference) ? 'checked' : '' }}> --}}
                                                        <span class="custom-control-label">{{ $item }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <span class="text-danger" id="attendancePreferenceErrorID"></span>
                                        </div>
                                    </div>
                                </div>

                                <h4 class="card-title mb-1  text-primary">Leave Information</h4>
                                <div class="form-group">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Leave Assign Policy <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2 custom-select"
                                                id="leavePolicy" data-placeholder="Select Leave Policy"
                                                onchange="handleChange(event); updateErrorMessagesstate6();">
                                                <option class="text-muted" value="" label="Select Leave Policy">
                                                </option>
                                                @foreach ($leavePolicy as $policy)
                                                    <option value="{{ $policy->pl_id }}"
                                                        {{ isset($employee) && $employee->emp_pl_id == $policy->pl_id ? 'selected' : '' }}>
                                                        {{ $policy->pl_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="leavePolicyError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Leave credit on pro-rata<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2 custom-select"
                                                id="joiningLeave" data-placeholder="Select Joining Leave"
                                                onchange="handleChange(event); toggleJoiningLeaveMethod(this.value); updateErrorMessagesstate6();">
                                                <option class="text-muted" value=""
                                                    label="Select Joining Leave"></option>
                                                <option value="1"
                                                    {{ isset($employee) && $employee->emp_allow_joining_leave == 1 ? 'selected' : '' }}>
                                                    Allowed</option>
                                                <option value="0"
                                                    {{ isset($employee) && $employee->emp_allow_joining_leave == 0 ? 'selected' : '' }}>
                                                    Not Allowed</option>
                                            </select>
                                            <span class="text-danger" id="joiningLeaveError"></span>
                                        </div>

                                        <div class="col-md-4" id="calculationMethodDiv" style="display: none">
                                            <label class="form-label mb-0 mt-2"> Joining Leave Calculation Method <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2 custom-select"
                                                id="calculationMethod" data-placeholder="Select Joining Method"
                                                onchange="handleChange(event); toggleDateInput(this.value); updateErrorMessagesstate6();">
                                                <option class="text-muted" value=""
                                                    label="Select Calculation Method"></option>
                                                @foreach ($leaveCalcBy as $method)
                                                    <option value="{{ $method->m_id }}"
                                                        {{ isset($employee) && $employee->emp_joining_leave_calc_type == $method->m_id ? 'selected' : '' }}>
                                                        {{ $method->m_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="calculationMethodError"></span>
                                        </div>

                                        <div class="col-md-4" id="dateInputDiv" style="display: none;">
                                            <label class="form-label mb-0 mt-2">Leave Applicable Date (1-29) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="applicableDate"
                                                min="1" max="29"  placeholder=" date (1-29)"
                                                oninput="handleChange(event); validateDateInput(this) ; updateErrorMessagesstate6();"
                                                value="{{ isset($employee->emp_joining_leave_before_date) ? $employee->emp_joining_leave_before_date : '' }}" />
                                            <span class="text-danger" id="applicableDateError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Probation leave on pro-rata <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2 custom-select"
                                                id="probationLeave" data-placeholder="Select Probation Leave"
                                                onchange="handleChange(event) ; updateErrorMessagesstate6();">
                                                <option class="text-muted" value=""
                                                    label="Select Probation Leave"></option>
                                                <option value="1"
                                                    {{ isset($employee) && $employee->emp_allow_probation_leave == 1 ? 'selected' : '' }}>
                                                    Allowed</option>
                                                <option value="0"
                                                    {{ isset($employee) && $employee->emp_allow_probation_leave == 0 ? 'selected' : '' }}>
                                                    Not Allowed</option>
                                            </select>
                                            <span class="text-danger" id="probationLeaveError"></span>
                                        </div>

                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const joiningLeaveValue = document.getElementById("joiningLeave").value;
                                                toggleJoiningLeaveMethod(joiningLeaveValue);
                                                // const calculationMethod = document.getElementById("calculationMethod").value;
                                                // // toggleDateInput(calculationMethod);
                                                // const applicableDate = document.getElementById("applicableDate").value;


                                                // let calculationMethodVal = calculationMethod ? calculationMethod : '';
                                                // let applicableDateVal = applicableDate ? applicableDate : '';
                                                // $('#calculationMethod').val(calculationMethodVal);
                                                // $('#applicableDate').val(applicableDateVal);
                                            });

                                            function toggleJoiningLeaveMethod(value) {
                                                const calculationMethodDiv = document.getElementById("calculationMethodDiv");
                                                document.getElementById("calculationMethodError").textContent = '';

                                                const dateInputDiv = document.getElementById("dateInputDiv");
                                                const applicableDate = document.getElementById("applicableDate");
                                                if (calculationMethodDiv) {
                                                    calculationMethodDiv.style.display = (value === "1") ? "block" : "none";
                                                }

                                                const calculationMethod = document.getElementById("calculationMethod").value;
                                                let applicableDateVal = applicableDate ? applicableDate : '';

                                                if (dateInputDiv) {
                                                    if (value === "1" && calculationMethod === "366") {
                                                        dateInputDiv.style.display = "block";
                                                        $('#applicableDate').attr("required", true);
                                                    } else {
                                                        $('#applicableDate').val(applicableDateVal);
                                                        dateInputDiv.style.display = "none";
                                                        $('#applicableDate').removeAttr("required");
                                                    }
                                                }
                                                // dateInputDiv.style.display = "none";
                                            }

                                            function toggleDateInput(selectedValue) {
                                                const dateInputDiv = document.getElementById("dateInputDiv");
                                                dateInputDiv.style.display = (selectedValue === "366") ? "block" : "none";
                                            }

                                            function validateDateInput(input) {
                                                let value = parseInt(input.value, 10);
                                                if (value < 1) input.value = 1;
                                                else if (value > 29) input.value = 29;
                                                else input.value = value.toString().slice(0, 2);
                                            }
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer  d-none" id="tab6btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('6','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="saveData('6','1')">Next</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active d-none" id="tab7">
                            <div class="card-body">
                                <h4 class="card-title mb-1  text-primary">Joining Details</h4>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2" id="status"
                                                aria-label="Type" onchange="handleChange(event); updateErrorMessages7();" name="status"
                                                data-placeholder="Select Status" required>
                                                <option class="text-muted" value=""
                                                    label="Select Employee Staus">
                                                </option>
                                                       @foreach ($emp_status as $item)
                                                         <option value="{{ $item->m_id }}"
                                                        {{ isset($employee) && $employee->emp_status == $item->m_id ? 'selected' : '' }}>
                                                        {{ $item->m_name }}</option>
                                                    @endforeach
                                            </select>
                                            <span class="text-danger" id="statusError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Employee Type <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2" id="contractType"
                                                aria-label="Type" onchange="handleChange(event); updateErrorMessages7(); " name="contract"
                                                data-placeholder="Select Employee Type" required>
                                                <option class="text-muted" value=""
                                                    label="Select Contract Type">
                                                </option>
                                                @foreach ($employeeType as $empType)
                                                    <option value="{{ $empType->m_id }}"
                                                        {{ isset($employee) && $employee->emp_type_id == $empType->m_id ? 'selected' : '' }}>
                                                        {{ $empType->m_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="contractTypeError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Date Of Joining <span
                                                    class="text-danger">*</span></label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_date_of_joining : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfJoin" required>
                                            <span class="text-danger" id="dateOfJoinError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Job Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2" id="employeeJobStatus"
                                                data-placeholder="Select Job Staus" onchange="handleChange(event); updateErrorMessages7();"
                                                required>
                                                <option value="" selected label="Select Job Status"></option>
                                                @foreach ($employeeJobStatus as $item)
                                                    <option value="{{ $item->m_id }}"
                                                        {{ isset($employee) && $employee->emp_job_status == $item->m_id ? 'selected' : '' }}>
                                                        {{ $item->m_name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="employeeJobStatusError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Date Of Group Joining</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_group_date_of_joining : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfGroupJoin" required>
                                            <span class="text-danger" id="dateOfGroupJoinError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Gratuity Start Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_date_of_gratuity : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfGratuity" required>
                                            <span class="text-danger" id="dateOfGratuityError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Transfer Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_date_of_transfer : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfTransfer" required>
                                            <span class="text-danger" id="dateOfTransferError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Expected Confirmation Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_date_of_expected_confirmation : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfExpectedConfirmation" required>
                                            <span class="text-danger" id="dateOfExpectedConfirmationError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Probation Period In Days</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" min="0"
                                                        oninput="validatePositiveNumber(this)"
                                                        value="{{ isset($employee) ? $employee->emp_probation_period : '180' }}"
                                                        maxlength="4" placeholder="Probation Period"
                                                        id="probationPeriod" onkeypress="numericOnly(event)"  onchange="updateConfirmationDate()">
                                                    <span class="text-muted"></span>
                                                    <span class="text-danger" id="probationPeriodError"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Confirmation Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_date_of_confirmation : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfConfirmation" required>
                                            <span class="text-danger" id="dateOfConfirmationError"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Pay Structure Applied From Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee->latest_salary_master_history) && $employee->latest_salary_master_history->wef ? \Carbon\Carbon::parse($employee->latest_salary_master_history->wef)->format('Y-m-d') : '' }}"
                                                placeholder="DD-MM-YYY" id="dateOfPayStructure" required>
                                            <span class="text-danger" id="dateOfPayStructureError"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-none" id="tab7btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('7','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary" id="nextBtn7"
                                        onclick="saveData('7','1')">Next</a>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane active d-none" id="tab8">
                            <div class="card-body">
                                <h4 class="card-title mb-1 p-2 text-primary">PF & ESIC Details</h4>
                                <div class="form-group">
                                    <div class="row">

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">PF Enable <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2" aria-label="Type"
                                                id="pf_enable" onchange="handleChange(event); handlePfEsicChange();"
                                                data-placeholder="Select PF Enable" required>
                                                <option class="text-muted" value="" label="Select PF Enable">
                                                </option>
                                                @foreach ($getEsicLimit as $item)
                                                    <option value="{{ $item->m_id }}"
                                                        @if (isset($employee) && $employee->emp_is_pf_enabled == $item->m_id) selected
                                                    @elseif (!isset($employee) && $item->m_id == 121)
                                                        selected @endif>
                                                        {{ $item->m_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                            <span class="text-danger" id="pf_enable_error"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">EPS Enable</label>
                                            <select class="form-control form-select select2" aria-label="Type" id="eps_enabled" onchange="handleChange(event);" data-placeholder="Select EPS Enable" required>
                                                <option class="text-muted" value="" label="Select EPS  Enable"> </option>
                                                @foreach ($getEsicLimit as $item)
                                                <option value="{{ $item->m_id }}"
                                                    @if (isset($employee) && $employee->emp_is_eps_enabled == $item->m_id) selected
                                                    @elseif (!isset($employee) && $item->m_id == 121)
                                                    selected @endif>
                                                    {{ $item->m_name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <span class="text-danger" id="eps_error"></span>
                                        </div>

                                       



                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">PF Trust Code<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                 placeholder=" PF Trust Code" id="pfTrustCode"
                                                value="{{ isset($employee) ? $employee->emp_pf_trust_code : '' }}">
                                            <span class="text-danger" id="pfTrustCodeError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Pension Fund Member<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class=" form-control"
                                                 placeholder=" Pension Found Number"
                                                value="{{ isset($employee) ? $employee->emp_pf_found_member : '' }}"
                                                id="pfFoundMember">
                                            <span class="text-danger" id="pfFoundMemberError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">PF Number<span class="text-danger"id="pf_err_span"></span></label>
                                            <input type="text" class=" form-control" placeholder=" PF Number"value="{{ isset($employee) ? $employee->emp_pf_no : '' }}"id="PfNumber" required>
                                            <span class="text-danger" id="PfNumberError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Universal Account Number<span
                                                    class="text-danger"></span></label>
                                                    <input type="text" class="form-control"
                                                        oninput="validatePositiveNumber(this)"
                                                         placeholder=" Notice Period Required Days" maxlength="12"
                                                        min="0"
                                                        value="{{ isset($employee) ? $employee->emp_pf_universal_ac_no : '' }}"
                                                        id="universalAccountNumber" onkeypress="numericOnly(event)">
                                            <span class="text-danger" id="universalAccountNumberError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">VPF(%)<span
                                                    class="text-danger"></span></label>
                                            <input type="text" class="form-control"oninput="validatePositiveNumber(this)"  placeholder=" VPF" maxlength="3"
                                                        min="0"value="{{ isset($employee) ? $employee->emp_vpf_percentage : '' }}"id="vpfPercentage" onkeypress="numericOnly(event)">
                                            <span class="text-danger" id="vpfPercentageError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">PF Date of Joining <span class="text-danger"
                                                    id="pf_err_span"></span></label></label>
                                          <input type="date"  max="2099-12-31" class="form-control  "
                                                value="{{ isset($employee) ? $employee->emp_pf_joining_date : '' }}"
                                                placeholder="DD-MM-YYY" id="pfDateOfJoining" required>
                                            <span class="text-danger" id="pfDateOfJoiningError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">PF Date of Leaving</label>
                                          <input type="date"  max="2099-12-31" class="form-control  "
                                                value="{{ isset($employee) ? $employee->emp_pf_leaving_date : '' }}"
                                                placeholder="DD-MM-YYY" id="pfDateOfLeaving" required>
                                            <span class="text-danger" id="pfDateOfLeavingError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Reason of Leaving PF</label>
                                            <input type="text" class=" form-control"
                                                 placeholder=" Reason of Leaving PF"
                                                value="{{ isset($employee) ? $employee->emp_pr_leaving_reason : '' }}"
                                                id="reasonOfLeavingPF">
                                            <span class="text-danger" id="reasonOfLeavingPFError"></span>
                                        </div>
                                    </div>
                                </div>
                                <h4 class="card-title mb-1 p-2 text-primary">ESIC</h4>
                                <div class="form-group">
                                    <div class="row">

                                          <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC Enable <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2" aria-label="Type"
                                                id="esic_limit" onchange="handleChange(event); handlePfEsicChange();"
                                                data-placeholder="Select ESIC  Enable" required>
                                                <option class="text-muted" value="" label="Select ESIC  Enable">
                                                </option>


                                                @foreach ($getEsicLimit as $item)
                                                    <option value="{{ $item->m_id }}"
                                                        @if (isset($employee) && $employee->emp_esic_limit == $item->m_id) selected
                                                    @elseif (!isset($employee) && $item->m_id == 121)
                                                        selected @endif>
                                                        {{ $item->m_name }}
                                                    </option>
                                                @endforeach


                                            </select>
                                            <span class="text-danger" id="esic_limit_error"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC Number<span class="text-danger"
                                                    id="esic_err_span"></span></label>
                                            <input type="text" class=" form-control"
                                                 placeholder=" ESIC Number"
                                                value="{{ isset($employee) ? $employee->emp_esic_no : '' }}"
                                                id="esiNumber" required>
                                            <span class="text-danger" id="esiNumberError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC Dispensary</label>
                                            <input type="text" class=" form-control"
                                                 placeholder=" ESIC Dispensary"
                                                value="{{ isset($employee) ? $employee->emp_esic_dispensary : '' }}"
                                                id="esiDespensary">
                                            <span class="text-danger" id="esiDespensaryError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC Date of Joining <span class="text-danger"
                                                    id="esic_err_span"></span></label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_esic_joining_date : '' }}"
                                                placeholder="DD-MM-YYY" id="esiDateOfJoining" required>
                                            <span class="text-danger" id="esiDateOfJoiningError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">ESIC Date of Leaving</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_esic_leaving_date : '' }}"
                                                placeholder="DD-MM-YYY" id="esiDateOfLeaving" required>
                                            <span class="text-danger" id="esiDateOfLeavingError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Reason of Leaving ESIC</label>

                                                       <select class="form-control form-select select2" id="reasonOfLeavingESIC"
                                                aria-label="Type" onchange="handleChange(event)" name="reasonOfLeavingESIC"
                                                data-placeholder="Select Status" required>
                                                <option class="text-muted" value=""
                                                    label="Reason of Leaving ESIC">
                                                </option>

                                                  @foreach ($emp_esic_reason as $item)
                                                         <option value="{{ $item->m_id }}"
                                                        {{ isset($employee) && $employee->emp_esic_leaving_reason == $item->m_id ? 'selected' : '' }}>
                                                        {{ $item->m_name }}</option>
                                                    @endforeach

                                            </select>

                                            <span class="text-danger" id="reasonOfLeavingESICError"></span>
                                        </div>
                                    </div>
                                </div> 
                              
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse" data-bs-target="#insurance" aria-expanded="false" aria-controls="insurance">
                                        <h4 class="card-title mb-1  text-primary">Group Insurance</h4>
                                    </button>
                                    </h2>
                                    <div id="insurance" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Insured By</label>
                                            <input type="text" class="form-control"
                                                id="emp_group_insured_by"
                                                 placeholder=" Insurer Name"
                                                value="{{ isset($employee) ? $employee->emp_group_insured_by : '' }}">
                                            <span class="text-danger" id="groupInsuredByError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Insurance Number</label>
                                            <input type="text" class="form-control"
                                                id="emp_group_insurance_no"
                                                 placeholder=" Insurance Number"
                                                value="{{ isset($employee) ? $employee->emp_group_insurance_no : '' }}">
                                            <span class="text-danger" id="groupInsuranceNumberError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Valid From</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                id="emp_group_insurance_start_date"
                                                value="{{ isset($employee) ? $employee->emp_group_insurance_start_date : '' }}">
                                            <span class="text-danger" id="groupInsuranceStartDateError"></span>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Valid Thru</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                id="emp_group_insurance_till_date"
                                                value="{{ isset($employee) ? $employee->emp_group_insurance_till_date : '' }}">
                                            <span class="text-danger" id="groupInsuranceTillDateError"></span>
                                        </div>
                                    </div>
                                
                                    </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="card-footer d-none" id="tab8btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('8','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="saveData('8','1')">Next</a>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active d-none" id="tab9">
                            <div class="card-body">
                                <h4 class="card-title mb-1  text-primary">Separation Details</h4>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Year of Service </label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control"
                                                         placeholder=" Year of Service"
                                                        value="{{ isset($formatted) ? $formatted : '' }}"
                                                        id="yearOfService" readonly>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Retirement Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_retirement_date : '' }}"
                                                placeholder="DD-MM-YYY" id="retirementDate" required>
                                        </div>
                                        <div class="col-md-4">
                                        <label class="form-label mb-0 mt-2">Separation Submit On</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_separation_submit_date : '' }}"
                                                placeholder="DD-MM-YYY" id="separationSubmitDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Expected Leaving Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_expected_leaving_date : '' }}"
                                                placeholder="DD-MM-YYY" id="expectedLeavingDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Leaving Date As Per Notice Period</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_leaving_date_as_per_notice_period : '' }}"
                                                placeholder="DD-MM-YYY" id="leavingDateAsPerNoticePeriod" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Notice Period Required Days </label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control"
                                                        oninput="validatePositiveNumber(this)"
                                                         placeholder=" Notice Period Required Days" maxlength="4"
                                                        min="0"
                                                        value="{{ isset($employee) ? $employee->emp_notice_period_req_days : '' }}"
                                                        id="noticePeriodRequiredDate" onkeypress="numericOnly(event)">
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                        <label class="form-label mb-0 mt-2">Reason For Leaving</label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                      <select class="form-control form-select select2" id="reasonForLeave" name="reasonForLeave">
                                                        <option value="">Select Reason</option>
                                                         @foreach ($emp_leaving as $item)
                                                         <option value="{{ $item->m_id }}"
                                                        {{ isset($employee) && $employee->emp_leaving_reason == $item->m_id ? 'selected' : '' }}>
                                                        {{ $item->m_name }}</option>
                                                        @endforeach

                                                    </select>
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Leaving Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_leave_date : '' }}"
                                                placeholder="DD-MM-YYY" id="leaveDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Notice Period Served Days </label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control"
                                                         placeholder=" Notice Period Served Days"
                                                        value="{{ isset($employee) ? $employee->emp_notice_period_serve_days : '' }}"
                                                        id="noticePeriodServedDate"
                                                        oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);">
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Settlement From</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_settlement_from_date : '' }}"
                                                placeholder="DD-MM-YYY" id="settlementFrom" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Final Settlement Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_final_settlement_date : '' }}"
                                                placeholder="DD-MM-YYY" id="finalSettlementDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Notice Period Shorftfall Days </label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control"
                                                         placeholder=" Notice Period Shorftfall Days"
                                                        value="{{ isset($employee) ? $employee->emp_notice_period_shortfall_days : '' }}"
                                                        id="noticePeriodShortfallDays"
                                                        oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);">
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Exit Interview Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_exit_interview_date : '' }}"
                                                placeholder="DD-MM-YYY" id="exitInterviewDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2">Last Working Date</label>
                                          <input type="date"  max="2099-12-31" class="form-control"
                                                value="{{ isset($employee) ? $employee->emp_last_working_date : '' }}"
                                                placeholder="DD-MM-YYY" id="lastWorkingDate" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Remark </label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control"
                                                         placeholder=" Remark"
                                                        value="{{ isset($employee) ? $employee->emp_remark : '' }}"
                                                        id="remark">
                                                    <span class="text-muted"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Notice Period For Employer <span
                                                    class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" placeholder="Notice Period For Employer" required
                                                        value="{{ isset($employee) ? $employee->emp_notice_period_day_for_employer : '' }}"
                                                        id="employerNoticePeriod"
                                                        oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);">
                                                    <span class="text-muted"></span>
                                                       <span class="text-danger" id="employerNoticePeriodError"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label mb-0 mt-2"> Notice Period For Employee<span
                                                    class="text-danger">*</span></label>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" placeholder="Notice Period For Employee" value="{{ isset($employee) ? $employee->emp_notice_period_day_for_employee : '' }}"
                                                        id="employeeNoticePeriod"
                                                        oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);">
                                                    <span class="text-muted"></span>
                                                       <span class="text-danger" id="employeeNoticePeriodError"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-none" id="tab9btns">
                            <div class="row">
                                <div class="col text-left">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary"
                                        onclick="previousData('9','1')">Previous</a>
                                </div>
                                <div class="col text-end">
                                    <a href="javascript:void(0);" class="btn btn-outline-primary" id="tab9btns"
                                        onclick="saveData('9','1')">Next</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane active d-none" id="tab10">
            <div class="card user-pro-list overflow-hidden">
                <div class="card-body">
                    <div class="user-pic text-center">
                        <!-- <span class="avatar avatar-xxl brround" id="empAvtar" onclick="inputClick(event)"
                                style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');">
                            <input type="file" id="profileInput" style="display:none;"
                                value="{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}"
                                onchange="profileSet()" />
                            <span class="avatar-status bg-green"></span>
                        </span> -->

                        <!-- Second Modal (Fixed) -->
                        <span class="avatar avatar-xxl brround emp-avatar" id="employee-avatar" data-target="2" onclick="openModal('uploadModal2')"
                            style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? $employee->emp_profile_photo : asset('assets/imgs/user.png') }}');">
                            @if (isset($employee))
                                @if ($employee->emp_status == 71)
                                    <span class="avatar-status bg-green"></span>
                                @else
                                    <span class="avatar-status bg-red"></span>
                                @endif
                            @endif
                        </span>
                        <input type="file" id="profileInput2" style="display:none;" accept="image/*" onchange="handleFileSelect(this, 'uploadModal2')" />

                        <div id="uploadModal2" class="modal uploadModal2">
                            <div class="modal-content">
                                <div id="cropModal2" class="crop-modal"
                                    style="display: none; justify-content: center; align-items: center; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 1100;">
                                    <div style="background: white; padding: 20px; max-width: 400px; width: 100%; border-radius: 10px;">
                                        <img id="cropperImage2" style="max-width: 100%; border-radius: 10px;" />
                                        <div style="margin-top: 10px; text-align: right;">
                                            <button onclick="cropImage('uploadModal2')" class="btn btn-outline-primary">Crop & Use</button>
                                            <button onclick="closeCropModal('uploadModal2')" class="btn btn-secondary">Cancel</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-container">
                                    <div class="tab-buttons">
                                        <div class="tab-button active" onclick="switchTab('gallery', 'uploadModal2')">Drive</div>
                                        <div class="tab-button" onclick="switchTab('camera', 'uploadModal2')">Camera</div>
                                    </div>

                                    <div id="gallery-tab-2" class="tab-content cam-tab-content active" data-modal="uploadModal2">
                                        <div class="camera-container">
                                            <label for="profileInput2" class="upload-icon" id="upload-area-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <p>Click to browse image</p>
                                            </label>

                                            <div id="gallery-preview-container-2" style="display: none;">
                                                <img id="gallery-preview-2" src="" style="max-width: 100%; max-height: 300px;" />
                                            </div>
                                        </div>
                                    </div>

                                    <div id="camera-tab-2" class="tab-content cam-tab-content" data-modal="uploadModal2">
                                        <div id="camera-container-2" class="camera-container">
                                            <video id="video-2" width="100%" height="auto" autoplay playsinline></video>
                                            <div class="button-row">
                                                <button id="startCameraBtn-2" class="btn btn-success"
                                                    onclick="startCamera('uploadModal2')">Start Camera</button>
                                                <button id="captureBtnCamera-2" class="btn btn-success"
                                                    onclick="capturePhoto('uploadModal2')" style="display: none;">Capture
                                                    Image</button>
                                            </div>
                                        </div>

                                        <canvas id="canvas-2" style="display:none;"></canvas>

                                        <div id="camera-preview-container-2" class="preview-container" style="display: none;">
                                            <img id="camera-preview-2" src="" style="max-width: 100%; max-height: 300px;" />
                                            <div class="button-row">
                                                <button class="btn btn-danger" onclick="retakePhoto('uploadModal2')">Retake</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" onclick="closeModal('uploadModal2')">Cancel</button>
                                    <button type="button" class="btn btn-outline-primary" id="saveBtn-2" name="action" value="ajaxcapsave" onclick="saveImage('uploadModal2')" disabled>Save</button>
                                </div>
                            </div>
                        </div>

                        {{-- <span class="avatar avatar-xxl brround" id="empAvtar2"
                        style="background-image: url('{{ isset($employee) && $employee->emp_profile_photo ? asset('uploads/employee_profile/' . $employee->emp_profile_photo) : asset('assets/imgs/user.png') }}');">
                            <input type="file" id="profileInput" style="display:none;" />
                            <span class="avatar-status bg-green"></span>
                        </span> --}}
                        <div class="pro-user mt-3">
                            <h5 class="pro-user-username text-dark mb-1 fs-16" id="employeeNameFinish">
                                Employee Name</h5>
                            <h6 class="pro-user-desc text-muted fs-12" id="emailText">-</h6>
                        </div>
                    </div>

                 {{-- start --}}
                <div class="row">

                    @php
                        // approvalFlowtypesandemp is now grouped by module_id
                        $prefilledApprovals = collect($approvalFlowtypesandemp);
                    @endphp

                    @foreach($finalResult as $module)

                        @php
                            // multiple prefills for this module
                            $prefills = $prefilledApprovals[$module['m_id']] ?? collect();
                        @endphp

                        <div class="col-lg-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="accordion" id="accordion-{{ $module['m_id'] }}">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $module['m_id'] }}">
                                            <button class="accordion-button collapsed d-flex justify-content-between align-items-center"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapse-{{ $module['m_id'] }}"
                                                aria-expanded="false"
                                                aria-controls="collapse-{{ $module['m_id'] }}">

                                                <span>{{ $module['m_name'] }}</span>

                                                <span class="ms-3 d-flex gap-2">
                                                    @foreach($module['statuses'] as $status)
                                                        <span class="badge bg-primary">{{ $status['m_name'] }}</span>
                                                    @endforeach
                                                </span>
                                            </button>
                                        </h2>

                                        <div id="collapse-{{ $module['m_id'] }}"
                                            class="accordion-collapse collapse"
                                            aria-labelledby="heading-{{ $module['m_id'] }}"
                                            data-bs-parent="#accordion-{{ $module['m_id'] }}">

                                            <div class="accordion-body">

                                                <form class="approver-form"
                                                    data-module-id="{{ $module['m_id'] }}"
                                                    method="POST"
                                                    action="{{ route('save.approvers.ajex', $module['m_id']) }}">
                                                    @csrf

                                                    <input type="hidden" name="approvaer_module" value="{{ $module['m_id'] }}">
                                                    <input type="hidden" name="approvaer_emp_id" value="{{ $employee->emp_id ?? '' }}">

                                                    <div class="approver-rows">

                                                        {{-- PREFILLED APPROVAL ROWS --}}
                                                        @forelse($prefills as $index => $prefill)
                                                            <div class="d-flex flex-wrap align-items-end gap-2 bg-light p-2 rounded mb-2 approver-row">

                                                                {{-- Approving Manager --}}
                                                                <div class="flex-grow-1">
                                                                    <label class="form-label">
                                                                        <span class="text-danger">*</span> Approving Manager
                                                                    </label>
                                                                    <select class="form-select manager-select"
                                                                        name="managers[]">
                                                                        <option value="">-- Select Manager --</option>
                                                                        @foreach($approvalFlowwmpList as $emp)
                                                                            <option value="{{ $emp->emp_id }}"
                                                                                @if($prefill['eas_approvel_id'] == $emp->emp_id) selected @endif>
                                                                                {{ $emp->emp_full_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                {{-- Approval Stage --}}
                                                                <div class="flex-grow-1">
                                                                    <label class="form-label">
                                                                        <span class="text-danger">*</span> Approval Stage
                                                                    </label>
                                                                    <select class="form-select" name="statuses[]">
                                                                        <option value="">-- Select Stage --</option>
                                                                        @foreach($module['statuses'] as $status)
                                                                            <option value="{{ $status['m_id'] }}"
                                                                                @if($prefill['eas_approvel_status'] == $status['m_id']) selected @endif>
                                                                                {{ $status['m_name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                {{-- Buttons --}}
                                                                <div class="d-flex flex-column gap-1">
                                                                    @if($index > 0)
                                                                        <button type="button" class="btn btn-sm close-btn">❌</button>
                                                                    @endif
                                                                    <button type="button" class="btn btn-sm add-approver">➕</button>
                                                                </div>

                                                            </div>
                                                        @empty

                                                            {{-- EMPTY DEFAULT ROW --}}
                                                            <div class="d-flex flex-wrap align-items-end gap-2 bg-light p-2 rounded mb-2 approver-row">

                                                                <div class="flex-grow-1">
                                                                    <label class="form-label">
                                                                        <span class="text-danger">*</span> Approving Manager
                                                                    </label>
                                                                    <select class="form-select manager-select"
                                                                        name="managers[]">
                                                                        <option value="">-- Select Manager --</option>
                                                                        @foreach($approvalFlowwmpList as $emp)
                                                                            <option value="{{ $emp->emp_id }}">
                                                                                {{ $emp->emp_full_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="flex-grow-1">
                                                                    <label class="form-label">
                                                                        <span class="text-danger">*</span> Approval Stage
                                                                    </label>
                                                                    <select class="form-select" name="statuses[]">
                                                                        <option value="">-- Select Stage --</option>
                                                                        @foreach($module['statuses'] as $status)
                                                                            <option value="{{ $status['m_id'] }}">
                                                                                {{ $status['m_name'] }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="d-flex flex-column gap-1">
                                                                    <button type="button" class="btn btn-sm add-approver">➕</button>
                                                                </div>

                                                            </div>
                                                        @endforelse

                                                    </div>

                                                    <button type="submit" class="btn btn-success mt-2">
                                                        Save
                                                    </button>

                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endforeach

                </div>
                {{-- end --}}


                </div>
            </div>
            <div class="card">
                <div class="card-body" id="dataCard" hidden></div>
            </div>
        </div>
    </div>
    <div class="card-footer d-none" id="tab10btns">
        <div class="row">
            <div class="col text-left">
                <a href="javascript:void(0);" class="btn btn-outline-primary" onclick="previousData('10','1')">Previous</a>
            </div>
            <div class="col text-end">
                <button href="jbuttonvascript:void(0);" class="btn btn-outline-primary" onclick="saveData('10','1')"
                    id="save-button">Save & Finish</button>
            </div>
        </div>
    </div>
@endsection
<!-- Cropper CSS -->

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />

<!-- Cropper JS -->

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

{{-- stat  --}}

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkboxes = document.querySelectorAll('input[name="checkInMethod[]"]');
        const error = document.getElementById("checkInMethodError2");

        function validateCheckbox() {
            let checked = false;

            checkboxes.forEach(function (cb) {
                if (cb.checked) {
                    checked = true;
                }
            });

            if (!checked) {
                error.innerText = "Please select at least one check-in method";
            } else {
                error.innerText = "";
            }
        }

        // har checkbox par event lagao
        checkboxes.forEach(function (cb) {
            cb.addEventListener("change", validateCheckbox);
        });

    });
</script>

{{-- scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.addEventListener('click', function(e) {

            // Add Approver
            if (e.target.closest('.add-approver')) {
                const button = e.target.closest('.add-approver');
                const form = button.closest('.approver-form');
                const moduleId = form.dataset.moduleId;
                const rowsContainer = form.querySelector('.approver-rows');
                const rowCount = rowsContainer.querySelectorAll('.approver-row').length;

                // Clone first row
                const firstRow = rowsContainer.querySelector('.approver-row');
                const newRow = firstRow.cloneNode(true);

                // Reset values
                newRow.querySelector('select[name="managers[]"]').value = '';
                newRow.querySelector('select[name="statuses[]"]').value = '';

                // Remove add button from cloned row
                const addBtn = newRow.querySelector('.add-approver');
                if (addBtn) addBtn.remove();

                // Add close button to cloned row
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn btn-sm close-btn';
                closeBtn.textContent = '❌';
                newRow.querySelector('.d-flex.flex-column.gap-1').appendChild(closeBtn);

                rowsContainer.appendChild(newRow);
            }

            // Remove row
            if (e.target.closest('.close-btn')) {
                const button = e.target.closest('.close-btn');
                const row = button.closest('.approver-row');
                row.remove();
            }

        });
    });
</script>

<script>
    $(document).on("submit", ".approver-form", function (e) {
        e.preventDefault();

        let form = $(this);
        let action = form.attr("action");
        let formData = form.serialize();

        $.ajax({
            url: action,
            type: "POST",
            data: formData,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved Successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Something went wrong',
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error saving data',
                });
            }
        });

    });
</script>

{{-- for pin code , zip code change  --}}
<script>
function updateZipLabels() {
    const nationalityInput = document.getElementById("emp_nationality");
    if (!nationalityInput) return;

    const nationality = nationalityInput.value.trim().toLowerCase();
    const labelText = (nationality === "indian") ? "Pin Code" : "Zip Code";

    // Update label text
    const permanentLabel = document.querySelector('label[for="permanentPinCode"]');
    const tempLabel = document.querySelector('label[for="tempPinCode"]');
    if (permanentLabel) permanentLabel.innerHTML = `${labelText} <span class="text-danger">*</span>`;
    if (tempLabel) tempLabel.innerHTML = `${labelText} <span class="text-danger">*</span>`;

    // Update placeholders
    const permanentInput = document.getElementById("permanentPinCode");
    const tempInput = document.getElementById("tempPinCode");
    if (permanentInput) permanentInput.placeholder = labelText;
    if (tempInput) tempInput.placeholder = labelText;
}

// Page load par bhi call kar do
document.addEventListener("DOMContentLoaded", function() {
    updateZipLabels();

    // Typing ke time pe bhi call hoga
    const nationalityInput = document.getElementById("emp_nationality");
    if (nationalityInput) {
        nationalityInput.addEventListener("input", updateZipLabels);
    }
});
</script>


{{-- for Geofencing  active or Inactive  --}}
<script>
function updateGeofencing(select) {
    const geofencingSelect = document.getElementById('geofencingId');
    const value = parseInt(select.value);

    // Automatically set based on Assign Mode
    if (value === 62) {
        geofencingSelect.value = '1'; // Active
    } else {
        geofencingSelect.value = '0'; // Inactive
    }

    // Trigger change event (so validation or any dependent logic runs)
    geofencingSelect.dispatchEvent(new Event('change'));
}
</script>



<script>
$(document).ready(function() {
    // Initialize all manager selects
    $('.manager-select').each(function() {
        initManagerSelect2(this, 'Search manager...');
    });
});
</script>


{{-- end  --}}


    <script>
        window.modalState = {
            uploadModal: {
                streamData: null,
                capturedImage: null,
                cropper: null,
                uploadSource: null,
                finalCroppedFile: null,
            },
            uploadModal2: {
                streamData: null,
                capturedImage: null,
                cropper: null,
                uploadSource: null,
                finalCroppedFile: null,
            }
        };

        let currentAvatar = null;
        let currentEmpId = null;

        function openModal(modalId) {
            let empIdcheck = '{{ $employee?->emp_id }}';

            if (modalId === 'uploadModal' && !empIdcheck) {
                console.log('Prevented uploadModal from opening in update mode');
                return;
            }

            document.getElementById(modalId).style.display = 'flex';
            switchTab('gallery', modalId); // Default to gallery tab
        }

        // Close modal by ID and reset
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            resetModal(modalId);
        }

        function openCropModal(imageSrc, modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const cropModalId = 'cropModal' + modalSuffix;
            const cropperImageId = 'cropperImage' + modalSuffix;

            document.getElementById(cropModalId).style.display = 'flex';
            const imageElement = document.getElementById(cropperImageId);
            imageElement.src = imageSrc;

            if (modalState[modalId].cropper) {
                modalState[modalId].cropper.destroy();
            }

            modalState[modalId].cropper = new Cropper(imageElement, {
                aspectRatio: 3 / 4, // Portrait ratio (3:4)
                viewMode: 1,
                movable: true,
                zoomable: true,
                scalable: false,
                rotatable: false
            });
        }

        function closeCropModal(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const cropModalId = 'cropModal' + modalSuffix;

            if (modalState[modalId].cropper) {
                modalState[modalId].cropper.destroy();
                modalState[modalId].cropper = null;
            }
            document.getElementById(cropModalId).style.display = 'none';
        }

        function cropImage(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

            if (window.modalState[modalId].cropper) {
                const canvas = window.modalState[modalId].cropper.getCroppedCanvas({
                    width: 300,
                    height: 400,
                });

                canvas.toBlob(function (blob) {
                    const fileName = "profile_photo_" + Date.now() + ".jpg";
                    const file = new File([blob], fileName, { type: "image/jpeg" });

                    // ✅ Store file globally so external JS can access it
                    window.modalState[modalId].finalCroppedFile = file;

                    const croppedDataUrl = URL.createObjectURL(file);

                    // ✅ DOM IDs
                    const galleryPreviewId = 'gallery-preview-' + modalSuffix;
                    const cameraPreviewId = 'camera-preview-' + modalSuffix;
                    const galleryPreviewContainerId = 'gallery-preview-container-' + modalSuffix;
                    const cameraPreviewContainerId = 'camera-preview-container-' + modalSuffix;
                    const uploadAreaId = 'upload-area-' + modalSuffix;
                    const cameraContainerId = 'camera-container-' + modalSuffix;
                    const saveBtnId = 'saveBtn-' + modalSuffix;

                    // ✅ Show preview
                    const preview = window.modalState[modalId].uploadSource === 'gallery'
                        ? document.getElementById(galleryPreviewId)
                        : document.getElementById(cameraPreviewId);

                    preview.src = croppedDataUrl;

                    // ✅ Toggle UI
                    if (window.modalState[modalId].uploadSource === 'gallery') {
                        document.getElementById(galleryPreviewContainerId).style.display = 'block';
                        document.getElementById(uploadAreaId).style.display = 'none';
                    } else {
                        document.getElementById(cameraPreviewContainerId).style.display = 'flex';
                        document.getElementById(cameraContainerId).style.display = 'none';
                    }

                    // ✅ Enable Save button
                    document.getElementById(saveBtnId).disabled = false;

                    // ✅ Close crop modal (if you defined this function)
                    closeCropModal(modalId);
                }, "image/jpeg");
            }
        }



        // Reset the modal state
        function resetModal(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

            // Stop camera if it's running
            stopCamera(modalId);

            // Reset previews
            document.getElementById('gallery-preview-container-' + modalSuffix).style.display = 'none';
            document.getElementById('camera-preview-container-' + modalSuffix).style.display = 'none';
            document.getElementById('camera-container-' + modalSuffix).style.display = 'flex';

            // Reset upload area
            const uploadArea = document.getElementById('upload-area-' + modalSuffix);
            if (uploadArea) {
                uploadArea.style.display = 'flex';
            }

            // Disable save button
            document.getElementById('saveBtn-' + modalSuffix).disabled = true;

            // Reset captured image
            modalState[modalId].capturedImage = null;
            modalState[modalId].uploadSource = null;
        }

        // Switch between gallery and camera tabs
        function switchTab(tab, modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';

            // Reset state before switching
            resetModal(modalId);

            // Get container
            const tabContainer = document.getElementById(modalId).querySelector('.tab-container');

            // Update tab buttons
            const tabButtons = tabContainer.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
            });

            // Update tab contents
            const tabContents = tabContainer.querySelectorAll('.cam-tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Activate selected tab
            if (tab === 'gallery') {
                tabContainer.querySelector('.tab-button:nth-child(1)').classList.add('active');
                document.getElementById('gallery-tab-' + modalSuffix).classList.add('active');
            } else {
                tabContainer.querySelector('.tab-button:nth-child(2)').classList.add('active');
                document.getElementById('camera-tab-' + modalSuffix).classList.add('active');
            }
        }

        // Handle file selection from gallery
        function handleFileSelect(input, modalId) {
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    modalState[modalId].uploadSource = 'gallery';
                    openCropModal(e.target.result, modalId);
                    input.value = '';
                };
                reader.readAsDataURL(file);
            }
        }

        // Start the camera
        function startCamera(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const videoId = 'video-' + modalSuffix;
            const startCameraBtnId = 'startCameraBtn-' + modalSuffix;
            const captureBtnCameraId = 'captureBtnCamera-' + modalSuffix;

            // Access the user's camera
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(videoStream) {
                    modalState[modalId].streamData = videoStream;
                    const video = document.getElementById(videoId);
                    video.srcObject = modalState[modalId].streamData;
                    video.play();

                    // Show capture button, hide start button
                    document.getElementById(startCameraBtnId).style.display = 'none';
                    document.getElementById(captureBtnCameraId).style.display = 'inline-block';
                })
                .catch(function(error) {
                    console.error('Error accessing camera:', error);
                    alert('Could not access the camera. Please make sure you have granted camera permissions.');
                });
        }

        // Stop the camera
        function stopCamera(modalId) {
            if (modalState[modalId] && modalState[modalId].streamData) {
                modalState[modalId].streamData.getTracks().forEach(track => {
                    track.stop();
                });
                modalState[modalId].streamData = null;

                const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
                const videoId = 'video-' + modalSuffix;
                const startCameraBtnId = 'startCameraBtn-' + modalSuffix;
                const captureBtnCameraId = 'captureBtnCamera-' + modalSuffix;

                // Reset video element
                const video = document.getElementById(videoId);
                if (video) video.srcObject = null;

                // Reset buttons
                const startCameraBtn = document.getElementById(startCameraBtnId);
                const captureBtnCamera = document.getElementById(captureBtnCameraId);

                if (startCameraBtn) startCameraBtn.style.display = 'inline-block';
                if (captureBtnCamera) captureBtnCamera.style.display = 'none';
            }
        }

        // Capture photo from camera
        function capturePhoto(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const videoId = 'video-' + modalSuffix;
            const canvasId = 'canvas-' + modalSuffix;

            const video = document.getElementById(videoId);
            const canvas = document.getElementById(canvasId);
            const context = canvas.getContext('2d');

            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw the current video frame on the canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to data URL
            const imageDataUrl = canvas.toDataURL('image/png');

            // Stop the camera
            stopCamera(modalId);

            modalState[modalId].uploadSource = 'camera';
            openCropModal(imageDataUrl, modalId);
        }

        // Retake photo
        function retakePhoto(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const cameraPreviewContainerId = 'camera-preview-container-' + modalSuffix;
            const cameraContainerId = 'camera-container-' + modalSuffix;
            const saveBtnId = 'saveBtn-' + modalSuffix;

            // Hide preview, show camera
            document.getElementById(cameraPreviewContainerId).style.display = 'none';
            document.getElementById(cameraContainerId).style.display = 'flex';

            // Start camera again
            startCamera(modalId);

            // Disable save button
            document.getElementById(saveBtnId).disabled = true;
            modalState[modalId].capturedImage = null;
        }

        function saveImage(modalId) {
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            let myformData = new FormData();

            const profileInputId = modalId === 'uploadModal' ? 'profileInput' : 'profileInput2';
            const imgInput = document.getElementById(profileInputId);
            const cameraPreviewId = 'camera-preview-' + modalSuffix;
            const imagePreview = document.getElementById(cameraPreviewId);
            const saveBtnId = 'saveBtn-' + modalSuffix;
            const saveButton = document.getElementById(saveBtnId);

            // const currentEmpId = document.getElementById('employee_id')?.value || '{{ $employee?->emp_id }}';
            const currentEmpId = '{{ $employee?->emp_id }}';

            const croppedFile = window.modalState[modalId]?.finalCroppedFile;

            if (croppedFile) {
                myformData.append('emp_profile_photo', croppedFile);
                logFormData(myformData);

                if (currentEmpId) {
                    console.log('update1');
                    sendImageData(myformData, modalId);
                } else {
                    console.log('create1');
                    closeModal(modalId);
                }

            } else if (imgInput?.files?.length > 0) {
                myformData.append('emp_profile_photo', imgInput.files[0]);
                logFormData(myformData);

                if (currentEmpId) {
                    console.log('update2');
                    sendImageData(myformData, modalId);
                } else {
                    console.log('create2');
                    closeModal(modalId);
                }

            } else if (imagePreview?.src && imagePreview.src.startsWith('data:image')) {
                fetch(imagePreview.src)
                    .then(res => res.blob())
                    .then(blob => {
                        const file = new File([blob], 'captured_image.jpg', { type: blob.type });
                        myformData.append('emp_profile_photo', file);
                        logFormData(myformData);

                        if (currentEmpId) {
                            console.log('update3');
                            sendImageData(myformData, modalId);
                        } else {
                            console.log('create3');
                            closeModal(modalId);
                        }
                    })
                    .catch(error => {
                        console.error('Error converting base64 to file:', error);
                        alert('Error preparing image for upload.');
                    });

            } else {
                alert('No image selected or captured.');
            }
        }


        function sendImageData(formData, modalId) {
            // Append emp_id to the FormData
            const modalSuffix = modalId === 'uploadModal' ? '1' : '2';
            const saveBtnId = 'saveBtn-' + modalSuffix;
            const saveButton = document.getElementById(saveBtnId);

            currentEmpId = '{{ $employee?->emp_id }}';
            formData.append('emp_id', currentEmpId);
            formData.append(saveButton.name, saveButton.value);

            fetch('{{ route('addEmp.saveData') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(errData => {
                            console.error('Validation error:', errData);
                            throw new Error('Upload failed');
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update the avatar that triggered this modal
                        const avatarTarget = document.querySelector(`.emp-avatar[data-target="${modalId === 'uploadModal' ? '1' : '2'}"]`);
                        if (avatarTarget) {
                            avatarTarget.style.backgroundImage = `url('${data.image_url}')`;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Image saved successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: 'Failed to upload image.'
                        });
                    }
                });
            closeModal(modalId);
        }

        function logFormData(formData) {
            for (let [key, value] of formData.entries()) {
                console.log(`${key}:`, value);
            }
        }
    </script>

    {{-- For Name Prefix to change gender value  --}}
<script>
    $(document).ready(function () {
        // Optional: initialize Select2 if using
        $('#gender').select2();
        $('#prefix').select2();

        // Trigger on page load
        const prefixSelect = document.getElementById("prefix");
        if (prefixSelect && prefixSelect.value) {
            namePrefix(prefixSelect);
        }
    });

    function namePrefix(prefixSelect) {
        const genderSelect = $("#gender");
        const prefixId = $(prefixSelect).val();

        const maleGenderId = "33";
        const femaleGenderId = "34";
        const neutralGenderId = "35";

        const prefixToGender = {
            "95": maleGenderId,
            "96": femaleGenderId,
            "98": femaleGenderId,
            "413": femaleGenderId,
        };
        const genderValue = prefixToGender[prefixId] || neutralGenderId;
        genderSelect.val(genderValue).trigger("change");
    }
</script>

    {{-- For Count Retrement date form 60 old of Date of Birth  --}}
     <script>
        window.onload = function() {
            calculateRetirementDate();
        };

        document.getElementById('dateOfBirth').addEventListener('change', calculateRetirementDate);
        document.getElementById('lastWorkingDate').addEventListener('change', calculateRetirementDate);
        document.getElementById('esic_limit').addEventListener('change', calculateRetirementDate);
        document.getElementById('pf_enable').addEventListener('change', calculateRetirementDate);

        function calculateRetirementDate() {
            let dateOfBirth = document.getElementById('dateOfBirth').value;
            let lastDate = document.getElementById('lastWorkingDate').value;
            let esic_limit = document.getElementById('esic_limit').value;
            let pf_enable = document.getElementById('pf_enable').value;

            console.log(esic_limit, pf_enable);


            if (!dateOfBirth) return;

            let dob = new Date(dateOfBirth);
            dob.setFullYear(dob.getFullYear() + 60);

            let retirementDate = dob.toISOString().split('T')[0];

            if (!lastDate) {
                document.getElementById('retirementDate').value = retirementDate;
            } else {
                document.getElementById('retirementDate').value = lastDate;
                // document.getElementById('pfDateOfLeaving').value = lastDate;
                // document.getElementById('esiDateOfLeaving').value = lastDate;

                    if (esic_limit == 120) {
                        document.getElementById('esiDateOfLeaving').value = lastDate;
                    }

                    if (pf_enable == 120) {
                        document.getElementById('pfDateOfLeaving').value = lastDate;
                    }

            }

        }
    </script>


        {{-- Year of Service --}}
        <script>
            window.onload = function () {
                calculateservicetDate();
            };

            document.getElementById('dateOfJoin').addEventListener('change', calculateservicetDate);
            document.getElementById('lastWorkingDate').addEventListener('change', calculateservicetDate);
            document.getElementById('dateOfConfirmation').addEventListener('change', calculateservicetDate);
            document.getElementById('probationPeriod').addEventListener('change', calculateservicetDate);

            function calculateservicetDate() {
                let dateOfJoin = document.getElementById('dateOfJoin').value;
                let lastDate = document.getElementById('lastWorkingDate').value;

                if (!dateOfJoin || !lastDate) return;

                let startDate = new Date(dateOfJoin);
                let endDate = new Date(lastDate);

                if (endDate < startDate) {
                    document.getElementById('yearOfService').value = 'Invalid Dates';
                    return;
                }

                let years = endDate.getFullYear() - startDate.getFullYear();
                let months = endDate.getMonth() - startDate.getMonth();
                let days = endDate.getDate() - startDate.getDate();

                if (days < 0) {
                    months -= 1;
                    let prevMonth = new Date(endDate.getFullYear(), endDate.getMonth(), 0);
                    days += prevMonth.getDate();
                }

                if (months < 0) {
                    years -= 1;
                    months += 12;
                }

                let formatted = `${years} y ${months} m ${days} d`;

                document.getElementById('yearOfService').value = formatted;

            }
        
        </script>


        {{-- Probation Period In Days count  --}}

    <script>
        window.onload = function () {
            const dateOfJoiningInput = document.getElementById('dateOfJoin');
            const probationInput = document.getElementById('probationPeriod');
            const confirmationDateInput = document.getElementById('dateOfConfirmation');

            function calculateServiceDate() {
            const joiningDateValue = dateOfJoiningInput.value;
            const probationDaysValue = parseInt(probationInput.value, 10);

            if (!joiningDateValue) {
                confirmationDateInput.value = '';
                return;
            }

            const joiningDate = new Date(joiningDateValue);
            if (isNaN(joiningDate.getTime())) {
                confirmationDateInput.value = '';
                return;
            }

            const confirmationDate = new Date(joiningDate);
            if (!isNaN(probationDaysValue) && probationDaysValue > 0) {
                confirmationDate.setDate(confirmationDate.getDate() + probationDaysValue);
            }

            const yyyy = confirmationDate.getFullYear();
            const mm = String(confirmationDate.getMonth() + 1).padStart(2, '0');
            const dd = String(confirmationDate.getDate()).padStart(2, '0');
            confirmationDateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            // Run on page load
            calculateServiceDate();

            // Recalculate when inputs change
            dateOfJoiningInput.addEventListener('change', calculateServiceDate);
            probationInput.addEventListener('input', calculateServiceDate);
        };
    </script>

    <script>
    function calculateFields(triggerSource = '') {
        const separationSubmit = document.getElementById('separationSubmitDate').value;
        const leavingDateInput = document.getElementById('leavingDateAsPerNoticePeriod');
        const noticeDaysInput = document.getElementById('noticePeriodRequiredDate');

        if (!separationSubmit) return;

        const sepDate = new Date(separationSubmit);

        // CASE 1: Leaving Date changes → calculate Notice Period Required Days
        if (triggerSource === 'leavingDate' && leavingDateInput.value) {
            const leaveDate = new Date(leavingDateInput.value);
            const diffTime = leaveDate - sepDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            noticeDaysInput.value = diffDays >= 0 ? diffDays : 0;
        }

        // CASE 2: Notice Period Required Days changes → calculate Leaving Date
        if (triggerSource === 'noticeDays' && noticeDaysInput.value) {
            const noticeDays = parseInt(noticeDaysInput.value);
            if (!isNaN(noticeDays)) {
                const newLeaveDate = new Date(sepDate);
                newLeaveDate.setDate(sepDate.getDate() + noticeDays);
                leavingDateInput.value = newLeaveDate.toISOString().split('T')[0];
            }
        }

        // Update Shortfall Days every time
        calculateNoticePeriodShortfall();
    }

    function calculateNoticePeriodServedDays() {
        const separationSubmit = document.getElementById('separationSubmitDate').value;
        const lastWorking = document.getElementById('lastWorkingDate').value;
        const servedDaysInput = document.getElementById('noticePeriodServedDate');

        if (separationSubmit && lastWorking) {
            const sepDate = new Date(separationSubmit);
            const lastDate = new Date(lastWorking);
            const diffTime = lastDate - sepDate;
            // const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            servedDaysInput.value = diffDays >= 0 ? diffDays : 0;
        } else {
            servedDaysInput.value = '';
        }

        // Update shortfall every time served days change
        calculateNoticePeriodShortfall();
    }

    function calculateNoticePeriodShortfall() {
        const requiredDays = parseInt(document.getElementById('noticePeriodRequiredDate').value) || 0;
        const servedDays = parseInt(document.getElementById('noticePeriodServedDate').value) || 0;
        const shortfallInput = document.getElementById('noticePeriodShortfallDays');

        let shortfall = requiredDays - servedDays;
        shortfallInput.value = shortfall > 0 ? shortfall : 0;
    }

    // Attach all listeners for live updates
    document.getElementById('separationSubmitDate').addEventListener('change', () => {
        calculateFields();
        calculateNoticePeriodServedDays();
    });
    document.getElementById('leavingDateAsPerNoticePeriod').addEventListener('change', () => calculateFields('leavingDate'));
    document.getElementById('noticePeriodRequiredDate').addEventListener('input', () => calculateFields('noticeDays'));
    document.getElementById('lastWorkingDate').addEventListener('change', calculateNoticePeriodServedDays);
    document.getElementById('noticePeriodServedDate').addEventListener('input', calculateNoticePeriodShortfall);
</script>



    <script>
        const fileInputs = [
            { inputId: 'upload_aadhar', btnId: 'aadharUploadBtn' },
            { inputId: 'upload_drivng_license', btnId: 'driving_license_UploadBtn' },
            { inputId: 'upload_voter_id', btnId: 'upload_voter' },
            { inputId: 'upload_passport', btnId: 'uplode_passport_file' },
            { inputId: 'upload_passbook', btnId: 'uplode_passbook_file' },
            { inputId: 'upload_pan', btnId: 'uploade_pan_file' }
        ];

        fileInputs.forEach(item => {
            const inputElement = document.getElementById(item.inputId);
            const btnElement = document.getElementById(item.btnId);

            if (inputElement && btnElement) {
                inputElement.addEventListener('change', function () {
                    if (inputElement.files.length > 0) {
                        btnElement.classList.remove('btn-outline-info');
                        btnElement.classList.add('btn-success');
                    }
                });
            }
        });
    </script>



    <script>
        window.routes = {
            employeeAddEditPayroll: "{{ route('employee.addEdit.payroll', ['id' => 'REPLACE_ID']) }}"
        };
    </script>


    <script>
        var formCompleted = false;
        // window.onbeforeunload = function(e) {
        //     if (!formCompleted) {
        //         return 'Are you sure you want to leave the page?';
        //     }
        // };
        var CSRF = '{{ csrf_token() }}';
        var empCheckUrl = "{{ route('addEmp.checkEmpID') }}";
        var formSubmitURL = "{{ route('addEmp.saveData') }}";
        var stateCityURL = "{{ route('addEmp.getStateCity') }}";
        var phoneEmailCheckURL = "{{ route('addEmp.checkMailPhone') }}";
        var ifscfURL = "{{ route('addEmp.checkIFSC') }}";
        var salaryifscfURL = "{{ route('addEmp.salaryifsc') }}";
        var profileURL = "{{ route('addEmp.uploadProfilePic') }}";
        var getEmpURL = "{{ route('addEmp.getEmployeeData') }}";
        var redirectURL = "{{ url('/admin/employee/') }}";
        var profilePath = '';
        var EmpNotAlreadyExist = true;
        var EmailNotAlreadyExist = true;
        var PhoneNotAlreadyExist = true;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Dropify for all file inputs
            var drEvent = $('.dropify').dropify();

            drEvent.on('dropify.afterClear', function(event, element) {
                // Handle clear event
            });

            drEvent.on('dropify.errors', function(event, element) {
                // Handle errors
                console.log('Error', event);
            });

            // Example of setting default file for 'upload_aadhar'
            var defaultAadhaarFile =
                '{{ isset($employeeDocuments->aadharUpload) ? asset('uploads/EmployeeDocs/' . $employeeDocuments->aadharUpload) : '' }}';
            if (defaultAadhaarFile) {
                var dropifyAadhaar = $('#upload_aadhar').dropify();
                dropifyAadhaar.data('dropify').settings.defaultFile = defaultAadhaarFile;
                dropifyAadhaar.data('dropify').destroy().init(); // Refresh Dropify
            }


            var today = new Date();

            // Calculate the date 18 years ago from today
            var eighteenYearsAgo = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

            // Format the date to YYYY-MM-DD for the input value
            var formattedDate = eighteenYearsAgo.toISOString().split('T')[0];

            // Get the date input element
            var dobInput = document.getElementById('dateOfBirth');

            // Set the default value to 18 years ago if the input is empty
            if (!dobInput.value) {
                // dobInput.value = formattedDate;
            }

            // Set the max attribute to 18 years ago to prevent selecting a date less than 18 years old
            dobInput.setAttribute('max', formattedDate);

        });


        function validatePositiveNumber(input) {
            // Use a regular expression to allow only positive numbers
            const regex = /^[1-9]\d*$/;

            // If the input does not match the regex, clear the value
            if (!regex.test(input.value)) {
                input.value = input.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
            }
        }

        function getQualification(e) {
            handleChange(event);
            let streamId = e.value;
            let qualificationId =
                '{{ isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_qualification_id : '' }}';
            $.ajax({
                url: "{{ route('get.qualification') }}",
                type: "POST",
                data: {
                    _token: '{{ csrf_token() }}',
                    stream: streamId,
                },
                dataType: 'json',
                cache: true,
                success: function(result) {
                    if (result.data) {
                        $('#qualification').html('');
                        var defaultOption = $('<option>').val('').text('Select Qualification');
                        $('#qualification').append(defaultOption);
                        result.data.forEach(function(element) {
                            var option = $('<option>').val(element.qua_id).text(element.qua_name);
                            if (qualificationId && qualificationId == element.qua_id) {
                                option.attr('selected', true);
                            }
                            $('#qualification').append(option);
                        });
                    } else {
                        $('#qualification').html('');
                    }
                },
                error: function(error) {
                    console.error('Error fetching qualification data:', error.data);
                }
            });
        }

        function numericOnly(event) {
            return (event.charCode >= 48 && event.charCode <= 57);
        }

        function initializeMap(mapId, inputId, latitudeId, longitudeId, pinCodeId, latitudeValue, longitudeValue,
            latitudeErrorId = null, longitudeErrorId = null) {
            const defaultLatitude = latitudeValue || 28.6139; // Default to New Delhi
            const defaultLongitude = longitudeValue || 77.2090;
            const defaultLocation = {
                lat: defaultLatitude,
                lng: defaultLongitude,
            };
            const mapElement = document.getElementById(mapId);
            const currentZoom = mapElement.dataset.zoom || 12;

            const map = new google.maps.Map(mapElement, {
                center: defaultLocation,
                zoom: parseInt(currentZoom)
            });

            const input = document.getElementById(inputId);
            const searchBox = new google.maps.places.SearchBox(input);

            map.addListener("bounds_changed", function() {
                searchBox.setBounds(map.getBounds());
            });

            let marker = new google.maps.Marker({
                position: defaultLocation,
                map: map
            });

            searchBox.addListener("places_changed", function() {
                const places = searchBox.getPlaces();
                if (places.length === 0) {
                    return;
                }

                const bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    marker.setMap(null); // Remove the previous marker
                    marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location
                    });

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                map.fitBounds(bounds);
                const selectedPlace = places[0];
                if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
                    const latitude = selectedPlace.geometry.location.lat();
                    const longitude = selectedPlace.geometry.location.lng();

                    document.getElementById(latitudeId).value = latitude;
                    document.getElementById(longitudeId).value = longitude;
                    document.getElementById(latitudeErrorId).innerHTML = '';
                    document.getElementById(longitudeErrorId).innerHTML = '';
                }
            });

            google.maps.event.addListener(map, 'zoom_changed', function() {
                mapElement.dataset.zoom = map.getZoom();
            });
        }


        function sameAddressFun(checkbox) {
            if (checkbox.checked) {
                // Safely get elements and set their values if they exist
                const permSearchInput = document.getElementById('permanentSearchInput');
                const tempSearchInput = document.getElementById('temporarySearchInput');
                const permLongitude = document.getElementById('permanentLongitude');
                const tempLongitude = document.getElementById('temporaryLongitude');
                const permLatitude = document.getElementById('permanentLatitude');
                const tempLatitude = document.getElementById('temporaryLatitude');
                const permPinCode = document.getElementById('permanentPinCode');
                const tempPinCode = document.getElementById('tempPinCode');

                if (tempSearchInput && permSearchInput) tempSearchInput.value = permSearchInput.value;
                if (tempLongitude && permLongitude) tempLongitude.value = permLongitude.value;
                if (tempLatitude && permLatitude) tempLatitude.value = permLatitude.value;
                if (tempPinCode && permPinCode) tempPinCode.value = permPinCode.value;

                // Clear error messages if elements exist
                if (document.getElementById('temporarySearchInputError'))
                    document.getElementById('temporarySearchInputError').innerHTML = '';
                if (document.getElementById('temporaryLatitudeError'))
                    document.getElementById('temporaryLatitudeError').innerHTML = '';
                if (document.getElementById('temporaryLongitudeError'))
                    document.getElementById('temporaryLongitudeError').innerHTML = '';
                if (document.getElementById('tempPinCodeError'))
                    document.getElementById('tempPinCodeError').innerHTML = '';
            } else {
                // Clear values if unchecked
                if (document.getElementById('temporarySearchInput'))
                    document.getElementById('temporarySearchInput').value = '';
                if (document.getElementById('temporaryLongitude'))
                    document.getElementById('temporaryLongitude').value = '';
                if (document.getElementById('temporaryLatitude'))
                    document.getElementById('temporaryLatitude').value = '';
                if (document.getElementById('tempPinCode'))
                    document.getElementById('tempPinCode').value = '';
            }

            // Re-initialize map with updated coordinates
            const latitude = parseFloat(document.getElementById("temporaryLatitude")?.value || 0);
            const longitude = parseFloat(document.getElementById("temporaryLongitude")?.value || 0);
            initializeMap(
                "tempmap", "temporarySearchInput", "temporaryLatitude", "temporaryLongitude", "tempPinCode",
                latitude, longitude, "temporaryLatitudeError", "temporaryLongitudeError"
            );
        }


        $(document).ready(function() {
            // Call the function if there is a pre-selected stream
            let preSelectedStream =
                '{{ old('stream', isset($employee->fh_employee_qualifications) ? $employee->fh_employee_qualifications->eq_stream_id : '') }}';
            if (preSelectedStream) {
                getQualification({
                    value: preSelectedStream
                });
            }

            // Get the current selected value from the dropdown
            var selectedAttendanceMethod = $('#attendanceMethod').val();
            checkInMethodCheckbox({
                value: selectedAttendanceMethod
            });
        });

        const today = new Date();
        const maxAllowedDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

        document.getElementById('dateOfBirth').addEventListener('input', function() {
            const userInputDate = new Date(this.value);
            if (userInputDate > maxAllowedDate) {
                this.setCustomValidity('You must be 18 years or older');
                this.reportValidity();
                document.getElementById('dateOfBirthError').innerHTML = 'You must be at 18 years or older';
                $('#nextBtn').addClass('disabled').off('click');

            } else {
                this.setCustomValidity('');
                this.reportValidity();
                document.getElementById('dateOfBirthError').innerHTML = '';
                $('#nextBtn').removeClass('disabled').on('click', function() {
                    saveData('1', '1');
                });
            }
        });

        $(window).on('load', function() {
            $('.select2').select2();
        });
    </script>
    <script>
        let permanentMap, tempMap;
        let permanentMarkers = [];
        let tempMarkers = [];

        // Use Blade syntax to pass saved coordinates
        const savedPermanentLocation = {
            lat: {{ $employee->emp_permanent_latitude ?? 28.6139 }},
            lng: {{ $employee->emp_permanent_longitude ?? 77.209 }}
        };

        const savedTemporaryLocation = {
            lat: {{ $employee->emp_temporary_latitude ?? 28.6139 }},
            lng: {{ $employee->emp_temporary_longitude ?? 77.209 }}
        };

        // function initPermanentMap() {
        //     // Initialize the permanent map with saved or default location
        //     permanentMap = new google.maps.Map(document.getElementById("map"), {
        //         center: savedPermanentLocation,
        //         zoom: 12
        //     });

        //     // Place a marker if saved coordinates are available
        //     if (savedPermanentLocation.lat && savedPermanentLocation.lng) {
        //         const marker = new google.maps.Marker({
        //             position: savedPermanentLocation,
        //             map: permanentMap,
        //             title: "Permanent Address"
        //         });
        //         permanentMarkers.push(marker);
        //     }

        //     // Search box for Permanent Address
        //     const permanentInput = document.getElementById("permanentSearchInput");
        //     const permanentSearchBox = new google.maps.places.SearchBox(permanentInput);

        //     permanentMap.addListener("bounds_changed", () => {
        //         permanentSearchBox.setBounds(permanentMap.getBounds());
        //     });

        //     permanentSearchBox.addListener("places_changed", () => {
        //         const places = permanentSearchBox.getPlaces();
        //         if (places.length === 0) return;

        //         permanentMarkers.forEach(marker => marker.setMap(null));
        //         permanentMarkers = [];

        //         const place = places[0];
        //         if (!place.geometry || !place.geometry.location) return;

        //         const marker = new google.maps.Marker({
        //             map: permanentMap,
        //             position: place.geometry.location,
        //             title: place.name
        //         });
        //         permanentMarkers.push(marker);

        //         if (place.geometry.viewport) {
        //             permanentMap.fitBounds(place.geometry.viewport);
        //         } else {
        //             permanentMap.setCenter(place.geometry.location);
        //             permanentMap.setZoom(14);
        //         }

        //         document.getElementById('permanentLongitude').value = place.geometry.location.lng();
        //         document.getElementById('permanentLatitude').value = place.geometry.location.lat();
        //         document.getElementById('permanentLongitudeError').innerHTML = '';
        //         document.getElementById('permanentLatitudeError').innerHTML = '';
        //     });
        // }

        function initPermanentMap() {

            const savedPermanentLocation = {
                lat: 23.2599, // default (change if needed)
                lng: 77.4126
            };

            // ✅ Initialize Map
            permanentMap = new google.maps.Map(document.getElementById("map"), {
                center: savedPermanentLocation,
                zoom: 12
            });

            // ✅ Default Marker (if saved)
            if (savedPermanentLocation.lat && savedPermanentLocation.lng) {
                const marker = new google.maps.Marker({
                    position: savedPermanentLocation,
                    map: permanentMap,
                    title: "Permanent Address"
                });
                permanentMarkers.push(marker);
            }

            // ✅ Input Field
            const input = document.getElementById("permanentSearchInput");

            // ✅ Autocomplete (IMPORTANT)
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ["geocode"],
                componentRestrictions: { country: "in" }
            });

            // ✅ Required fields
            autocomplete.setFields(["address_components", "geometry", "name"]);

            // ✅ On place select
            autocomplete.addListener("place_changed", function () {

                const place = autocomplete.getPlace();
                if (!place.geometry) return;

                // ❌ Remove old markers
                permanentMarkers.forEach(marker => marker.setMap(null));
                permanentMarkers = [];

                // ✅ Add new marker
                const marker = new google.maps.Marker({
                    map: permanentMap,
                    position: place.geometry.location,
                    title: place.name
                });

                permanentMarkers.push(marker);

                // ✅ Center map
                permanentMap.setCenter(place.geometry.location);
                permanentMap.setZoom(14);

                // ✅ Latitude & Longitude
                document.getElementById('permanentLongitude').value =
                    place.geometry.location.lng();

                document.getElementById('permanentLatitude').value =
                    place.geometry.location.lat();

                document.getElementById('permanentLongitudeError').innerHTML = '';
                document.getElementById('permanentLatitudeError').innerHTML = '';

                // ✅ PINCODE LOGIC (with fallback)
                let pincode = "";

                // Try from autocomplete
                if (place.address_components) {
                    place.address_components.forEach(component => {
                        if (component.types.includes("postal_code")) {
                            pincode = component.long_name;
                        }
                    });
                }

                // If not found → use Geocoder
                if (!pincode) {
                    const geocoder = new google.maps.Geocoder();

                    geocoder.geocode(
                        { location: place.geometry.location },
                        function (results, status) {
                            if (status === "OK" && results[0]) {
                                results[0].address_components.forEach(component => {
                                    if (component.types.includes("postal_code")) {
                                        document.getElementById('permanentPinCode').value =
                                            component.long_name;
                                    }
                                });
                            }
                        }
                    );
                } else {
                    document.getElementById('permanentPinCode').value = pincode;
                }
            });
        }

        function initTemporaryMap() {
            const savedTemporaryLocation = {
                lat: {{ $employee->emp_temporary_latitude ?? 28.6139 }},
                lng: {{ $employee->emp_temporary_longitude ?? 77.209 }}
            };

            tempMap = new google.maps.Map(document.getElementById("tempmap"), {
                center: savedTemporaryLocation,
                zoom: 12
            });

            if (savedTemporaryLocation.lat && savedTemporaryLocation.lng) {
                const marker = new google.maps.Marker({
                    position: savedTemporaryLocation,
                    map: tempMap,
                    title: "Temporary Address"
                });
                tempMarkers.push(marker);
            }

            const input = document.getElementById("temporarySearchInput");
            const autocomplete = new google.maps.places.Autocomplete(input, {
                types: ["geocode"],
                componentRestrictions: { country: "in" }
            });

            autocomplete.setFields(["address_components", "geometry", "formatted_address", "name"]);

            function fillLatLngPincode(place) {
                if (!place.geometry) return;

                // Remove old markers
                tempMarkers.forEach(marker => marker.setMap(null));
                tempMarkers = [];

                // Add new marker
                const marker = new google.maps.Marker({
                    map: tempMap,
                    position: place.geometry.location,
                    title: place.name || ''
                });
                tempMarkers.push(marker);

                tempMap.setCenter(place.geometry.location);
                tempMap.setZoom(14);

                document.getElementById('temporaryLongitude').value = place.geometry.location.lng();
                document.getElementById('temporaryLatitude').value = place.geometry.location.lat();

                // Pincode logic
                let pincode = "";
                if (place.address_components) {
                    place.address_components.forEach(c => {
                        if (c.types.includes("postal_code")) pincode = c.long_name;
                    });
                }

                const setPinCode = code => document.getElementById('tempPinCode').value = code || '';

                if (pincode) {
                    setPinCode(pincode);
                } else {
                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ location: place.geometry.location }, function(results, status) {
                        if (status === "OK" && results[0]) {
                            for (let res of results) {
                                for (let comp of res.address_components) {
                                    if (comp.types.includes("postal_code")) {
                                        pincode = comp.long_name;
                                        break;
                                    }
                                }
                                if (pincode) break;
                            }
                            // Last resort: 6-digit regex
                            if (!pincode && results[0].formatted_address) {
                                const match = results[0].formatted_address.match(/\b\d{6}\b/);
                                if (match) pincode = match[0];
                            }
                            setPinCode(pincode);
                        }
                    });
                }
            }

            // Event 1: dropdown select / enter press
            autocomplete.addListener("place_changed", function () {
                const place = autocomplete.getPlace();
                fillLatLngPincode(place);
            });

            // Event 2: blur / focus out (mouse typing)
            input.addEventListener("blur", function () {
                const address = input.value;
                if (!address) return;

                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: address }, function (results, status) {
                    if (status === "OK" && results[0]) {
                        fillLatLngPincode(results[0]);
                    }
                });
            });
        }

        // function initTemporaryMap() {
        //     // Initialize the temporary map with saved or default location
        //     tempMap = new google.maps.Map(document.getElementById("tempmap"), {
        //         center: savedTemporaryLocation,
        //         zoom: 12
        //     });

        //     // Place a marker if saved coordinates are available
        //     if (savedTemporaryLocation.lat && savedTemporaryLocation.lng) {
        //         const marker = new google.maps.Marker({
        //             position: savedTemporaryLocation,
        //             map: tempMap,
        //             title: "Temporary Address"
        //         });
        //         tempMarkers.push(marker);
        //     }

        //     // Search box for Temporary Address
        //     const tempInput = document.getElementById("temporarySearchInput");
        //     const tempSearchBox = new google.maps.places.SearchBox(tempInput);

        //     tempMap.addListener("bounds_changed", () => {
        //         tempSearchBox.setBounds(tempMap.getBounds());
        //     });

        //     tempSearchBox.addListener("places_changed", () => {
        //         const places = tempSearchBox.getPlaces();
        //         if (places.length === 0) return;

        //         tempMarkers.forEach(marker => marker.setMap(null));
        //         tempMarkers = [];

        //         const place = places[0];
        //         if (!place.geometry || !place.geometry.location) return;

        //         const marker = new google.maps.Marker({
        //             map: tempMap,
        //             position: place.geometry.location,
        //             title: place.name
        //         });
        //         tempMarkers.push(marker);

        //         if (place.geometry.viewport) {
        //             tempMap.fitBounds(place.geometry.viewport);
        //         } else {
        //             tempMap.setCenter(place.geometry.location);
        //             tempMap.setZoom(14);
        //         }

        //         document.getElementById('temporaryLongitude').value = place.geometry.location.lng();
        //         document.getElementById('temporaryLatitude').value = place.geometry.location.lat();
        //         document.getElementById('temporaryLongitudeError').innerHTML = '';
        //         document.getElementById('temporaryLatitudeError').innerHTML = '';
        //     });
        // }

        // Initialize both maps when the page loads
        function initMaps() {
            initPermanentMap();
            initTemporaryMap();
        }
    </script>

    <script>
        function checkInMethodCheckbox(e) {
            if (e.value == 62) {
                $('#checkInMethodID1').prop('disabled', false);
                $('#checkInMethodID2').prop('disabled', false);
                $('#checkInMethodID3').prop('disabled', false);
                $('#checkInMethodID4').prop('disabled', false);
            } else if (e.value == 63) {
                $('#checkInMethodID1').prop('disabled', false);
                $('#checkInMethodID2').prop('disabled', true);
                $('#checkInMethodID3').prop('disabled', false);
                $('#checkInMethodID4').prop('disabled', true);

                $('#checkInMethodID2').prop('checked', false);
                $('#checkInMethodID4').prop('checked', false);
            } else if (e.value == 64) {
                $('#checkInMethodID1').prop('disabled', false);
                $('#checkInMethodID2').prop('disabled', true);
                $('#checkInMethodID3').prop('disabled', false);
                $('#checkInMethodID4').prop('disabled', true);

                $('#checkInMethodID2').prop('checked', false);
                $('#checkInMethodID4').prop('checked', false);
            }
        }
    </script>

    <!-- Load Google Maps API and initialize maps -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('credentials')['MAP_API_KEY'] }}&libraries=places&callback=initMaps"
        async defer></script>

    <script src="{{ asset('assets/js/employee/add-employee-form.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.querySelectorAll('.extract-img').forEach(input => {
            input.addEventListener('change', async function() {
                const file = this.files[0];
                if (!file) return;

                const inputId = this.id;
                const fieldKey = inputId.replace('upload_', '');
                const targetId = `${fieldKey}_number`;
                const result = document.getElementById(targetId);
                if (!result) return;

                // Find the corresponding invalid message span next to the label
                const invalidMsgId = `invalid_${fieldKey}`;
                const invalidMsg = document.getElementById(invalidMsgId);

                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', targetId);

                try {
                    const fastApiBaseUrl = "{{ app('App\\Helpers\\ApiHelper')::FASTAPI_BASE_URL }}";

                    const response = await fetch(fastApiBaseUrl + '/extracted-id', {
                    // const response = await fetch("https://6ca86e3ead8e.ngrok-free.app/extracted-id", {
                        method: 'POST',
                        body: formData,
                    });

                    const data = await response.json();
                    console.log(data);



               


                    if (response.ok) {
    result.value = data.value || '';
    if (invalidMsg) invalidMsg.innerHTML = '';

    let licenseField = document.getElementById('emp_drivng_license_valid');
    if (licenseField) {
        if (data.validity_info && data.validity_info.length > 0) {
            let rawDate = data.validity_info[0]; // take first item from array

            // If backend already sends YYYY-MM-DD, no need to reformat
            if (/^\d{4}-\d{2}-\d{2}$/.test(rawDate)) {
                licenseField.value = rawDate;
            } else {
                // Try to parse and reformat
                let date = new Date(rawDate);
                if (!isNaN(date)) {
                    licenseField.value = date.toISOString().split('T')[0];
                } else {
                    console.error("Invalid date format from API:", rawDate);
                    licenseField.value = '';
                }
            }
        } else {
            licenseField.value = '';
        }
    }
}
 else {
                        result.value = '';
                        if (invalidMsg) invalidMsg.innerHTML = data.message || "Extraction failed.";
                    }
                } catch (error) {
                    console.error(error);
                    result.value = '';
                    if (invalidMsg) invalidMsg.innerHTML = "Error uploading image";
                }
            });
        });
    </script>

    <script>
        function toggleFilters() {
            const container = document.getElementById('tempmap');
            container.style.display = container.style.display === 'none' ? 'flex' : 'none';
        }

        function toggleFilterstwo() {
            const container = document.getElementById('map');
            container.style.display = container.style.display === 'none' ? 'flex' : 'none';
        }
    </script>
    <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const startDateInput = document.getElementById('emp_group_insurance_start_date');
                    const tillDateInput = document.getElementById('emp_group_insurance_till_date');

                    const startError = document.getElementById('groupInsuranceStartDateError');
                    const tillError = document.getElementById('groupInsuranceTillDateError');

                    function validateDates() {
                        const startDateValue = startDateInput.value;
                        const tillDateValue = tillDateInput.value;

                        // Clear previous error messages
                        startError.textContent = '';
                        tillError.textContent = '';

                        // Proceed only if both dates are selected
                        if (startDateValue && tillDateValue) {
                            const startDate = new Date(startDateValue);
                            const tillDate = new Date(tillDateValue);

                            if (startDate > tillDate) {
                                startError.textContent = 'Start date must be before till date.';
                                tillError.textContent = 'Till date must be after start date.';
                                return false;
                            }
                        }

                        return true;
                    }

                    startDateInput.addEventListener('change', validateDates);
                    tillDateInput.addEventListener('change', validateDates);
                });
    </script>

    <script>
    const radios = document.querySelectorAll('input[name="payment"]');
    const bankDiv = document.getElementById('bankDetailsDiv');

    function toggleBankDetails() {
        if (document.getElementById('bank').checked) {
        bankDiv.style.display = 'block';
        } else {
        bankDiv.style.display = 'none';
        }
    }

    // Call on page load
    toggleBankDetails();

    // Call on change
    radios.forEach(radio => {
        radio.addEventListener('change', toggleBankDetails);
    });
    </script> 




<script>
  function setupDurationCalculator(joinId, leaveId, durationId) {
    const join = document.getElementById(joinId);
    const leave = document.getElementById(leaveId);
    const duration = document.getElementById(durationId);

    // Enable/disable leave date
    join.addEventListener("change", () => {
      leave.disabled = !join.value;
      leave.min = join.value;
      if (leave.value && leave.value < join.value) leave.value = "";
      calculateDuration();
    });

    leave.addEventListener("change", calculateDuration);

    function calculateDuration() {
      let joinDate = join.value;
      let leaveDate = leave.value;

      if (joinDate && leaveDate) {
        let start = new Date(joinDate);
        let end = new Date(leaveDate);

        let years = end.getFullYear() - start.getFullYear();
        let months = end.getMonth() - start.getMonth();
        let days = end.getDate() - start.getDate();

        if (days < 0) {
          months--;
          days += new Date(end.getFullYear(), end.getMonth(), 0).getDate();
        }

        if (months < 0) {
          years--;
          months += 12;
        }

        duration.value = `${years} Y ${months} M ${days} D`;
      } else {
        duration.value = "";
      }
    }
  }

  // Initialize for both companies
  setupDurationCalculator("joinDatecompany", "leaveDatecompany", "serviceDuration");
  setupDurationCalculator("joinDatecompany1", "leaveDatecompany1", "serviceDuration1");
</script>

@endsection
