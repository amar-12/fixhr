<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Employee Profile - {{ $employee['name'] ?? 'Employee' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a1a2e;
            background: #ffffff;
            padding: 20px;
        }

        .page-wrapper {
            background: #ffffff;
            border-radius: 10px;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            max-width: 750px;
            margin: 0 auto;
        }

        /* ── HEADER BANNER ── */
        .header-banner {
            background: #0f172a;
            border-radius: 10px 10px 0 0;
            padding: 22px 24px;
            display: table;
            width: 100%;
        }

        .header-avatar-cell {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }

        .avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #3b82f6;
            background: #1e293b;
            display: inline-block;
        }

        .header-info-cell {
            display: table-cell;
            vertical-align: middle;
            padding-left: 14px;
        }

        .emp-name {
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .emp-designation {
            font-size: 10px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .status-badge {
            display: inline-block;
            margin-top: 7px;
            background: #10b981;
            color: #ffffff;
            font-size: 8px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .header-code-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .emp-code-label {
            font-size: 8px;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .emp-code-value {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 1px;
        }

        /* ── BODY CONTENT ── */
        .content-area {
            padding: 16px 0px 10px;
        }

        /* ── THREE COLUMN GRID ── */
        .three-col-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 8px;
        }

        .info-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            vertical-align: top;
            width: 33.33%;
        }

        .card-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        /* ── FIELD ROWS ── */
        .field-row {
            margin-bottom: 9px;
        }

        .field-label {
            font-size: 8px;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .field-value {
            font-size: 10px;
            font-weight: 600;
            color: #0f172a;
        }

        /* ── FULL WIDTH CARD ── */
        .full-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
        }

        .full-card .card-title {
            margin-bottom: 10px;
        }

        .two-col-table {
            width: 100%;
            border-collapse: collapse;
        }

        .two-col-table td {
            width: 50%;
            vertical-align: top;
            padding-right: 16px;
        }

        /* ── POLICY + MILESTONES ROW ── */
        .bottom-two-col {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 8px;
        }

        .bottom-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            vertical-align: top;
        }

        .milestones-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .milestones-inner td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }
    </style>
</head>

<body>
    <div class="header-banner">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:70px; vertical-align:middle;">
                    <img src={{ $employee->emp_profile_s3_url ?? 'N/A' }} alt="Profile Image"
                        style="width:60px; height:60px; border-radius:50%; object-fit:cover;">
                </td>

                <td style="vertical-align:middle; padding-left:14px;">
                    <div class="emp-name">
                        {{ $employee->emp_full_name ?? $employee->emp_fname . ' ' . $employee->emp_lname }}</div>
                    <div class="emp-designation">{{ $employee->fh_designation->dg_name ?? 'N/A' }} |
                        {{ $employee->fh_department->d_name ?? 'N/A' }}</div>
                    <div class="status-badge">
                        {{ $employee->emp_status == 71 ? 'Active Employee' : 'Inactive Employee' }}</div>
                </td>
                <td style="vertical-align:middle; text-align:right;">
                    <div class="emp-code-label">Employee Code</div>
                    <div class="emp-code-value">{{ $employee->emp_code ?? 'N/A' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-area">
        <table class="three-col-table">
            <tr>

                <!-- Employment Information -->
                <td class="info-card">
                    <div class="card-title">Employment Information</div>

                    <div class="field-row">
                        <div class="field-label">Date of Joining</div>
                        <div class="field-value">
                            {{ $employee->emp_date_of_joining ? \Carbon\Carbon::parse($employee->emp_date_of_joining)->format('d-M-Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Employee Type</div>
                        <div class="field-value">{{ $employee->fh_employee_type->m_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Grade</div>
                        <div class="field-value">{{ $employee->fh_grade->g_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Branch</div>
                        <div class="field-value">{{ $employee->fh_branch->br_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Reporting Manager</div>
                        <div class="field-value">
                            {{ $employee->fh_reporting_manager_id->emp_full_name ?? 'Not Assigned' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Probation Period</div>
                        <div class="field-value">{{ $employee->emp_probation_period ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Work Mode</div>
                        <div class="field-value">{{ $employee->fh_work_mode->m_name ?? 'N/A' }}</div>
                    </div>
                </td>

                <!-- Personal Details -->
                <td class="info-card">
                    <div class="card-title">Personal Details</div>

                    <div class="field-row">
                        <div class="field-label">Date of Birth</div>
                        <div class="field-value">
                            {{ $employee->emp_dob ? \Carbon\Carbon::parse($employee->emp_dob)->format('d-M-Y') : 'N/A' }}
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Gender / Marital Status</div>
                        <div class="field-value">
                            {{ $employee->fh_gender->m_name ?? 'N/A' }} /
                            {{ $employee->emp_marital_status_id ? 'Married' : 'Unmarried' }}
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Blood Group</div>
                        <div class="field-value">{{ $employee->fh_blood_group->m_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Nationality</div>
                        <div class="field-value">{{ $employee->emp_nationality ?? 'N/A' }} </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Personal Mobile</div>
                        <div class="field-value">{{ $employee->emp_phone ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Personal Email</div>
                        <div class="field-value">{{ $employee->emp_email ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Emergency Contact</div>
                        <div class="field-value">{{ $employee->emp_emergency_contact ?? 'N/A' }}</div>
                    </div>
                </td>

                <!-- Statutory & Bank -->
                <td class="info-card">
                    <div class="card-title">Statutory &amp; Bank</div>

                    <div class="field-row">
                        <div class="field-label">Bank Name</div>
                        <div class="field-value">{{ $employee->emp_bank_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Account No</div>
                        <div class="field-value">{{ $employee->emp_bank_account_no ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">IFSC Code</div>
                        <div class="field-value">{{ $employee->emp_bank_ifsc_code ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">PAN Number</div>
                        <div class="field-value">{{ $employee->emp_pan_number ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Aadhar Number</div>
                        <div class="field-value">{{ $employee->emp_aadhar_number ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">PF UAN No</div>
                        <div class="field-value">{{ $employee->emp_pf_no ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">ESIC No</div>
                        <div class="field-value">{{ $employee->emp_esic_no ?? 'N/A' }}</div>
                    </div>
                </td>

            </tr>
        </table>

        <!-- Addresses -->
        <table class="three-col-table">
            <tr>
                <td class="info-card">
                    <div class="card-title">Permanent Address</div>
                    <div class="field-row">
                        <div class="field-value">
                            {{ $employee->emp_permanent_address ?? 'N/A' }}<br>
                            Pin Code: {{ $employee->emp_permanent_pin_code ?? 'N/A' }}
                        </div>
                    </div>
                </td>

                <td class="info-card">
                    <div class="card-title">Temporary Address</div>
                    <div class="field-row">
                        <div class="field-value">
                            {{ $employee->emp_temporary_address ?? 'Same as Permanent' }}<br>
                            Pin Code: {{ $employee->emp_temporary_pin_code ?? 'N/A' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bottom Section -->
        <table class="bottom-two-col">
            <tr>
                <td class="bottom-card" style="width:38%;">
                    <div class="card-title">Policy &amp; System</div>

                    <div class="field-row">
                        <div class="field-label">Shift Timing</div>
                        <div class="field-value">{{ $employee->fh_shift_type->m_name ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Geofencing</div>
                        <div class="field-value">{{ $employee->emp_is_geofencing_active ? 'Active' : 'Inactive' }}
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">IMEI Mapping</div>
                        <div class="field-value">{{ $employee->emp_imei_no ?? 'N/A' }}</div>
                    </div>
                    <div class="field-row">
                        <div class="field-label">Wifi Restricted</div>
                        <div class="field-value">{{ $employee->emp_is_wifi_restricted ? 'Yes' : 'No' }}</div>
                    </div>
                </td>

                <td class="bottom-card" style="width:62%;">
                    <div class="card-title">Other Professional Milestones</div>
                    <table class="milestones-inner">
                        <tr>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">Expected Confirmation</div>
                                    <div class="field-value">
                                        {{ $employee->emp_date_of_confirmation ? \Carbon\Carbon::parse($employee->emp_date_of_confirmation)->format('d-M-Y') : 'N/A' }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">Gratuity Eligibility</div>
                                    <div class="field-value">
                                        {{ $employee->emp_date_of_gratuity ? \Carbon\Carbon::parse($employee->emp_date_of_gratuity)->format('d-M-Y') : 'N/A' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">Notice Period</div>
                                    <div class="field-value">
                                        {{ $employee->emp_notice_period_day_for_employee ?? 'N/A' }} Days</div>
                                </div>
                            </td>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">Retirement Date</div>
                                    <div class="field-value">
                                        {{ $employee->emp_retirement_date ? \Carbon\Carbon::parse($employee->emp_retirement_date)->format('d-M-Y') : 'N/A' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">SAP Budget Code</div>
                                    <div class="field-value">{{ $employee->emp_sap_budget_code ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td>
                                <div class="field-row">
                                    <div class="field-label">Last Updated</div>
                                    <div class="field-value">
                                        {{ $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->format('d-M-Y') : 'N/A' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
