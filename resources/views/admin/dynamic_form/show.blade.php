@extends('admin.layout.master')
@section('title', $formTitle ?? 'Dynamic Form')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 mb-3">
                <div class="bg-light rounded shadow-sm p-3 h-100">
                    <h5 class="text-primary mb-3">📚 Form Sections</h5>
                    <ul class="nav flex-column">
                        @foreach ($formSections as $index => $section)
                            <li class="nav-item">
                                <a class="nav-link {{ $index == 0 ? 'active fw-bold' : '' }}" href="#"
                                    onclick="showSection({{ $index }}, event)">
                                    {{ $section->section_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <form action="{{ route('forms.submit') }}" method="POST">
                    @csrf
                    @foreach ($formSections as $index => $section)
                        <div id="section{{ $index }}" class="content-section"
                            style="{{ $index == 0 ? 'display: block;' : 'display: none;' }}">
                            <div class="card p-4 mb-4 shadow-sm">
                                <h4 class="text-secondary mb-3">{{ $section->section_name }}</h4>
                                <div class="row">
                                    @foreach ($section->fields as $field)
                                        <div class="col-lg-4 col-md-6 mb-3">
                                            <label class="form-label fw-bold">
                                                {{ $field->field_name }}
                                                @if ($field->is_required == 1)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>


                                            @switch($field->field_type)
                                                @case('text')
                                                @case('password')

                                                @case('email')
                                                @case('number')

                                                @case('date')
                                                    <input type="{{ $field->field_type }}" class="form-control required-field"
                                                        name="{{ $field->colume_name }}" placeholder="{{ $field->placeholder }}"
                                                        {{ $field->required }} data-required="{{ $field->is_required }}">
                                                @break

                                                @case('textarea')
                                                    <textarea class="form-control required-field" name="{{ $field->colume_name }}" placeholder="{{ $field->placeholder }}"
                                                        {{ $field->required }} data-required="{{ $field->is_required }}"></textarea>
                                                @break

                                                @case('select')
                                                    <select class="form-control required-field" name="{{ $field->colume_name }}"
                                                        {{ $field->required }} data-required="{{ $field->is_required }}">
                                                        <option value="">-- Select --</option>


                                                        @if ($field->field_name == 'Marital Status')
                                                            @foreach ($maritalStatus as $martial)
                                                                <option value="{{ $martial->m_id }}">{{ $martial->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Blood Group')
                                                            @foreach ($bloodGroupList as $bloodgroup)
                                                                <option value="{{ $bloodgroup->m_id }}">{{ $bloodgroup->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Prefix')
                                                            @foreach ($prefix as $prefixitem)
                                                                <option value="{{ $prefixitem->m_id }}">{{ $prefixitem->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Gender')
                                                            @foreach ($staticGender as $gender)
                                                                <option value="{{ $gender->m_id }}">{{ $gender->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Stream')
                                                            @foreach ($stream as $stm)
                                                                <option value="{{ $stm->stm_id }}">{{ $stm->stm_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Qualification Course Type')
                                                            @foreach ($qualificationCourseType as $qct)
                                                                <option value="{{ $qct->m_id }}">{{ $qct->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Nature of Course')
                                                            @foreach ($natureCourse as $qct)
                                                                <option value="{{ $qct->m_id }}">{{ $qct->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Course Qualification Status')
                                                            @foreach ($qualificationStatus as $qct)
                                                                <option value="{{ $qct->m_id }}">{{ $qct->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Branch')
                                                            @foreach ($BranchList as $branchlist)
                                                                <option value="{{ $branchlist->br_id }}">
                                                                    {{ $branchlist->br_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Department')
                                                            @foreach ($DepartmentList as $department)
                                                                <option value="{{ $department->d_id }}">{{ $department->d_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Designation')
                                                            @foreach ($DesignationList as $designation)
                                                                <option value="{{ $designation->dg_id }}">
                                                                    {{ $designation->dg_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Grade')
                                                            @foreach ($Grade as $grade)
                                                                <option value="{{ $grade->g_id }}">{{ $grade->g_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Role')
                                                            @foreach ($Role as $roleitem)
                                                                <option value="{{ $roleitem->role_id }}">
                                                                    {{ $roleitem->role_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Employee Reporting Manager')
                                                            @foreach ($supervisor as $roleitem)
                                                                <option value="{{ $roleitem->emp_id }}">
                                                                    {{ $roleitem->emp_full_name }} {{ $roleitem->emp_code }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Assign Policy')
                                                            @foreach ($leavePolicy as $leavpolicy)
                                                                <option value="{{ $leavpolicy->pl_id }}">
                                                                    {{ $leavpolicy->pl_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Assign Shift')
                                                            @foreach ($ShiftType as $shift)
                                                                <option value="{{ $shift->pst_id }}">
                                                                    {{ $shift->pst_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Assign Mode')
                                                            @foreach ($attendanceMethod as $method)
                                                                <option value="{{ $method->m_id }}">
                                                                    {{ $method->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Geofencing')
                                                            @foreach ($status as $key => $stat)
                                                                <option value="{{ $key }}">
                                                                    {{ $stat }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Weekoff')
                                                            @foreach ($weekOffs as $key => $wof)
                                                                <option value="{{ $key }}">
                                                                    {{ $wof }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Attendance Policy')
                                                            @foreach ($attendancePolicy as $attpolicy)
                                                                <option value="{{ $attpolicy->ap_id }}">
                                                                    {{ $attpolicy->ap_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Fixing Dots Leave credit on pro-rata')
                                                            <option value="1"
                                                                {{ isset($employee) && $employee->emp_allow_joining_leave == 1 ? 'selected' : '' }}>
                                                                Allowed</option>
                                                            <option value="0"
                                                                {{ isset($employee) && $employee->emp_allow_joining_leave == 0 ? 'selected' : '' }}>
                                                                Not Allowed</option>
                                                        @elseif ($field->field_name == 'Joining Leave Calculation Method')
                                                            @foreach ($leaveCalcBy as $method)
                                                                <option value="{{ $method->m_id }}">
                                                                    {{ $method->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Probation leave on pro-rata')
                                                            <option value="1"
                                                                {{ isset($employee) && $employee->emp_allow_probation_leave == 1 ? 'selected' : '' }}>
                                                                Allowed</option>
                                                            <option value="0"
                                                                {{ isset($employee) && $employee->emp_allow_probation_leave == 0 ? 'selected' : '' }}>
                                                                Not Allowed</option>
                                                        @elseif ($field->field_name == 'Status')
                                                            <option value="71"
                                                                {{ isset($employee) ? ($employee->emp_status == 71 ? 'selected' : '') : 'selected' }}>
                                                                Active</option>
                                                            <option value="72"
                                                                {{ isset($employee) && $employee->emp_status == 72 ? 'selected' : '' }}>
                                                                In-Active</option>
                                                        @elseif ($field->field_name == 'Employee Type')
                                                            @foreach ($employeeType as $empType)
                                                                <option value="{{ $empType->m_id }}">
                                                                    {{ $empType->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'Job Status')
                                                            @foreach ($employeeJobStatus as $item)
                                                                <option value="{{ $item->m_id }}">
                                                                    {{ $item->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'PF Enable')
                                                            @foreach ($getEsicLimit as $item)
                                                                <option value="{{ $item->m_id }}"
                                                                    @if (isset($employee) && $employee->emp_is_pf_enabled == $item->m_id) selected
                                                            @elseif (!isset($employee) && $item->m_id == 121)
                                                                selected @endif>
                                                                    {{ $item->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @elseif ($field->field_name == 'ESIC Enable')
                                                            @foreach ($getEsicLimit as $item)
                                                                <option value="{{ $item->m_id }}"
                                                                    @if (isset($employee) && $employee->emp_esic_limit == $item->m_id) selected
                                                        @elseif (!isset($employee) && $item->m_id == 121)
                                                            selected @endif>
                                                                    {{ $item->m_name }}
                                                                </option>
                                                            @endforeach
                                                        @endif


                                                    </select>
                                                @break

                                                @case('radio')
                                                    <div>
                                                        @if (!empty($field->options))
                                                            @foreach ($field->options as $option)
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input required-field" type="radio"
                                                                        name="{{ $field->colume_name }}"
                                                                        value="{{ $option }}" {{ $field->required }}
                                                                        data-required="{{ $field->is_required }}">
                                                                    <label class="form-check-label">{{ $option }}</label>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                @break

                                                @case('checkbox')
                                                    <div class="form-check">
                                                        <input class="form-check-input required-field" type="checkbox"
                                                            name="{{ $field->colume_name }}" value="1" {{ $field->required }}
                                                            data-required="{{ $field->is_required }}">
                                                        <label
                                                            class="form-check-label">{{ $field->placeholder ?? 'Check this box' }}</label>
                                                    </div>
                                                @break

                                                @foreach ($checkInMethod as $index => $method)
                                                    <label class="custom-control custom-checkbox d-inline-block me-3">
                                                        <input type="checkbox" class="custom-control-input"
                                                            master="{{ $method->m_id }}" name="checkInMethod[]"
                                                            id="checkInMethodID{{ $index + 1 }}"
                                                            {{ isset($employee) && in_array($method->m_id, $employee->emp_checkin_method_id ?? []) ? 'checked' : '' }}
                                                            onchange="handleChange(event)">
                                                        <span class="custom-control-label"></span>{{ $method->m_name }}
                                                    </label>
                                                @endforeach
                                                @case('file')
                                                    <input type="file" class="form-control required-field"
                                                        name="{{ $field->colume_name }}" {{ $field->required }}
                                                        data-required="{{ $field->is_required }}">
                                                @break

                                                @default
                                                    <input type="text" class="form-control required-field"
                                                        name="{{ $field->colume_name }}" placeholder="{{ $field->placeholder }}"
                                                        {{ $field->required }} data-required="{{ $field->is_required }}">
                                            @endswitch
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach



                    <!-- Navigation Buttons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="prevBtn"
                            onclick="changeSection(-1)" disabled>
                            ⬅ Previous
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="nextBtn" onclick="changeSection(1)">
                            Next ➡
                        </button>
                        <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                            ✅ Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Optional style section --}}
    <style>
        .is-invalid {
            border: 2px solid red !important;
            background-color: #fff5f5;
        }
    </style>

    {{-- Script for section navigation and validation --}}
    <script>
        let currentSection = 0;
        const totalSections = {{ count($formSections) }};

        function showSection(index, event = null) {
            document.querySelectorAll('.content-section').forEach((section, i) => {
                section.style.display = i === index ? 'block' : 'none';
            });

            document.querySelectorAll('.nav-link').forEach((link, i) => {
                link.classList.toggle('active', i === index);
                link.classList.toggle('fw-bold', i === index);
            });

            currentSection = index;
            updateButtons();
            if (event) event.preventDefault();
        }

        function changeSection(step) {
            if (step === 1 && !validateRequiredFields()) {
                return;
            }

            const newIndex = currentSection + step;
            if (newIndex >= 0 && newIndex < totalSections) {
                showSection(newIndex);
            }
        }

        function validateRequiredFields() {
            const fields = document.querySelectorAll(`#section${currentSection} .required-field`);
            let allValid = true;

            fields.forEach(field => {
                const isRequired = field.dataset.required === "1";
                if (isRequired && !field.value.trim()) {
                    field.classList.add('is-invalid');
                    allValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            return allValid;
        }

        function updateButtons() {
            document.getElementById("prevBtn").disabled = currentSection === 0;
            document.getElementById("nextBtn").style.display = currentSection === totalSections - 1 ? "none" :
                "inline-block";
            document.getElementById("submitBtn").style.display = currentSection === totalSections - 1 ? "inline-block" :
                "none";
        }

        // Clear validation error on input
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('required-field')) {
                if (e.target.dataset.required === "1" && e.target.value.trim() !== "") {
                    e.target.classList.remove('is-invalid');
                }
            }
        });

        // Initial setup
        showSection(0);
    </script>
@endsection
