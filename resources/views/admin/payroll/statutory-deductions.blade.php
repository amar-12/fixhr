@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .bordered-box {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .disable-link {
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
        }
        .disable-link:hover {
            text-decoration: underline;
        }
        .sample-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #e1e1e1;
        }
        .calc-title {
            font-weight: bold;
            margin-bottom: 15px;
        }
        .small-text {
            font-size: 0.9rem;
            color: #555;
        }
        .contribution-section {
            margin-top: 10px;
        }
        .tab-content {
            margin-top: 20px;
        }
        ul li::marker {
            color: red;
        }
    </style>
@endsection
@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />

    <div class="container">
        <!-- Statutory Components Tabs -->
        <h3 class="mb-4">Statutory Components</h3>
        <ul class="nav nav-tabs" id="statutoryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="epf-tab" data-bs-toggle="tab" data-bs-target="#epf" type="button" role="tab" aria-controls="epf" aria-selected="true">EPF</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="esi-tab" data-bs-toggle="tab" data-bs-target="#esi" type="button" role="tab" aria-controls="esi" aria-selected="false">ESI</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button" role="tab" aria-controls="tax" aria-selected="false">Professional Tax</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="welfare-tab" data-bs-toggle="tab" data-bs-target="#welfare" type="button" role="tab" aria-controls="welfare" aria-selected="false">Labour Welfare Fund</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bonus-tab" data-bs-toggle="tab" data-bs-target="#bonus" type="button" role="tab" aria-controls="bonus" aria-selected="false">Statutory Bonus</button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="statutoryTabsContent">
            <!-- EPF Tab -->
            <div class="tab-pane fade show active" id="epf" role="tabpanel" aria-labelledby="epf-tab">
                <div class="row mt-4">
                    <!-- EPF Details -->
                    <div class="col-md-8 bordered-box">
                        <h4 class="section-title">Employees' Provident Fund</h4>
                        <div>
                            <strong>EPF Number</strong>: <span>-</span>
                        </div>
                        <div>
                            <strong>Deduction Cycle</strong>: <span>Monthly</span>
                        </div>
                        <div>
                            <strong>Employee Contribution Rate</strong>: <span>12% of Actual PF Wage</span>
                        </div>
                        <div>
                            <strong>Employer Contribution Rate</strong>: <span>12% of Actual PF Wage <a href="#" class="small-text">(View Splitup)</a></span>
                        </div>
                        <hr>
                        <div>
                            <strong>CTC Inclusions</strong>:
                            <ul class="small-text">
                                <li>&#x2716; Employer's contribution is not included in the CTC.</li>
                                <li>&#x2716; Employer's EDLI contribution is not included in the CTC.</li>
                                <li>&#x2716; Admin charges are not included in the CTC.</li>
                            </ul>
                        </div>
                        <div><strong>Allow Employee level Override</strong>: No</div>
                        <div><strong>Pro-rate Restricted PF Wage</strong>: No</div>
                        <div>
                            <strong>Consider applicable salary components based on LOP</strong>: Yes (when PF wage is less than ₹15,000)
                        </div>
                        <div><strong>Eligible for ABRY Scheme</strong>: No</div>
                        <div class="mt-3">
                            <a href="#" class="disable-link">&#128465; Disable EPF</a>
                        </div>
                    </div>

                    <!-- EPF Sample Calculation -->
                    <div class="col-md-4">
                        <div class="sample-box">
                            <div class="calc-title">Sample EPF Calculation</div>
                            <div class="small-text">
                                Let's assume the PF wage is ₹ 20,000. The breakup of contribution will be:
                            </div>
                            <div class="contribution-section">
                                <div class="row">
                                    <div class="col-8"><strong>Employee's Contribution</strong></div>
                                    <div class="col-4 text-end">₹ 2400</div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-8"><strong>Employer's Contribution</strong></div>
                                    <div class="col-4"></div>
                                </div>
                                <div class="row">
                                    <div class="col-8">EPS (8.33% of 20000)</div>
                                    <div class="col-4 text-end">₹ 1250</div>
                                </div>
                                <div class="row">
                                    <div class="col-8">EPF (12% of 20000 - EPS)</div>
                                    <div class="col-4 text-end">₹ 1150</div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-8"><strong>Total</strong></div>
                                    <div class="col-4 text-end"><strong>₹ 2400</strong></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 small-text text-center">
                            Do you want to preview EPF calculation for multiple cases, based on the preferences you have configured?
                            <div>
                                <a href="#" class="disable-link">🔍 Preview EPF Calculation</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Tabs -->
            <div class="tab-pane fade" id="esi" role="tabpanel" aria-labelledby="esi-tab">
                <div class="bordered-box mt-4">ESI Content Goes Here</div>
            </div>
            <div class="tab-pane fade" id="tax" role="tabpanel" aria-labelledby="tax-tab">
                <div class="bordered-box mt-4">Professional Tax Content Goes Here</div>
            </div>
            <div class="tab-pane fade" id="welfare" role="tabpanel" aria-labelledby="welfare-tab">
                <div class="bordered-box mt-4">Labour Welfare Fund Content Goes Here</div>
            </div>
            <div class="tab-pane fade" id="bonus" role="tabpanel" aria-labelledby="bonus-tab">
                <div class="bordered-box mt-4">Statutory Bonus Content Goes Here</div>
            </div>
        </div>
    </div>
@endsection

