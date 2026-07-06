@extends('admin.layout.master')
@section('title')
    {{ $pageTitle ?? 'Candidates' }}
@endsection
@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs ?? []" />

    <div class="card mt-4 mb-5">
        <!-- Navigation Section -->
        <div class="d-flex justify-content-between align-items-center p-3">
            <div>
                <a href="/recruitment/candidate-view/30/" title="Previous Candidate" class="text-danger me-2 fw-bold">
                    <ion-icon name="arrow-back-circle-outline" style="font-size: 35px;"></ion-icon>
                </a>
                <a href="/recruitment/candidate-view/2/" title="Next Candidate" class="text-danger fw-bold">
                    <ion-icon name="arrow-forward-circle-outline" style="font-size: 35px;"></ion-icon>
                </a>
            </div>
            <div>
                <a href="/recruitment/candidate-update/28/" class="btn btn-light" title="Edit">
                    <i class="feather feather-edit" style="font-size: 20px;"></i>
                </a>
                <form action="/recruitment/candidate-conversion/28/" method="post" class="d-inline"
                    onsubmit="return confirm('Are you sure you want to convert this candidate into an employee?')">
                    @csrf
                    <button type="submit" class="btn btn-success">Convert To Employee</button>
                </form>
            </div>
        </div>

        <!-- Profile Section -->
        <div class="d-flex align-items-center p-3">
            <!-- Profile Picture -->
            <div class="rounded-circle bg-warning text-white text-center"
                style="width: 80px; height: 80px; font-size: 2rem; line-height: 80px;">
                NA
            </div>

            <!-- Profile Details -->
            <div class="ms-4 col-4">
                <h4 class="mb-0">{{ $recruitmentCandidate->rc_name }}</h4>
                <small>{{ $recruitmentCandidate?->fh_designation?->dg_name }}</small>
            </div>

            <!-- Contact Info -->
            <div>
                <p class="mb-0"><strong>Email:</strong> {{ $recruitmentCandidate->rc_email }}</p>
                <p class="mb-0"><strong>Phone:</strong> {{ $recruitmentCandidate->rc_mobile }}</p>
            </div>
        </div>

        <div class="panel panel-primary">
            <!-- Tabs Heading -->
            <div class="tab-menu-heading p-0 bg-light">
                <div class="tabs-menu1">
                    <ul class="nav panel-tabs">
                        <li><a href="#tab5" class="active" data-bs-toggle="tab">About</a></li>
                        <li><a href="#tab6" data-bs-toggle="tab">Resume</a></li>
                        <li><a href="#tab13" data-bs-toggle="tab">Scheduled Interviews</a></li>
                    </ul>
                </div>
            </div>

            <!-- Tabs Content -->
            <div class="panel-body tabs-menu-body">
                <div class="tab-content">
                    <!-- About Tab -->
                    <div class="tab-pane active" id="tab5">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title">Personal Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li><strong>Date of Birth:</strong>
                                                {{ $recruitmentCandidate->rc_dob?->format('d-m-Y') }}</li>
                                            <li><strong>Gender:</strong> {{ $recruitmentCandidate?->fh_gender?->m_name }}
                                            </li>
                                            <li><strong>Address:</strong> {{ $recruitmentCandidate->rc_address }}</li>
                                            <li><strong>Country:</strong> {{ $recruitmentCandidate?->fh_country?->c_name }}
                                            </li>
                                            <li><strong>State:</strong> {{ $recruitmentCandidate?->fh_state?->s_name }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="card mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title">Recruitment Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <ul class="list-unstyled">
                                                    <li><strong>Recruitment:</strong>
                                                        {{ $recruitmentCandidate?->fh_recruitment?->r_title }}</li>
                                                    {{-- <li><strong>Department:</strong> S/W Dept</li> --}}
                                                </ul>
                                            </div>
                                            <div class="col-lg-6">
                                                <ul class="list-unstyled">
                                                    <li><strong>Current Stage:</strong>
                                                        {{ $recruitmentCandidate?->fh_recruitment_stage?->rsg_stage }}</li>
                                                    <li><strong>Job Position:</strong>
                                                        {{ $recruitmentCandidate?->fh_designation?->dg_name }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resume Tab -->
                    <div class="tab-pane" id="tab6">
                        <iframe id="iframe_pdf"
                        src="{{ !empty($recruitmentCandidate->rc_resume) ? $recruitmentCandidate->rc_resume : '' }}"
                            style="width: 100%; height: 500px;" frameborder="0"></iframe>
                    </div>
                    <!-- Scheduled Interviews Tab -->
                    <div class="tab-pane" id="tab13">
                        @foreach ($recruitmentCandidate->fh_recruitment_interviewschedules as $interviewKey => $interviewItem)
                            <div class="d-flex justify-content-between mb-3">
                                <h4>{{ $interviewItem?->fh_recruitment_created_by?->emp_full_name }} Scheduled Interviews
                                </h4>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#createScheduleInterviewModal" hidden>+ Add</button>
                            </div>

                            <!-- Interview Cards -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <strong>Date:</strong> {{ $interviewItem->ris_interview_date->format('d-M-Y') }}<br>
                                    <strong>Time:</strong> {{ $interviewItem->ris_interview_time->format('H:i:s') }}<br>
                                    <strong>Interviewer: </strong> {{ $interviewItem->ris_interview_name }}
                                    <br>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal to Schedule Interview -->
    <x-modal id="createScheduleInterviewModal" title="Schedule Interview" formId="createScheduleInterview"
        action="{{ route('schedule-interview.store') }}" method="POST" enctype="multipart/form-data" size="modal-md"
        submitButtonText="Save" submitButtonId="saveButton">
        @csrf
        <x-select id="ris_candidate_id" name="ris_candidate_id" class="sumo_search" label="Candidate" :options="$candidate ?? []"
            astric="true" />
        <x-select id="ris_interviewer" name="ris_interviewer" class="sumo_search" label="Interviewer" :options="$interviewers ?? []"
            astric="true" />
        <x-input id="ris_interview_date" name="ris_interview_date" type="date" label="Interview Date"
            placeholder="Interview Date" />
        <x-input id="ris_interview_time" name="ris_interview_time" type="time" label="Interview Time"
            placeholder="Interview Time" />
        <x-textarea id="ris_description" label="Description" name="ris_description" placeholder="Description" />
        <div class="form-group">
            <label class="custom-switch">
                <span class="custom-switch-description mx-0 me-2 form-label">Is Interview Complete</span>
                <input type="checkbox" name="ris_completed" id="ris_completed" class="custom-switch-input">
                <span class="custom-switch-indicator"></span>
            </label>
        </div>
    </x-modal>
@endsection

@section('script')
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
@endsection
