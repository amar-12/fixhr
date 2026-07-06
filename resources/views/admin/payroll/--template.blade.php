<?php
use App\Helpers\RolePermissionLogics;

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}
@endsection
@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div>
        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0">
                        <h4 class="card-title">Salary Templates                        </h4>
                    </div>
                    <div class="page-rightheader ms-auto mx-4">
                        <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                            <div class="btn-list d-flex">
                                @if ($permission->check_route_permission('payroll/template/create', 115))

                                        <button type="button" class="btn btn-outline-primary" id="addTemplateBtn">Create New</button>

                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                        style="width: 100px">
                                        <option value="5" style="width: 100px">5</option>
                                        <option value="10" style="width: 100px">10</option>
                                        <option value="25" style="width: 100px">25</option>
                                        <option value="50" style="width: 100px">50</option>
                                        <option value="100" style="width: 100px">100</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-1 col-sm-4 pt-5 mt-1" align="right">
                                <div class="btn-group">
                                    <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                    </ul>
                                </div>

                            </div>
                            <div class="col-md-8 col-sm-4"></div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <div class="form-group mb-3">
                                        <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                            data-search />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom" id="arroval-table-dynamic">
                                <thead>
                                    <tr>
                                        @foreach ($columns as $column)
                                            <th style="font-size: 13px">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="row mt-5">
                            <div class="col-sm-6">
                                <div id="custom-show-entries" data-show-entries></div>
                            </div>
                            <div class="col-sm-6 d-flex justify-content-end">
                                <ul data-pagination class="custom-pagination"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateModalTitle">Add Salary Template</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class=" mt-5">
                    <div class="card">
                      <div class="card-body">
                        <!-- Form Header -->
                        <div class="row mb-3">
                          <div class="col-md-6">
                            <label for="templateName" class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input
                              type="text"
                              id="templateName"
                              class="form-control"
                              placeholder="Enter Template Name"
                            />
                          </div>
                          <div class="col-md-6">
                            <label for="description" class="form-label">Description</label>
                            <textarea
                              id="description"
                              class="form-control"
                              rows="1"
                              placeholder="Max 500 Characters"
                            ></textarea>
                          </div>
                        </div>

                        <!-- Annual CTC -->
                        <div class="row mb-4">
                          <label class="form-label col-md-2">Annual CTC</label>
                          <div class="col-md-4 d-flex">
                            <span class="input-group-text">&#8377;</span>
                            <input
                              type="text"
                              id="annualCTC"
                              class="form-control"
                              placeholder="Enter CTC"
                            />
                            <span class="input-group-text">per year</span>
                          </div><label class="form-label col-md-2">Monthly CTC</label>
                          <div class="col-md-4 d-flex">
                            <span class="input-group-text">&#8377;</span>
                            <input
                              type="text"
                              id="annualCTC"
                              class="form-control"
                              placeholder="Enter CTC"
                            />
                            <span class="input-group-text">per month</span>
                          </div>
                        </div>

                        <!-- Earnings Table -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <h5 class="mb-0">Earnings</h5>
                          <button class="btn btn-outline-primary">New</button>
                        </div>
                        <table class="table table-bordered align-middle">
                          <thead>
                            <tr class="bg-light">
                              <th>Salary Components</th>
                              <th>Bifurcation</th>
                              {{-- <th>MONTHLY AMOUNT</th>
                              <th>ANNUAL AMOUNT</th> --}}
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($allowances as $allowance)
                            <tr>
                                <td>{{$allowance->sa_title}}</td>
                                <td>
                                  <div class="input-group">
                                    <input
                                      type="number"
                                      class="form-control"
                                      value="{{$allowance->sa_threshold_value}}"
                                      readonly
                                    />
                                    <span class="input-group-text">{{ optional($allowance->fh_allowance_calculation_type)->m_name }}</span>
                                  </div>
                                </td>
                                {{-- <td>
                                  <input
                                    type="number"
                                    class="form-control text-center"
                                    value="167"
                                    readonly
                                  />
                                </td>
                                <td>
                                  <input
                                    type="number"
                                    class="form-control text-center"
                                    value="2004"
                                    readonly
                                  />
                                </td> --}}
                              </tr>
                            @endforeach
                          </tbody>
                        </table>

                        <!-- Earnings Table -->
                        {{-- <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Deductions</h5>
                            <button class="btn btn-outline-primary">New</button>
                          </div>
                          <table class="table table-bordered align-middle">
                            <thead>
                              <tr class="bg-light">
                                <th>Deductions</th>
                                <th>Employee Contribution</th>
                                <th>Employer Contribution</th>
                                <th>Deduction Cycle</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach ($deductions as $deduction)
                              <tr>
                                  <td>{{optional($deduction->fh_deduction_type)->m_name }}</td>
                                  <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        value="{{$deduction->std_employee_contri_rate_amount}}"
                                        readonly
                                      />
                                  </td>
                                  <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        value="{{$deduction->std_employer_contri_rate_amount}}"
                                        readonly
                                      />
                                  </td>
                                  <td>
                                    <input
                                        class="form-control"
                                        value="{{$deduction->fh_deduction_cycle->m_name}}"
                                        readonly
                                      />
                                  </td>

                                </tr>
                              @endforeach
                            </tbody>
                          </table> --}}

                        <!-- Cost to Company -->
                        <div class="row salary-summary p-3 mt-4">
                          <div class="col-md-6 text-start">Cost to Company</div>
                          <div class="col-md-6 text-end">
                            <span class="fs-5">&#8377; 333</span>
                            <span class="fs-5 ms-4">&#8377; 4000</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
            </div>
        </div>
    </div>
    <!-- END MODAL -->

@endsection
@section('script')
<script type="text/javascript">
    $(document).on('click', '#addTemplateBtn', function() {
        $('#templateModal').modal('show');
    });
</script>
@endsection
