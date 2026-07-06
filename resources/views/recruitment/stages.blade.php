@extends('admin.layout.master')

@section('title', 'Stage Management')

@section('css')

    <style>
        .export-button {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .export-button:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .dropdown-menu-export {
            font-size: 14px;
            min-width: 140px;
        }

        .dropdown-menu-export .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .custom-button {
            display: flex;
            align-items: center;
            gap: 6px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .custom-button:hover {
            background-color: #f1f1f1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .custom-button svg {
            width: 16px;
            height: 16px;
        }

        #stage-table tbody tr:hover {
            background-color: rgb(236, 236, 236);
            /* light gray background */
            transition: background-color 0.2s ease-in-out;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    {{-- Breadcrumb Navigation --}}
    {{-- Breadcrumb Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/recruitment') }}">Recruitment</a></li>
                    <li class="active"><span><b>Stages</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button" data-bs-toggle="modal"
                                    data-bs-target="#createStageModal" data-title="Create Stage">
                                    Create Stage
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Breadcrumb End --}}


    {{-- Create Stage Modal --}}
    <x-modal id="createStageModal" title="Create Stage" formId="createStageForm" action="{{ route('stages.store') }}"
        method="POST" enctype="multipart/form-data" size="modal-lg" submitButtonText="Save Stage"
        submitButtonId="saveStageButton">

        {{-- Form fields --}}
        <div class="row">
            <div class="col-md-6">
                <x-input type="text" id="rsg_stage" label="Stage Name" name="rsg_stage" placeholder="Stage Name"
                    astric="*" maxlength="200" />
            </div>
            <div class="col-md-6">
                <x-select id="rsg_recruitment_id" name="rsg_recruitment_id" label="Recruitment" class="sumo_search"
                    astric="*" :options="$recruitment" placeholder="Select Recruitment" required />
            </div>
            <div class="col-md-6">
                <x-select id="rsg_managers" name="rsg_managers[]" label="Stage Managers" class="sumo_search" astric="*"
                    :options="$employees" placeholder="Select Stage Managers" required multiple />
            </div>
            <div class="col-md-6">
                <x-select id="rsg_stage_type" name="rsg_stage_type" label="Stage Type" class="sumo_search" astric="*"
                    :options="$stageType" placeholder="Select Stage Type" required />
            </div>
        </div>
    </x-modal>

    {{-- Stage Table Section --}}
    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Stage List</h4>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row">
                        {{-- Show Entries Dropdown --}}
                        <div class="col-md-1 col-sm-4">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                    style="width: 100px">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>

                        {{-- Export Options --}}

                        <div class="col-sm-1"
                            style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                            <div class="form-group dropdown">
                                <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    <i class="fa fa-download me-2"></i> Export As
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

                        {{-- Search Input --}}
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

                    {{-- Table Section --}}
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="stage-table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>

                    {{-- Pagination and Show Entries --}}
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

{{-- Scripts --}}
@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "stage-table",
                url: "{{ route('stages.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        });
    </script>

    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
@endsection
