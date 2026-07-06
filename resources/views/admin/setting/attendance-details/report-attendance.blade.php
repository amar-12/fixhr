<?php
use App\Helpers\RolePermissionLogics;
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')
@section('title')
    Report
@endsection
@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container-fluid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .section {
            background-color: #fff;
            padding: 12px;
            /* border: 1px solid #ddd; */
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 4px solid #ddd;
            padding-bottom: 10px;
        }
        .ul {
            list-style: none;
            /* Remove default bullets */
            padding: 0;
        }
        .li {
            /* margin: 17px 0; */
            /* Add space between each item */
            display: flex;
            align-items: center;
            /* Align text and icon */
            color: #007bff;
        }
        .li a {
            color: #007bff;
            /* Apply the desired color to the text */
            text-decoration: none;
            /* Remove underline for links */
        }
        .li a:hover {
            text-decoration: underline;
            /* Optional: Add underline on hover */
        }
        .li::before {
            content: '\276F';
            /* Unicode for right arrow icon */
            font-size: 14px;
            color: black;
            margin-right: 8px;
            /* Add space between the icon and text */
        }
    </style>
@endsection
@section('content')
    <div>
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="active"><span><b>Report</b></span></li>
            </ol>
        </div>
        <!-- END ROW -->
        <!-- ROW -->
        <div class="row pt-5">
            <div class="container-fluid">
                <div class="section">
                     <h3 class="pb-3">Employee</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('employee.report', ['slug' => 'employee-detail']) }}">Employee
                                Report</a></li>
                        <li class="li"><a
                                href="{{ route('employee.report', ['slug' => 'employee-birthday']) }}">Employee
                                Birthday Report</a></li>
                        <li class="li"><a href="{{ route('employee.report', ['slug' => 'employee-joining']) }}">Employee
                                Joining Report</a></li>
                    </ul>
                </div>
                {{-- <div class="section">
                     <h3 class="pb-3">Monthly Attendance</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('attendance.report.export') }}">Monthly Basic Report</a></li>
                        <li class="li"><a href="{{ route('detailed.attendance.report.export') }}">Detailed Report</a>
                        </li>
                        <li class="li"><a href="{{ route('selfie.attendance.report.export') }}">Selfie Punch Report</a>
                        </li>
                    </ul>
                </div> --}}
                <div class="section">
                     <h3 class="pb-3">Attendance</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('attendance.report', ['slug' => 'daily-attendance']) }}">Daily
                                Attendance Report</a> </li>
                        <li class="li"><a
                                href="{{ route('attendance.report', ['slug' => 'monthly-attendance-detail']) }}">Monthly
                                Detail Report</a></li>
                        <li class="li"><a
                                href="{{ route('attendance.report', ['slug' => 'monthly-attendance-basic']) }}">Monthly
                                Basic Report</a></li>
                        <li class="li"><a
                                href="{{ route('attendance.report', ['slug' => 'monthly-attendance-in-out']) }}">Monthly
                                In/Out Report</a></li>
                        <li class="li"><a href="{{ route('attendance.report', ['slug' => 'yearly-summary']) }}">Yearly
                                Summary Report</a>
                        </li>
                        <li class="li"><a
                                href="{{ route('attendance.report', ['slug' => 'selfie-attendance']) }}">Selfie Attendance
                                Report</a>
                        </li>
                    </ul>
                </div>
                <div class="section">
                     <h3 class="pb-3">Monthly Leave Report</h3>
                    <ul class="ul">

                        <li class="li"><a href="{{ route('leave.report', ['slug' => 'daily-forecast']) }}">Attndance Forecast</a></li>
                        <li class="li"><a href="{{ route('leave.report', ['slug' => 'department-wise-forecast']) }}">Department Wise Forecast</a></li>

                        <li class="li"><a href="{{ route('leave.report', ['slug' => 'summery']) }}">Leave Summery
                                Report</a></li>
                        <li class="li"><a href="{{ route('leave.report', ['slug' => 'deatails']) }}">Leave Details
                                Report</a></li>
                        <li class="li"><a href="{{ route('leave.report', ['slug' => 'balance']) }}">Balance Leave
                                Report</a></li>
                    </ul>
                </div>
                <div class="section">
                     <h3 class="pb-3">Payroll</h3>
                    {{-- <!-- <li class="li"><a href="{{ route('employee.payroll.report.export') }}">Payroll Report</a></li> --> --}}
                    {{-- <li class="li"><a href="{{route('bank.sheet.export')}}">Bank Sheet Report</a></li> --}}
                    <li class="li"><a
                            href="{{ route('employee.payroll.report.export.sheet', ['slug' => 'payroll-report']) }}">
                            Monthly Payroll Report</a>
                    </li>
                    <li class="li"><a href="{{ route('mc.template.report') }}">Monthly Contribution Template</a></li>
                    <li class="li"><a
                            href="{{ route('employee.payroll.report.export.sheet', ['slug' => 'yearly-processed-salary-report']) }}">
                            Yearly Payroll Report</a></li>
                    <li class="li"><a href="{{ route('adhoc.report.export.sheet') }}">Adhoc Payments/Deductions</a>
                    </li>
                    <li class="li"><a href="{{ route('bank.sheet') }}">Bank Sheet</a></li>
                    <ul class="ul">
                    </ul>
                </div>
                                {{-- <div class="section"> --}}
                     {{-- <h3 class="pb-3">Statutory Reports</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('pf.eps.summary.report.export.sheet') }}">PF And EPS Summary
                                Report</a></li>
                        <li class="li"><a href="{{ route('esic.report.export.sheet') }}">ESIC Report</a></li>
                        <li class="li"><a href="{{ route('epf.ecr.report.export.sheet') }}">EPF And ECR Report</a></li>
                    </ul> --}}
                {{-- </div> --}}
                <div class="section">
                    <h3 class="pb-3">Tax Form Reports</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('tax.form.report', ['slug' => 'form-16a']) }}">Form 16A Report</a></li>
                        <li class="li"><a href="{{ route('tax.form.report', ['slug' => 'form-16']) }}">Form 16 Report</a></li>
                        <li class="li"><a href="{{ route('tax.form.report', ['slug' => 'form-15g']) }}">Form 15G Report</a></li>
                    </ul>
                </div>
                <div class="section">
                     <h3 class="pb-3">Travel</h3>
                    <ul class="ul">
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'travel-report']) }}">Travel Report</a>
                        </li>
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'travel-advance-report']) }}">Travel
                                Advance Report</a></li>
                        <li class="li"><a
                        href="{{ route('travel.report.index', ['report' => 'travel-attendance-report']) }}">Travel
                        Attendance Report</a></li>
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'travel-application-detailed-report']) }}">Travel
                                Application Detailed Report</a></li>
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'claim-basic-report']) }}">Claim Basic
                                Report</a></li>
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'claim-detailed-report']) }}">Claim
                                Detailed Report</a></li>
                        <li class="li"><a
                                href="{{ route('travel.report.index', ['report' => 'claim-summary-report']) }}">Claim
                                Summary Report</a></li>
                    </ul>
                </div>
                
                <div class="section">
                     <h3 class="pb-3">Policy</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('policy.report', ['slug' => 'holiday-policy']) }}">Holiday
                                Policy</a></li>
                        <li class="li"><a href="{{ route('policy.report', ['slug' => 'week-off-policy']) }}">Week Off
                                Policy</a></li>
                    </ul>
                </div>
                <div class="section">
                     <h3 class="pb-3">Loan</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('loan.report', ['slug' => 'loan-register']) }}">Loan
                                Register</a></li>
                        <li class="li"><a href="{{ route('loan.report', ['slug' => 'loan-projection']) }}">Loan
                                Projection</a></li>
                        {{-- <li class="li"><a href="{{ route('loan.report', ['slug' => 'loan-statements']) }}">Loan
                                Statements</a></li> --}}

                    </ul>
                </div>

                <div class="section">
                    <h3 class="pb-5">Assets</h3>
                    <ul class="ul">
                        <li class="li"><a href="{{ route('assets.report.summary') }}">Asset Summary</a></li>
                        <li class="li"><a href="{{ route('assets.report.stock') }}">Stock</a></li>
                        <li class="li"><a href="{{ route('assets.report.assigned') }}">Assigned</a></li>
                        <li class="li"><a href="{{ route('assets.report.service') }}">Service / Maintenance</a></li>
                        <li class="li"><a href="{{ route('assets.report.scrap') }}">Scrap</a></li>
                        <li class="li"><a href="{{ route('assets.report.replace') }}">Replacement</a></li>
                    </ul>
                </div>

                <div class="section">
                     <h3 class="pb-3">Auxiliary Report</h3>
                    <ul class="ul">
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'late-coming']) }}">Late Coming Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'early-going']) }}">Early Going Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'half-day']) }}">Half Day Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'present']) }}">Present Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'absent']) }}">Absent Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'missed-punch']) }}">Missed Punch Report</a>
                        </li>
                        {{-- <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'comp-off']) }}">Comp Off Report</a>
                        </li> --}}
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'holiday-present']) }}">Holiday Present
                                Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'weekly-off-present-detailed']) }}">Weekly
                                Off Present Detailed Report
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'weekly-off-present-summary']) }}">Weekly Off
                                Present Summary
                                Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'continuous-absent']) }}">Continuous Absent
                                Report</a>
                        </li>
                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'continuous-leave']) }}">Continuous Leave
                                Report</a>
                        </li>


                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'gate-pass']) }}">Gate Pass Report</a>
                        </li>


                        <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'attendance-regularization']) }}">Attendance Regularization Report</a>
                        </li>

                         <li class="li">
                            <a href="{{ route('monitoring.report', ['slug' => 'missed-punch-regularization']) }}">Missed Punch Regularization Report</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script></script>
