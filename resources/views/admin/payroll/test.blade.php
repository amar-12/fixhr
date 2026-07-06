@extends('admin.layout.master')

@section('title', $pageTitle)

@section('content')
<!-- PAGE HEADER -->
<!-- <x-breadcrumb :breadcrumbs="$breadcrumbs ?? []" /> -->
<div class="page-header d-md-flex d-block">
    <div class="page-leftheader">
        <div class="py-0 bd-highlight">
            <div>
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="">Payroll</a></li>
                    <li class="active"><span><b>Deductions</b></span></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="page-header d-flex justify-content-between align-items-center">
    <div class="page-leftheader">
        <div class="page-title">Payroll Deduction</div>
    </div>
</div>

<x-modal id="createTDSModal" title="Create TDS" formId="createTDSForm"
    action="{{ route('payroll.deduction.create.update') }}" method="POST" enctype="multipart/form-data" size="modal-md"
    submitButtonText="Save TDS" submitButtonId="saveTDSButton">
    <input type="hidden" name="std_deduction_type_id" value="354">
    {{-- Hidden field for ID (only set for editing) --}}
    <input type="hidden" id="its_id" name="its_id">

    <x-input type="number" id="its_income_from" label="Income From" name="its_income_from"
        placeholder="Enter Income From" astric="*" maxlength="15" />

    <x-input type="number" id="its_income_to" label="Income To" name="its_income_to" placeholder="Enter Income To"
        maxlength="15" />

    <x-input type="number" id="its_tax_rate" label="Tax Rate (%)" name="its_tax_rate" placeholder="Enter Tax Rate"
        astric="*" maxlength="5" />
</x-modal>

<!-- ROW -->
<div class="row">
    <div class="col-md-12">
        <!-- Tabs -->
        <div class="card shadow">
            <div class="card-header py-0">
                <ul class="nav nav-tabs" id="approvalSettingsTab" role="tablist">
                    @foreach ($statutoryDeduction as $id => $title)
                    @continue($id == 354) {{-- ← don’t render tab for type‑354 --}}

                    <li class="nav-item">
                        <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="{{ strtolower(str_replace(' ', '-', $title)) }}-tab" data-bs-toggle="tab"
                            href="#tab-{{ $id }}" role="tab">
                            {{ $title }}
                        </a>
                    </li>
                    @endforeach
                </ul>

            </div>

            <!-- Tab Content -->
            <div class="card-body  py-0">
                <div class="tab-content" id="approvalSettingsTabContent">
                    <!-- Dynamic Form Content for Each Tab -->
                    @foreach ($statutoryDeduction as $id => $title)
                    @php
                    $oldData = $statutoryDeductionData->where('std_deduction_type_id', $id)->first();
                    @endphp
                    {{--
                    <pre>{{$oldData}}</pre> --}}
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $id }}"
                        role="tabpanel">
                        @if ($id != 353)
                        <form id="form-{{ $id }}" method="POST" action="{{ route('payroll.deduction.create.update') }}"
                            enctype="multipart/form-data">
                            @csrf
                            @endif
                            <div class="card-header border-0 px-4">
                                <h4 class="card-title">{{ $title }}</h4>
                                <input type="hidden" name="std_id" value="{{ $oldData->std_id ?? '' }}">
                                <!-- Dynamically pass the unique m_id -->
                                <input type="hidden" name="std_deduction_type_id" value="{{ $id }}">
                                <input type="hidden" name="title" value="{{ $title }}">
                                @if ($id == 351)
                                <i class="feather feather-info text-muted" role="button" data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title="As per the Employees’ Provident Fund Act, the employer contributes 12 % of Basic + DA — split as 3.67 % into EPF, 8.33 % into EPS (pension, capped at ₹15,000 wage). In addition, the employer pays 0.5 % EDLI, 0.5 % EPF admin and 0.01 % EDLI admin, taking their total outflow to ≈ 13.61 %. The employee contributes a flat 12 % into EPF.">
                                </i>
                                @endif

                            </div>

                            @if ($id == 353)
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th style="width:60px;">#</th>
                                                <th>State</th>
                                                <th class="text-end">Income&nbsp;From&nbsp;(₹)</th>
                                                <th class="text-end">Income&nbsp;To&nbsp;(₹)</th>
                                                <th class="text-end">Tax&nbsp;Amount&nbsp;(₹)</th>
                                                <th>Cycle</th>
                                                <th>Gender</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($professionalTaxMaster as $index => $slab)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $slab->state?->s_name ?? '-' }}</td>
                                                <td class="text-end">{{ number_format($slab->ptm_income_from, 0) }}</td>
                                                <td class="text-end">
                                                    {{ $slab->ptm_income_to
                                                    ? number_format($slab->ptm_income_to, 0)
                                                    : 'No Limit' }}
                                                </td>
                                                <td class="text-end">{{ number_format($slab->ptm_tax_amount, 0) }}</td>
                                                <td>{{ $slab->cycle?->m_name ?? '-' }}</td>
                                                <td>{{ $slab->gender?->m_name ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    No Professional‑Tax slabs found.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            @elseif($id == 357) {{-- Assuming 355 is the ID for Labour Welfare Fund --}}
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle mb-0">
                                        <thead class="bg-primary text-white">
                                            <tr>
                                                <th style="width:60px;">#</th>
                                                <th>State</th>
                                                <th>Cycle</th>
                                                <th class="text-end">Employee Contribution (₹)</th>
                                                <th class="text-end">Employer Contribution (₹)</th>
                                                <th class="text-end">Total Contribution (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($labourWelfareMasters as $index => $lwf)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>

                                                {{-- STATE COLUMN --}}
                                                <td>
                                                    {{ $lwf->state?->s_name ?? '-' }}

                                                    {{-- Custom Text Conditions --}}
                                                    @if($lwf->state?->s_name == 'Maharashtra' &&
                                                    $lwf->lwf_employee_contri == 6)
                                                    <br>
                                                    <small class="text-muted">
                                                        (Salary up to Rs. 3,000 per month)
                                                    </small>

                                                    @elseif($lwf->state?->s_name == 'Maharashtra' &&
                                                    $lwf->lwf_employee_contri == 12)
                                                    <br>
                                                    <small class="text-muted">
                                                        (Salary more than Rs. 3,000 per month)
                                                    </small>

                                                    @elseif($lwf->state?->s_name == 'Kerala' && $lwf->cycle?->m_name ==
                                                    'Monthly')
                                                    <br>
                                                    <small class="text-muted">
                                                        (For firms under Shops and Establishment Act)
                                                    </small>

                                                    @elseif($lwf->state?->s_name == 'Kerala' && $lwf->cycle?->m_name ==
                                                    'Half Yearly')
                                                    <br>
                                                    <small class="text-muted">
                                                        (For firms under Factories Act)
                                                    </small>
                                                    @endif
                                                </td>

                                                {{-- CYCLE COLUMN --}}
                                                <td>{{ $lwf->cycle?->m_name ?? '-' }}</td>

                                                {{-- CONTRIBUTIONS --}}
                                                <td class="text-end">{{ number_format($lwf->lwf_employee_contri, 2) }}
                                                </td>
                                                <td class="text-end">{{ number_format($lwf->lwf_employer_contri, 2) }}
                                                </td>
                                                <td class="text-end">{{ number_format($lwf->total_contribution, 2) }}
                                                </td>
                                            </tr>

                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    No Labour Welfare Fund records found.
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            {{-- @elseif($id == 354)
                            <div class="card-body">
                                <x-button type="button" class="btn btn btn-primary create-button d-flex  float-end"
                                    data-bs-toggle="modal" data-bs-target="#createTDSModal"
                                    data-title="Create TDS Deduction">
                                    Create TDS Deduction
                                </x-button>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Income From</th>
                                            <th>Income To</th>
                                            <th>Tax Rate (%)</th>
                                            <th>Actions</th> <!-- Added Actions Column -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($incomeTaxSlabs as $slab)
                                        <tr>
                                            <td>{{ $slab->its_id }}</td>
                                            <td>{{ number_format($slab->its_income_from, 2) }}</td>
                                            <td>{{ $slab->its_income_to ? number_format($slab->its_income_to, 2) : '-'
                                                }}
                                            </td>
                                            <td>{{ $slab->its_tax_rate }}%</td>
                                            <td>
                                                <!-- Edit Button -->
                                                <button class="btn btn-sm btn-primary edit-button action-btns"
                                                    data-bs-toggle="modal" data-title="Edit TDS"
                                                    data-bs-target="#createTDSModal" data-edit-data='{!! json_encode([
                                                                    ' its_id'=> Crypt::encrypt($slab->its_id),
                                                    'its_income_from' => $slab->its_income_from,
                                                    'its_income_to' => $slab->its_income_to,
                                                    'its_tax_rate' => $slab->its_tax_rate,
                                                    'std_deduction_type_id' => $id,
                                                    ]) !!}'>
                                                    <i class="feather feather-edit"></i>
                                                </button>


                                                <!-- Delete Button -->
                                                </button><button class="btn btn-sm btn-danger delete-button action-btns"
                                                    data-id="{{ $slab->its_id }}" title="Delete"
                                                    data-url="{{ route('payroll.deduction.destroy', Crypt::encrypt($slab->its_id)) }}">
                                                    <i class="feather feather-trash"></i>
                                                </button>

                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <!-- Laravel Pagination Links -->
                                <div class="d-flex justify-content-center">
                                    {{ $incomeTaxSlabs->links() }}
                                </div>
                            </div> --}}


                            @elseif($id == 351)
                            <div class="card-body">
                                {{-- Info icon beside the title --}}
                                <div class="d-flex align-items-center mb-3">
                                    <h4 class="card-title mb-0">EPF Configuration</h4>
                                    <i class="feather feather-info ms-2" role="button" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        title="As per the Employees’ Provident Fund Act, the employer contributes 12 % of basic + DA — split as 3.67 % into EPF, 8.33 % into EPS (pension, capped at ₹15,000 wage). Additionally, the employer pays 0.5 % EDLI, 0.5 % EPF admin and 0.01 % EDLI admin, totaling ≈ 13.61 %. The employee contributes a flat 12 % into EPF.">
                                    </i>
                                </div>

                                {{-- ─────────────── ROW‑1 (4 fields) ─────────────── --}}
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <x-select id="std_deduction_cycle_id-{{ $id }}" name="std_deduction_cycle_id"
                                            label="Deduction Cycle" :options="$deductionCycle"
                                            selected="{{ $oldData->std_deduction_cycle_id ?? '' }}" astric="true" />
                                    </div>

                                    <div class="col-md-3">
                                        <x-input id="std_employee_contri_rate_amount-{{ $id }}"
                                            name="std_employee_contri_rate_amount" type="number"
                                            label="Employee Contribution Rate (%)" placeholder="e.g. 12"
                                            value="{{ $oldData->std_employee_contri_rate_amount ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col-md-3">
                                        <x-input id="std_employer_contri_rate_amount-{{ $id }}"
                                            name="std_employer_contri_rate_amount" type="number"
                                            label="Employer Contribution Rate (%)" placeholder="e.g. 13"
                                            value="{{ $oldData->std_employer_contri_rate_amount ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col-md-3">
                                        <x-input id="std_threshold-{{ $id }}" name="std_threshold" type="number"
                                            label="Salary Threshold Limit" placeholder="e.g. 15000"
                                            value="{{ $oldData->std_threshold ?? '' }}" step="0.01" />
                                    </div>
                                </div>

                                {{-- ─────────────── ROW‑2 (5 share fields in one line) ─────────────── --}}
                                <div class="row row-cols-md-5 g-3 mt-0">
                                    <div class="col">
                                        <x-input id="epf_employer_share-{{ $id }}" name="epf_employer_share"
                                            type="number" label="EPF Employer Share (%)" placeholder="3.67"
                                            value="{{ $oldData->epf_employer_share ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col">
                                        <x-input id="eps_employer_share-{{ $id }}" name="eps_employer_share"
                                            type="number" label="EPS Employer Share (%)" placeholder="8.33"
                                            value="{{ $oldData->eps_employer_share ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col">
                                        <x-input id="edli_employer_share-{{ $id }}" name="edli_employer_share"
                                            type="number" label="EDLI Contribution (%)" placeholder="0.50"
                                            value="{{ $oldData->edli_employer_share ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col">
                                        <x-input id="admin_charges-{{ $id }}" name="admin_charges" type="number"
                                            label="EPF Admin (%)" placeholder="0.50"
                                            value="{{ $oldData->admin_charges ?? '' }}" step="0.01" />
                                    </div>

                                    <div class="col">
                                        <x-input id="edli_admin_charges-{{ $id }}" name="edli_admin_charges"
                                            type="number" label="EDLI Admin (%)" placeholder="0.01"
                                            value="{{ $oldData->edli_admin_charges ?? '' }}" step="0.01" />
                                    </div>
                                </div>

                                {{-- Enable/Disable Switch and Save --}}
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <label class="form-label">Disable/Enable EPF</label>
                                        <label class="custom-switch">
                                            <input type="checkbox" name="std_status" class="custom-switch-input" {{
                                                isset($oldData->std_status) && $oldData->std_status == 1 ? 'checked' :
                                            '' }}>
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-outline-primary">Save</button>
                                </div>
                            </div>




                            @else
                            <div class="card-body">
                                <div class="form-group row">
                                    <div class="col-md-3 mb-4">
                                        <x-select id="std_deduction_cycle_id-{{ $id }}" name="std_deduction_cycle_id"
                                            class="sumo_search" label="Deduction Cycle"
                                            selected="{{ $oldData->std_deduction_cycle_id ?? '' }}"
                                            :options="$deductionCycle" astric="true" />
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <x-input id="std_employee_contri_rate_amount-{{ $id }}"
                                            name="std_employee_contri_rate_amount" type="number"
                                            label="Employee Contribution Rate"
                                            placeholder="Enter Employee Contribution Rate"
                                            value="{{ $oldData->std_employee_contri_rate_amount ?? '' }}" astric="*"
                                            step="0.01" />
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <x-input id="std_employer_contri_rate_amount-{{ $id }}"
                                            name="std_employer_contri_rate_amount" type="number"
                                            label="Employer Contribution Rate"
                                            placeholder="Enter Employer Contribution Rate"
                                            value="{{ $oldData->std_employer_contri_rate_amount ?? '' }}" astric="*"
                                            step="0.01" />
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <x-input id="std_threshold-{{ $id }}" name="std_threshold" type="number"
                                            label="Threshold" value="{{ $oldData->std_threshold ?? '' }}"
                                            placeholder="Enter Threshold" step="0.01" />
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-0">
                                        <div>
                                            <div class="form-group">
                                                <div class="form-label">Disable/Enable {{ $title }}
                                                </div>
                                                <label class="custom-switch">
                                                    <input type="checkbox" id="std_status-{{ $id }}" name="std_status"
                                                        class="custom-switch-input" {{ isset($oldData->std_status) ?
                                                    ($oldData->std_status == 1 ? 'checked' : '') : '' }}>
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-outline-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if ($id != 353)
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div> <!-- End Card Body -->
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
<script>
    $(document).ready(function() {
        const handleTabActivation = () => $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        });
        handleTabActivation();
        $(document).on("click", ".edit-btn", function() {
            let id = $(this).data('id');
            let brId = $(this).data('br-id');
            let card = $(this).closest('.card');
            let branch = card.find("h5").text().trim();

            let stateText = card.find("p").eq(0).text()
                .trim(); // Extracts full text: "State: Maharashtra"
            let stateValue = stateText.replace("State:", "").trim(); // Extracts only "Maharashtra"
            console.log(stateValue);


            let incomeText = card.find("p").eq(1).text().trim();
            let incomeValues = incomeText.match(/₹([\d.]+)\s*-\s*₹([\d.]+)/); // Extract numeric values

            let taxText = card.find("p").eq(2).text().trim();
            let taxValue = taxText.match(/₹([\d.]+)/); // Extract tax amount

            let incomeFrom = incomeValues ? incomeValues[1] : "";
            let incomeTo = incomeValues ? incomeValues[2] : "";
            let taxAmount = taxValue ? taxValue[1] : "";
            console.log("click");

            // Replace only the content inside the card (keeping the outer structure)
            card.html(`

                        <input type="hidden" name="id" value="${id}">
                        <input type="hidden" name="brId" value="${brId}">

                        <div class="row mb-2">
                            <label for="pts_branch_id" class="col-sm-4 col-form-label">Branch Name :</label>
                            <div class="col-sm-8">
                                <span><strong>${branch}</strong></span>
                            </div>
                        </div>
                         <div class="row mb-2">
                            <label for="pts_state_id" class="col-sm-4 col-form-label">State :</label>
                            <div class="col-sm-8">
                                <span><strong>${stateValue}</strong></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="pts_income_from" class="col-sm-4 col-form-label">Income From <span style="color:red">*</span> :</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="pts_income_from" value="${incomeFrom}">
                                  <span class="text-danger error-text" id="pts_income_from_error"></span>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="pts_income_to" class="col-sm-4 col-form-label">Income To <span style="color:red">*</span> :</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="pts_income_to" value="${incomeTo}">
                                                <span class="text-danger error-text" id="pts_income_to_error"></span>

                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="pts_tax_amount" class="col-sm-4 col-form-label">Tax :</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" name="pts_tax_amount" value="${taxAmount}">
                                                <span class="text-danger error-text" id="pts_tax_amount_error"></span>

                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn btn-outline-primary btn-sm">Update</button>
                            <button type="button" class="btn btn-outline-danger btn-sm cancel-btn">Cancel</button>
                        </div>

                `);
        });

        // Cancel Edit (restore original view)
        $(document).on("click", ".cancel-btn", function() {
            location.reload(); // Simple way to reload the card list
        });

    });

</script>
@endsection