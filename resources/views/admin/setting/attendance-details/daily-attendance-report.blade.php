@extends('admin.layout.master')
@section('title') Attendance Report @endsection

@section('css')
    <style>
        h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #007bff;
        }
        #loader {
            display: none;
            margin-left: 15px;
        }
    </style>
@endsection

@section('content')
    <div>
        <div class="p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/report/attendance-report') }}">Report</a></li>
                <li class="active"><span><b>Selfie Attendance Report</b></span></li>
            </ol>
        </div>

        <div class="row pt-5">
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <span class="navbar-brand fw-bold me-5">
                        <span class="h3">Daily Attendance</span>
                    </span>

                    <div class="collapse navbar-collapse">
                        <div class="row w-100 align-items-center">
                            <!-- Date Input -->
                            <div class="col-md-4">
                                <label for="dateFilter" class="form-label">Select Date</label>
                                <input type="date" id="dateFilter" name="date" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>

                            <!-- Export Button -->
                            <div class="col-md-4 mt-4">
                                <button type="button" id="exportBtn" class="btn btn-outline-success">
                                    Export
                                </button>
                                <div id="loader" class="mt-2">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Exporting...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
@endsection

@section('script')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    document.getElementById('exportBtn').addEventListener('click', function (e) {
        e.preventDefault();

        const date = document.getElementById('dateFilter').value;
        const exportBtn = document.getElementById('exportBtn');
        const loader = document.getElementById('loader');

        if (!date) {
            Swal.fire('Please select a date!');
            return;
        }

        loader.style.display = 'inline-block';
        exportBtn.disabled = true;

        const formData = new FormData();
        formData.append('date', date);

        fetch("{{ route('daily.attendance.report') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error downloading the file.');
            return response.blob();
        })
        .then(blob => {
            if (blob.size === 0) {
                Swal.fire('No data found for selected date!', '', 'info');
                return;
            }

            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'selfie-attendance-report-' + date + '.xlsx';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => {
            console.error('Download error:', error);
            Swal.fire('Error', error.message, 'error');
        })
        .finally(() => {
            loader.style.display = 'none';
            exportBtn.disabled = false;
        });
    });
</script>
@endsection

