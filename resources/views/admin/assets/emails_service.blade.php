<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Asset Service Request</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f5f6fa; padding: 20px;">

    <table
        style="max-width: 800px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0;">
        <thead>
            <tr>
                <th colspan="4"
                    style="background-color: #4a90e2; color: #ffffff; padding: 20px; text-align: center; font-size: 20px;">
                    Asset Service Request
                </th>
            </tr>
        </thead>

        <tbody>
            @if ($service_type == 'Return')
                {{-- Only Asset Tag --}}
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold; width: 30%;">
                        Asset Tag
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                        {{ $service->asset_tag }}
                    </td>
                </tr>
            @else
                {{-- Full Details --}}
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold; width: 20%;">Asset Tag
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; width: 30%;">{{ $service->asset_tag }}</td>

                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold; width: 20%;">Service Type
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; width: 30%;">{{ $service->service_type }}
                    </td>
                </tr>

                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Service Location</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                        {{ optional($service->fh_branch)->br_name ?? 'Locale' }}
                    </td>

                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Vendor Name</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->vendor_name ?? 'N/A' }}</td>
                </tr>

                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Service Cost</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->service_cost ?? 'N/A' }}</td>

                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Service Start Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->service_start_date ?? 'N/A' }}
                    </td>
                </tr>

                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Service End Date</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->service_end_date ?? 'N/A' }}
                    </td>

                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Issue Description</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->issue_description ?? 'N/A' }}
                    </td>
                </tr>

                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Courier Name</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->courier_name ?? 'N/A' }}</td>

                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Docket No</td>
                    <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">{{ $service->docket_no ?? 'N/A' }}</td>
                </tr>

                @if ($service->service_file)
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: bold;">Attached Asset</td>
                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0;">
                            <a href="{{ asset($service->service_file) }}"
                                style="color: #4a90e2; text-decoration: none;">Download</a>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                @endif
            @endif

            <tr>
                <td colspan="4" style="padding: 20px; text-align: center; font-size: 13px; color: #555555;">
                    Regards,<br><strong>Asset Management System</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <hr style="max-width: 800px; margin: 20px auto; border: none; border-top: 2px solid #4a90e2;">

</body>

</html>
