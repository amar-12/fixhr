<?php
use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    On-Boarding
@endsection
@section('css')
@endsection

<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }


      #daily-attendance-table-dynamic tr:hover {
        background-color: rgb(236, 236, 236);
        /* light gray background */
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
    }
</style>

@section('content')
    <div>

        {{-- Bradcrumbs Start --}}
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li class="active"><span><b>On-Boarding</b></span></li>
                    </ol>
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-2">
                    <div class="page-rightheader ms-md-auto">
                        <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                            <div class="d-lg-flex d-block ms-auto">
                                <div class="btn-list">
                                    <button type="button" class="btn btn-outline-primary" id="stage" data-bs-toggle="modal"
                                data-bs-target="#createStage">Create Stage</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Bradcrumbs End --}}


        <!-- END ROW -->


        <!-- ROW -->
        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                        style="width: 100px" data-length>
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
                                <div class="form-group">
                                    <div class="form-group mb-3">
                                        <input type="text" id="searchFilter" placeholder="Search"
                                            class="form-control" data-search />
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table display table-hover table-vcenter text-wrap border-bottom"
                                id="daily-attendance-table-dynamic">
                                <thead>
                                    <tr>
                                        <th style="font-size: 13px">S.NO</th>
                                        <th style="font-size: 13px">Employee</th>
                                        <th style="font-size: 13px">Email</th>
                                        <th style="font-size: 13px">Joining Date</th>
                                        <th style="font-size: 13px">Status</th>
                                        <th style="font-size: 13px">Task</th>
                                        <th style="font-size: 13px">Stage</th>
                                        <th style="font-size: 13px">Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr></tr>
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


    {{-- Grade Creation Modal --}}
    <div class="modal fade" id="createStage" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Create Stage</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="stageForm">
                    @csrf
                    <div class="modal-body">
                        <label for="stagename" class="form-label mb-2 mt-3">Stage Name<span class="text-red">*</span></label>
                        <input id="stagename" name="stagename" type="text" class="form-control" placeholder="Enter Stage Name" required>

                        <div class="d-flex align-items-center justify-content-between pt-2">
                            <label for="Department" class="form-label">Stage Assign<span class="text-red">*</span></label>
                        </div>
                        <select name="stageAssign" id="stageAssign" class="form-control form-select travelType select2"
                            data-placeholder="Stage Assign" required>
                            <option selected>Manager</option>
                            <option value="1">TL</option>
                            <option value="2">HR</option>
                            <option value="3">Boss</option>
                        </select>
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
