@extends('admin.layout.master')
@section('title', 'Pipeline')


@section('content')
    {{-- Bradcrumbs Start --}}
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    {{-- Bradcrumbs End --}}

    <div class="card mt-5">
        <div class="card-header border-bottom-0">
            <h3 class="card-title">Recruitment Pipeline</h3>
        </div>
        <div class="card-body">
            <div class="panel panel-primary">
                <div class="tab-menu-heading p-0 bg-light">
                    <div class="tabs-menu1">
                        <!-- Tabs -->
                        <ul class="nav panel-tabs">
                            @foreach ($recruitment as $recruitmentKey => $recruitmentItem)
                                <li class="">
                                    <a href="#tab{{ $recruitmentKey }}" class="{{ $loop->first ? 'active' : '' }}"
                                        data-bs-toggle="tab">
                                        {{ $recruitmentItem->r_title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="panel-body tabs-menu-body">
                    <div class="tab-content">
                        @foreach ($recruitment as $recruitmentKey => $recruitmentItem)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="tab{{ $recruitmentKey }}">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="panel-group1" id="accordion1">
                                                    @foreach ($recruitmentItem->fh_recruitment_stages ?? [] as $stageKey => $stageItem)
                                                        <div class="panel panel-default mb-4 overflow-hidden br-7">
                                                            <div class="panel-heading1">
                                                                <h4 class="panel-title1">
                                                                    {{-- <a class="accordion-toggle collapsed bg-gradient-primary"
                                                                        data-bs-toggle="collapse" data-parent="#accordion1"
                                                                        href="#collapseFour{{ $recruitmentKey }}{{ $stageKey }}"
                                                                        aria-expanded="false">{{ $stageItem->rsg_stage }}</a> --}}

                                                                    @php
                                                                        $stageColorStyles = [
                                                                            'Initial' => '#0ea5e9', // sky-500
                                                                            'Applied' => '#facc15', // yellow-400
                                                                            'Test' => '#a855f7', // purple-500
                                                                            'Interview' => '#4ade80', // green-400
                                                                            'Cancelled' => '#ef4444', // red-500
                                                                            'Hired' => '#047857', // emerald-700
                                                                        ];

                                                                        $color =
                                                                            $stageColorStyles[$stageItem->rsg_stage] ??
                                                                            '#d1d5db';
                                                                    @endphp
                                                                        <a class="accordion-toggle collapsed"
                                                                        style="border: 2px solid {{ $color }}; color: {{ $color }}; background-color: transparent;"
                                                                        data-bs-toggle="collapse" data-parent="#accordion1"
                                                                        href="#collapseFour{{ $recruitmentKey }}{{ $stageKey }}"
                                                                        aria-expanded="false"
                                                                        onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.color='blue';"
                                                                        onmouseout="this.style.backgroundColor='transparent'; this.style.color='{{ $color }}';">
                                                                        {{ $stageItem->rsg_stage }}
                                                                        </a>




                                                                </h4>
                                                            </div>
                                                            <div id="collapseFour{{ $recruitmentKey }}{{ $stageKey }}"
                                                                class="panel-collapse collapse" role="tabpanel"
                                                                aria-expanded="false">
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="card">
                                                                                <div class="card-body">
                                                                                    <div class="table-responsive">
                                                                                        <table
                                                                                            class="table table-vcenter text-nowrap table-bordered border-bottom"
                                                                                            id="hr-payroll{{ $recruitmentItem->r_id }}{{ $stageItem->rsg_id }}">
                                                                                            <thead>
                                                                                                <tr>
                                                                                                    <th
                                                                                                        class="border-bottom-0 w-5">
                                                                                                        <input
                                                                                                            type="checkbox"
                                                                                                            class="checkAll"
                                                                                                            name="checkAll"
                                                                                                            onchange="if ($(this).is(':checked')) {
                                                                                                                $(this).closest('table').find('.candidate-checkbox').prop('checked', true).change();
                                                                                                            } else {
                                                                                                                $(this).closest('table').find('.candidate-checkbox').prop('checked', false).change();
                                                                                                            }">
                                                                                                    </th>
                                                                                                    <th
                                                                                                        class="border-bottom-0">
                                                                                                        Candidate</th>
                                                                                                    <th
                                                                                                        class="border-bottom-0">
                                                                                                        Email</th>
                                                                                                    <th
                                                                                                        class="border-bottom-0">
                                                                                                        Contact</th>

                                                                                                    <th
                                                                                                        class="border-bottom-0">
                                                                                                        Stage</th>
                                                                                                    <th
                                                                                                        class="border-bottom-0">
                                                                                                        <select
                                                                                                            name="bulk_stage"
                                                                                                            class="form-control form-select"
                                                                                                            onchange="
                                                                                                            if (this.value) {
                                                                                                                let preStageId = 26;  // Assuming preStageId is defined
                                                                                                                let stageId = this.value;
                                                                                                                let candidateCheckBoxes = $(this).closest('.table').find('.candidate-checkbox[type=checkbox]:checked');

                                                                                                                if (!candidateCheckBoxes.length) {
                                                                                                                    Swal.fire({
                                                                                                                        title: 'No rows selected!',
                                                                                                                        icon: 'warning'
                                                                                                                    });
                                                                                                                } else {
                                                                                                                    let canIds = [];
                                                                                                                    $.each(candidateCheckBoxes, function(index, checkbox) {
                                                                                                                        canIds.push($(checkbox).val());
                                                                                                                    });
                                                                                                                    updateStage(canIds, stageId, '{{ $stageItem->rsg_stage }}', this.options[this.selectedIndex].text);
                                                                                                                }
                                                                                                            }
                                                                                                        ">
                                                                                                            <option
                                                                                                                value=""
                                                                                                                selected>
                                                                                                                Stage Bulk
                                                                                                                update
                                                                                                            </option>
                                                                                                            @foreach ($recruitmentItem->fh_recruitment_stages ?? [] as $stageKeyChild => $stageItemChild)
                                                                                                                <option
                                                                                                                    value="{{ $stageItemChild->rsg_id }}">
                                                                                                                    {{ $stageItemChild->rsg_stage }}
                                                                                                                </option>
                                                                                                            @endforeach
                                                                                                        </select>
                                                                                                    </th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                @foreach ($stageItem->fh_candidate ?? [] as $candidateKey => $candidateItem)
                                                                                                    <tr
                                                                                                        id="candidate-row-{{ $candidateItem->rc_id }}">
                                                                                                        <!-- Unique ID added -->
                                                                                                        <td><input
                                                                                                                type="checkbox"
                                                                                                                class="candidate-checkbox"
                                                                                                                value="{{ md5($candidateItem->rc_id) }}"
                                                                                                                onchange="if (!$(this).is(':checked')) {
                                                                                                            $(this).closest('.table').find('.checkAll').prop('checked', false);
                                                                                                        }">
                                                                                                        </td>
                                                                                                        <td> <a
                                                                                                                href="{{ route('candidates.show', ['candidate' => md5($candidateItem->rc_id)]) }}">
                                                                                                                <div
                                                                                                                    class="d-flex">
                                                                                                                    {{-- <span
                                                                                                                    class="avatar avatar-md brround me-3"
                                                                                                                    style="background-image: url({{ asset($candidateItem->rc_profile) }})">
                                                                                                                </span> --}}

                                                                                                                    <span
                                                                                                                        class="avatar avatar-md brround me-3"
                                                                                                                        style="background-image: url('{{ !empty($candidateItem->rc_profile) ? $candidateItem->rc_profile : '' }}')">
                                                                                                                    </span>

                                                                                                                    <div
                                                                                                                        class="me-3 mt-0 mt-sm-1 d-block">
                                                                                                                        <h6
                                                                                                                            class="mb-1 fs-14">
                                                                                                                            {{ $candidateItem->rc_name }}
                                                                                                                        </h6>
                                                                                                                        <p
                                                                                                                            class="text-muted mb-0 fs-12">
                                                                                                                            {{ $candidateItem->fh_designation?->dg_name }}
                                                                                                                        </p>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </a>
                                                                                                        </td>
                                                                                                        <td>{{ $candidateItem->rc_email }}
                                                                                                        </td>
                                                                                                        <td>{{ $candidateItem->rc_mobile }}
                                                                                                        </td>

                                                                                                        <td>
                                                                                                            <select
                                                                                                                class="form-control"
                                                                                                                onchange="updateStage({{ $candidateItem->rc_id }}, this.value, '{{ $stageItem->rsg_stage }}', this.options[this.selectedIndex].text)"
                                                                                                                name="stage">
                                                                                                                @foreach ($recruitmentItem->fh_recruitment_stages as $stageKeyChild => $stageItemChild)
                                                                                                                    <option
                                                                                                                        value="{{ $stageItemChild->rsg_id }}"
                                                                                                                        {{ $stageItemChild->rsg_id == $stageItem->rsg_id ? 'selected' : '' }}>
                                                                                                                        {{ $stageItemChild->rsg_stage }}
                                                                                                                    </option>
                                                                                                                @endforeach
                                                                                                            </select>
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="text-start d-flex justify-content-center">
                                                                                                            @php
                                                                                                                $schedule = $sheduled->firstWhere(
                                                                                                                    'ris_candidate_id',
                                                                                                                    $candidateItem->rc_id,
                                                                                                                );
                                                                                                            @endphp
                                                                                                            <a href="javascript:void(0);"
                                                                                                                class="action-btns {{ !$schedule ? 'createScheduleInterview' : '' }}"
                                                                                                                data-id="{{ !$schedule ? Crypt::encrypt($candidateItem->rc_id) : '' }}"
                                                                                                                {{ !$schedule ? 'data-bs-toggle=modal data-bs-target=#createScheduleInterviewModal' : '' }}
                                                                                                                data-bs-toggle="popover"
                                                                                                                data-bs-placement="top"
                                                                                                                data-bs-trigger="hover focus"
                                                                                                                data-bs-content="{{ $schedule ? 'Interview Scheduled on: ' . date('d M Y', strtotime($schedule->ris_interview_date)) . ' at ' . date('h:i A', strtotime($schedule->ris_interview_time)) : 'Click to schedule interview' }}">

                                                                                                                <i
                                                                                                                    class="feather feather-clock text-primary"></i>
                                                                                                            </a>


                                                                                                            {{-- @php
                                                                                                                $hasMailLog = false;
                                                                                                            @endphp

                                                                                                            @foreach ($emal_logs as $data)
                                                                                                                @if ($candidateItem->rc_id == $data->rel_candidate_id)
                                                                                                                    @php
                                                                                                                        $mail_template =
                                                                                                                            $mailTemplates[
                                                                                                                                $data
                                                                                                                                    ->rel_mail_template_id
                                                                                                                            ] ??
                                                                                                                            null;
                                                                                                                        $hasMailLog = true;
                                                                                                                    @endphp

                                                                                                                    @if ($mail_template)
                                                                                                                        <a href="javascript:void(0);"
                                                                                                                            class="action-btns sendMail"
                                                                                                                            data-id="{{ Crypt::encrypt($candidateItem->rc_id) }}"
                                                                                                                            onclick="sendMailData(this)"
                                                                                                                            data-bs-toggle="popover"
                                                                                                                            data-bs-placement="top"
                                                                                                                            data-bs-trigger="hover focus"
                                                                                                                            data-bs-content="{{ $mail_template->mt_title }} - Click to resend mail">
                                                                                                                            <i class="feather feather-mail text-primary"></i>
                                                                                                                        </a>
                                                                                                                    @endif
                                                                                                                @endif
                                                                                                            @endforeach --}}

                                                                                                            {{-- @if (!$hasMailLog)
                                                                                                                <a href="javascript:void(0);"
                                                                                                                    class="action-btns sendMail"
                                                                                                                    data-id="{{ Crypt::encrypt($candidateItem->rc_id) }}"
                                                                                                                    onclick="sendMailData(this)"
                                                                                                                    data-bs-toggle="popover"
                                                                                                                    data-bs-placement="top"
                                                                                                                    data-bs-trigger="hover focus"
                                                                                                                    data-bs-content="Send mail to this candidate">
                                                                                                                    <i
                                                                                                                        class="feather feather-mail text-primary"></i>
                                                                                                                </a>
                                                                                                            @endif --}}


                                                                                                            @php
                                                                                                                $mailTitles = [];
                                                                                                            @endphp

                                                                                                            @foreach ($emal_logs as $data)
                                                                                                                @if ($candidateItem->rc_id == $data->rel_candidate_id)
                                                                                                                    @php
                                                                                                                        $mail_template =
                                                                                                                            $mailTemplates[
                                                                                                                                $data
                                                                                                                                    ->rel_mail_template_id
                                                                                                                            ] ??
                                                                                                                            null;
                                                                                                                        if (
                                                                                                                            $mail_template
                                                                                                                        ) {
                                                                                                                            $mailTitles[] =
                                                                                                                                $mail_template->mt_title;
                                                                                                                        }
                                                                                                                    @endphp
                                                                                                                @endif
                                                                                                            @endforeach

                                                                                                            @if (!empty($mailTitles))
                                                                                                                <a href="javascript:void(0);"
                                                                                                                    class="action-btns sendMail"
                                                                                                                    data-id="{{ Crypt::encrypt($candidateItem->rc_id) }}"
                                                                                                                    onclick="sendMailData(this)"
                                                                                                                    data-bs-toggle="popover"
                                                                                                                    data-bs-placement="top"
                                                                                                                    data-bs-trigger="hover focus"
                                                                                                                    data-bs-content="{{ implode(', ', $mailTitles) }} - Click to resend mail">
                                                                                                                    <i
                                                                                                                        class="feather feather-mail text-primary"></i>
                                                                                                                </a>
                                                                                                            @else
                                                                                                                <a href="javascript:void(0);"
                                                                                                                    class="action-btns sendMail"
                                                                                                                    data-id="{{ Crypt::encrypt($candidateItem->rc_id) }}"
                                                                                                                    onclick="sendMailData(this)"
                                                                                                                    data-bs-toggle="popover"
                                                                                                                    data-bs-placement="top"
                                                                                                                    data-bs-trigger="hover focus"
                                                                                                                    data-bs-content="No emails sent yet - Click to send first mail">
                                                                                                                    <i
                                                                                                                        class="feather feather-mail text-muted"></i>
                                                                                                                </a>
                                                                                                            @endif



                                                                                                            <a href="{{ !empty($candidateItem->rc_resume) ? $candidateItem->rc_resume : '' }}"
                                                                                                                class="action-btns sendMail"
                                                                                                                data-id="{{ Crypt::encrypt($candidateItem->rc_id) }}"
                                                                                                                target="_blank">
                                                                                                                <i class="fe fe-file text-primary"
                                                                                                                    data-bs-toggle="tooltip"
                                                                                                                    data-bs-placement="top"
                                                                                                                    title="Resume"></i>
                                                                                                            </a>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endforeach
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END ROW -->
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination Links -->
        <div class="card-footer d-flex justify-content-end">
            {{ $recruitment->links() }}
        </div>
    </div>
    <x-modal id="sendMailModal" title="Send Mail" formId="createSendMailForm" action="{{ route('send.mail.store') }}"
        method="POST" enctype="multipart/form-data" size="modal-md" submitButtonText="Save"
        submitButtonId="saveSendMailButton">
        @csrf
        <input type="hidden" id="rel_candidate_id" name="rel_candidate_id">
        {{-- <x-input id="ris_interview_date" name="ris_interview_date" type="text" label="To" astric="*"
            value="" /> --}}
        <x-input id="rel_subject" name="rel_subject" type="text" label="Subject" astric="*" value="" />

        <div class="mb-3">
            <label for="rel_mail_template_id" class="form-label">
                Template
                <span style="color:red">*</span>
            </label>
            <select name="rel_mail_template_id" id="rel_mail_template_id" class="form-select sumo_search">
                <option value="">Select Template</option>
            </select>
            <span class="text-danger" id="rel_mail_template_id_error"></span>
            @error('rel_mail_template_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <label for="" class="form-label">Message Body</label>
        <textarea class="summernote" name="rel_body"></textarea>
        <span class="text-danger" id="rel_body_error"></span>
        <x-input id="rel_attachment" name="rel_attachment" type="file" label="Attachment" astric="*"
            value="" />

    </x-modal>

    <x-modal id="createScheduleInterviewModal" title="Schedule Interview" formId="createInterviewScheduleForm"
        action="{{ route('interview.schedule.store') }}" method="POST" enctype="multipart/form-data" size="modal-md"
        submitButtonText="Save" submitButtonId="saveInterviewScheduleButton">
        @csrf
        <input type="hidden" id="ris_candidate_id" name="ris_candidate_id">
        <x-select id="ris_interviewer" name="ris_interviewer[]" class="sumo_search" label="Interviewer"
            :options="$employees" selected="false" astric="true" multiple />
        <x-input id="ris_interview_date" name="ris_interview_date" type="date" label="Interview Date" astric="*"
            value="{{ date('Y-m-d') }}" />
        <x-input id="ris_interview_time" name="ris_interview_time" type="time" label="Interview Time" value=""
            astric="*" />
        <x-textarea id="ris_description" label="Description" name="ris_description" placeholder="Description"
            astric="*" />
        <div class="form-group">
            <label class="custom-switch">
                <span class="custom-switch-description me-2">Is Interview Completed</span>
                <input type="checkbox" name="ris_completed" class="custom-switch-input">
                <span class="custom-switch-indicator"></span>
            </label>
        </div>
    </x-modal>
@endsection

@section('script')
    <!-- INTERNAL JS -->
    <script src="{{ asset('assets/plugins/summer-note/summernote1.js') }}"></script>
    <script src="{{ asset('assets/js/summernote.js') }}"></script>
    <script>
        function updateStage(candidateIds, newStageId, currentStageName, newStageName) {
            // Check if we are updating a single candidate or bulk
            let isBulkUpdate = Array.isArray(candidateIds);

            // Show SweetAlert confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to move the candidates from "${currentStageName}" to "${newStageName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update!',
                cancelButtonText: 'No, cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/update-candidate-stage', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
                            },
                            body: JSON.stringify({
                                candidate_id: candidateIds,
                                stage_id: newStageId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                if (isBulkUpdate) {
                                    // Bulk update: Loop through all updated candidates
                                    $.each(data.updated_candidates, function(index, candidateId) {
                                        let row = document.querySelector(
                                            `#candidate-row-${candidateId}`);
                                        if (row) {
                                            row.remove(); // Remove the row from the old table
                                            // Find the target table for the new stage
                                            let targetTable = document.querySelector(
                                                `#hr-payroll${data.recruitmentKey}${newStageId} tbody`
                                            );
                                            if (targetTable) {
                                                targetTable.appendChild(
                                                    row); // Append to the new stage table
                                            } else {
                                                console.error("Target table not found for stage:",
                                                    newStageId);
                                            }
                                        }
                                    });
                                } else {
                                    // Single candidate update: Move the row
                                    let row = document.querySelector(`#candidate-row-${candidateIds}`);
                                    if (row) {
                                        row.remove();
                                        let targetTable = document.querySelector(
                                            `#hr-payroll${data.recruitmentKey}${newStageId} tbody`);
                                        if (targetTable) {
                                            targetTable.appendChild(row);
                                        } else {
                                            console.error("Target table not found for stage:", newStageId);
                                        }
                                    }
                                }

                                Swal.fire('Success!', 'Stage updated successfully!', 'success');
                            } else {
                                Swal.fire('Error', 'Failed to update stage: ' + data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'An error occurred while updating the stage.', 'error');
                        });
                } else {
                    // If user cancels, just show a message
                    Swal.fire('Cancelled', 'Stage update was cancelled.', 'info');
                }
            });
        }

        // Show create interview schedule modal when clicking on the "Schedule Interview" button
        $(document).on('click', '.createScheduleInterview', function() {
            $('#ris_candidate_id').val($(this).data('id'));
        });

        $(document).on('click', '.sendMail', function() {
            $('#rel_candidate_id').val($(this).data('id'));
        });
    </script>
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>

    <script>
        function sendMailData(element) {
            let candidateId = $(element).data('id');
            // console.log('Encrypted Candidate ID:', candidateId);
            $.ajax({
                url: '{{ route('fetch.candidate.data') }}',
                type: 'GET',
                data: {
                    id: candidateId
                },
                success: function(response) {
                    console.log('AJAX Success:', response);
                    if (response.options) {
                        $('#rel_mail_template_id').html('<option value="">Select Template</option>' + response
                            .options);

                        // If using SumoSelect
                        if ($('#rel_mail_template_id')[0].sumo) {
                            $('#rel_mail_template_id')[0].sumo.reload();
                        }

                        $('#sendMailModal').modal('show');
                    } else {
                        Swal.fire({
                            title: 'No mail templates available.',
                            icon: 'warning',
                        })


                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    alert('Something went wrong. Please try again.');
                }
            });
        }
    </script>

@endsection
