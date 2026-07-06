@extends('admin.layout.master')
@section('title')
    TA & DA Settings
@endsection

@section('content')
    <div>

        {{-- Be like water. --}}
        <div class=" p-0 my-3">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('/admin/settings/tada-settings') }}">Settings </a></li>
                <li class="active"><span><b>TA & DA Settings</b></span></li>
            </ol>
        </div>
        <div class="">
            <p class="text-muted">Create and Update Your TA & DA Settings</p>
        </div>
        <div class="row row-sm">

            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-map-o"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/travel-types') }}">
                                        <h5 class="my-auto text-dark"> Travel Type</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/travel-types') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-file-text-o"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/policy-category') }}">
                                        <h5 class="my-auto text-dark">Policy Category</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/policy-category') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="ion-plane"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/travel-modes') }}">
                                        <h5 class="my-auto text-dark">Travel Mode</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/travel-modes') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-car"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/travel-vehicle') }}">
                                        <h5 class="my-auto text-dark">Travel Vehicle & Allowance</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/travel-vehicle') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="fa fa-clipboard"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="#">
                                    <h5 class="my-auto text-dark">Expense Policy Settings</h5>
                                </a>
                                <p class="my-auto">

                                </p>
                            </div>
                            <div class="my-auto"> <a
                                    href="{{ url('admin/settings/tada-settings/travel_expense_policy') }}"><i
                                        class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary">
                                    <i class="ti-wallet "></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/daily-allowance') }}">
                                        <h5 class="my-auto text-dark">Daily Allowance</h5>
                                    </a>
                                    <p class="my-auto"></p>
                                </div>
                                <div class="my-auto">
                                    <a href="{{ url('admin/settings/tada-settings/daily-allowance') }}">
                                        <i class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="ti-wallet"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/lodging') }}">
                                        <h5 class="my-auto text-dark">Lodging</h5>
                                    </a>
                                    <p class="my-auto">
                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/lodging') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="ti-wallet"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/lodging') }}">
                                    <h5 class="my-auto text-dark">Daily Allowance & Lodging</h5>
                                </a>
                                <p class="my-auto">
                                </p>
                            </div>
                            <div class="my-auto"> <a href="{{ url('admin/settings/tada-settings/lodging') }}"><i
                                        class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div> --}}


            {{-- <div class="col-xl-6">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="fa fa-cc"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto"><a href="{{route('travel-allowance.index')}}">
                                    <h5 class="my-auto text-dark">Travel Allowance</h5>
                                </a>
                                <p class="my-auto">

                                </p>
                            </div>
                            <div class="my-auto"> <a
                                href="{{route('travel-allowance.index')}}"><i
                                    class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-building"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ route('travel.cities.list') }}">
                                        <h5 class="my-auto text-dark">Metro Cities</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a href="{{ route('travel.cities.list') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-xl-6" >
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                    class="mdi mdi-av-timer"></i></span>
                        </div>
                        <div class="col-10 d-flex justify-content-between">
                            <div class="my-auto">
                                <a href="#">
                                    <h5 class="my-auto text-dark">DA Policy Settings</h5>
                                </a>
                                <p class="my-auto">

                                </p>
                            </div>
                            <div class="my-auto">
                                <a href="{{ url('admin/settings/tada-settings/da_policy') }}">
                                    <i
                                        class="fa fa-angle-double-right fs-20 my-auto">
                                    </i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-star-o"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a
                                        href="{{ url('admin/settings/tada-settings/get-travel-purpose') }}">
                                        <h5 class="my-auto text-dark">Travel Purpose</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a
                                        href="{{ url('admin/settings/tada-settings/get-travel-purpose') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-book"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto"><a href="{{ url('admin/settings/tada-settings/expense-setting') }}">
                                        <h5 class="my-auto text-dark">Expense Settings</h5>
                                    </a>
                                    <p class="my-auto">

                                    </p>
                                </div>
                                <div class="my-auto"> <a
                                        href="{{ url('admin/settings/tada-settings/expense-setting') }}"><i
                                            class="fa fa-angle-double-right fs-20 my-auto"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-2 my-auto">
                                <span class="settings-icon bg-primary-transparent text-primary border-primary"><i
                                        class="fa fa-book"></i></span>
                            </div>
                            <div class="col-10 d-flex justify-content-between">
                                <div class="my-auto">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#paymentModeModal">
                                        <h5 class="my-auto text-dark">Reimburse Payment Mode</h5>
                                    </a>

                                </div>

                                <div class="my-auto">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#paymentModeModal">
                                        <i class="fa fa-angle-double-right fs-20 my-auto"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Payment Mode Modal -->
            <div class="modal fade" id="paymentModeModal" tabindex="-1" aria-labelledby="paymentModeLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-3 shadow">

                        <!-- Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="paymentModeLabel">Reimburse Payment Mode</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <div class="d-flex flex-column gap-3">

                                <!-- Bank Transfer -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" id="bank"
                                        value="Bank Transfer"
                                        {{ isset($paymentMode) && $paymentMode->pm_mode == 'Bank Transfer' ? 'checked' : '' }}
                                        required>
                                    <label class="form-check-label" for="bank">
                                        Bank Transfer
                                    </label>
                                </div>

                                <!-- Cash -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" id="cash"
                                        value="Cash"
                                        {{ isset($paymentMode) && $paymentMode->pm_mode == 'Cash' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cash">
                                        Cash
                                    </label>
                                </div>

                                <!-- Cheque -->
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_mode" id="cheque"
                                        value="Cheque"
                                        {{ isset($paymentMode) && $paymentMode->pm_mode == 'Cheque' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cheque">
                                        Cheque
                                    </label>
                                </div>

                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-success" id="savePaymentMode">Save</button>
                        </div>
                    </div>
                </div>
            </div>


            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                let paymentModeId = "{{ $paymentMode->pm_id ?? '' }}";
            </script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const saveBtn = document.getElementById("savePaymentMode");

                    // Backend se agar record ka ID mila hai to use karo
                    let paymentModeId = "{{ $paymentMode->id ?? '' }}";
                    let businessId = "{{ $business->id ?? '' }}"; // business id bhi pass karna hoga

                    saveBtn.addEventListener("click", function() {
                        const selected = document.querySelector('input[name="payment_mode"]:checked');

                        if (!selected) {
                            Swal.fire({
                                icon: "warning",
                                title: "Oops...",
                                text: "Please select a payment mode!",
                                confirmButtonColor: "#3085d6"
                            });
                            return;
                        }

                        const paymentMode = selected.value;
                        let method = paymentModeId ? "PUT" : "POST";

                        fetch("{{ route('payment-mode.save') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    payment_mode: paymentMode
                                })
                            })

                            .then(res => res.json())
                            .then(data => {
                                if (data.status) {
                                    // agar naya record bana to uski id save karo future ke liye
                                    if (data.id) {
                                        paymentModeId = data.id;
                                    }

                                    Swal.fire({
                                        icon: "success",
                                        title: "Success!",
                                        text: data.message,
                                        confirmButtonColor: "#3085d6"
                                    }).then(() => {
                                        const modal = bootstrap.Modal.getInstance(
                                            document.getElementById('paymentModeModal')
                                        );
                                        modal.hide();
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: data.message || "Something went wrong!",
                                        confirmButtonColor: "#d33"
                                    });
                                }
                            })
                            .catch(err => {
                                console.error("Error:", err);
                                Swal.fire({
                                    icon: "error",
                                    title: "Server Error",
                                    text: "Please try again later.",
                                    confirmButtonColor: "#d33"
                                });
                            });
                    });
                });
            </script>




        </div>
    </div>
@endsection
