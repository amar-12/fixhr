@extends('admin.layout.master')
@section('title')
    {{ $pageTitle }}
@endsection

@section('css')
    <!-- Bootstrap Select CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
@endsection

@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div class="mt-5 row g-4">
        <!-- Copy Business Settings Section -->
        <div class="col-xl-12">
            <div class="card custom-card shadow-sm border-0">
                <div class="card-header bg-primary text-white">Copy Settings</div>
                <div class="card-body">
                    <form action="{{ route('organizations.copy-settings-store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="target_business" value="{{ $business->b_id }}">

                        <!-- Source Business Selection -->
                        <div class="mb-3">
                            <label class="form-label">Select Source Business</label>
                            <select class="form-control" data-live-search="true" name="source_business" required>
                                <option value="">-- Select a Business --</option>
                                @foreach ($businesses as $b)
                                    <option value="{{ $b->b_id }}"
                                        {{ old('source_business') == $b->b_id ? 'selected' : '' }}>
                                        {{ $b->b_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Settings to Copy -->
                        <div class="mb-3">
                            <label class="form-label">Settings to Copy</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[]" value="attendance"
                                    checked>
                                <label class="form-check-label">Attendance Settings</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[]" value="payroll" checked>
                                <label class="form-check-label">Payroll Settings</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="settings[]" value="account" checked>
                                <label class="form-check-label">Account Settings</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">Copy Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- jQuery (Ensure this is loaded first) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Bootstrap Select JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

    <!-- Initialize Bootstrap Select -->
    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker(); // Ensure the dropdown is initialized
        });
    </script>
@endsection
