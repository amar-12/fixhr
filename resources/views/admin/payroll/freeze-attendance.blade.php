

@extends('admin.layout.master')
@section('title')
    {{ $title }}
@endsection
@section('css')
        <style>
            @import url(https://fonts.googleapis.com/css?family=Open+Sans:600,400,300,300italic);


            .frame {
                position: absolute;
                top: 50%;
                left: 50%;
                width: 400px;
                height: 400px;
                margin-top: -200px;
                margin-left: -200px;
                border-radius: 2px;
                box-shadow: 1px 2px 10px 0 rgba(0, 0, 0, 0.3);
                background: #4CB6DE;
                color: #fff;
                font-family: 'Open Sans', Helvetica, sans-serif;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            .quote {
                position: relative;
                margin-top: 90px;
                padding: 0 30px;
            }

            .quote::before {
                content: '„';
                position: absolute;
                top: -100px;
                left: 7px;
                font-family: Arial;
                font-size: 250px;
                color: #6AC2E3;
                line-height: 35px;
            }

            .quote p {
                position: relative;
                font-size: 24px;
                line-height: 35px;
                margin: 20px 0;
            }

            .quote .author {
                font-weight: 300;
                font-style: italic;
                font-size: 20px;
                line-height: 28px;
            }

            .tooltipo {
                position: relative;
                display: inline-block;
                background: #41cbff;
                padding: 3px 7px 3px 6px;
                margin: -10px 0;
                cursor: pointer;
            }

            .tooltipo:hover .info,
            .tooltipo:focus .info {
                visibility: visible;
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }

            .info {
                position: absolute;
                bottom: 30px;
                left: -145px;
                background: #000;
                width: 300px;
                font-size: 16px;
                line-height: 24px;
                visibility: hidden;
                opacity: 0;
                transform: translate3d(0, -20px, 0);
                transition: all 0.5s ease-out;
            }

            .info::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 14px;
                bottom: -14px;
                left: 0;
            }

            .info::after {
                content: '';
                position: absolute;
                width: 10px;
                height: 10px;
                transform: rotate(45deg);
                bottom: -5px;
                left: 50%;
                margin-left: -5px;
                background: #286F8A;
            }

            .pronounce {
                display: block;
                background: #fff;
                color: #286F8A;
                padding: 8px 17px 10px 17px;
                line-height: 16px;
            }

            .pronounce .fa {
                margin-left: 10px;
                cursor: pointer;
                transition: all 0.2s ease-out;
            }

            .pronounce .fa:hover {
                transform: scale(1.15);
            }

            .text {
                display: block;
                padding: 13px 17px;
            }
        </style>
@endsection

@section('content')
        <div class="page-header d-md-flex d-block">
            <div class="page-leftheader">
                <div class="py-0 bd-highlight">
                    <div>
                        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                            <!-- <li><a href="/admin/employee/manage-salary">Payroll Management</a></li> -->
                            <li class="active"><span><b>{{ $title }}</b></span></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>


        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0">
                        <h4 class="card-title">Attendance Vault</h4>
                        {{-- <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addEmpSalaryBtn">Add Salary</button>
                            </div>
                        </div> --}}
                    </div>
                    <div class="card-body">
                        <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <p class="form-label">Year</p>
                                <select id="statusFilter" class="form-select-md search_test custom-heighlight">
                                <option value="">Select Year</option>

                                    @foreach($financialYears as $year)
                                        <option value="{{ $year->fy_id }}">{{ $year->fy_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <p class="form-label">Month</p>
                                <select id="statusFilter" class="form-select-md search_test custom-heighlight">
                                    <option value="">Select Month</option>
                                    @foreach($months as $month)
                                        <option value="{{ $month->m_id }}">{{ $month->m_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>




                            <div class="col-md-3">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <div class="form-group mb-3">
                                        <input type="search" id="searchFilter" placeholder="Search" class="form-control"
                                            data-search />
                                    </div>
                                </div>
                            </div>

                        </div>
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

                            <div class="col-md-9 col-sm-4"></div>

                            <div class="col-md-2 col-sm-4 pt-5" align="right">

                            <!-- <a href="{{ route('payroll.period.create') }}" class="btn btn-outline-primary" id="addPayrollPeriod">
                                Add Payroll Period
                            </a> -->
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPayrollPeriodModal">
                                Add Payroll Period
                            </button>


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

                        </div>

                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom"
                                id="manage-salary-table-dynamic">
                                <thead>
                                <tr>
                                        @foreach ($columns as $column)
                                            <th style="font-size: 13px">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                @php $i = 1; @endphp

                            </tbody>
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






@endsection



@section('script')



@endsection
