@extends('superadmin.layout.master')

@yield('css')
<style>
    .page-title {
        font-size: 30px !important;
        font-weight: bolder !important;
    }

    .two {
        margin-top: 15px;
    }
</style>

@section('content')
{{-- @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session()->get('success') }}
    </div>
@endif --}}

    <div class="page-leftheader">
        <div class="page-title">Welcome Back <span class="font-weight-normal text-muted ms-2"><b>Super Admin</b></span>
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <div class="mt-0 text-start"> <span class="fs-14 font-weight-semibold">TOTAL
                                            BUSSINNESSES</span>
                                        <h3 class="mb-0 mt-1 mb-2">120</h3>

                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="icon1 bg-success my-auto  float-end"> <i
                                            class="feather feather-users two"></i> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <div class="mt-0 text-start"> <span class="fs-14 font-weight-semibold">ACTIVE
                                            MODULES</span>
                                        <h3 class="mb-0 mt-1 mb-2">124</h3>

                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="icon1 bg-primary my-auto  float-end"> <i
                                            class="fa-solid fa-layer-group two"></i> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <div class="mt-0 text-start"> <span class="fs-14 font-weight-semibold">REVENVUE (THIS
                                            MONTH)</span>
                                        <h3 class="mb-0 mt-1  mb-2">$200</h3>
                                    </div>

                                </div>
                                <div class="col-4">
                                    <div class="icon1 bg-secondary brround my-auto  float-end"> <i
                                            class="feather feather-dollar-sign two"></i> </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-12">
                    <div class="card p-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-8">
                                    <div class="mt-0 text-start"> <span class="fs-14 font-weight-semibold">BLOCKED
                                            BUSSINESSES</span>
                                        <h3 class="mb-0 mt-1  mb-2">4</h3>
                                    </div>

                                </div>
                                <div class="col-4">
                                    <div class="icon1 bg-secondary brround my-auto  float-end"> <i
                                            class="fa-solid fa-ban two"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card">
                <div class="card-header border-0 responsive-header">
                    <h4 class="card-title">Monthly Revenue</h4>
                </div>
                <div class="card-body">
                    <canvas id="chartLine"></canvas>
                </div>
            </div>
        </div>


        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card">
                <div class="card-header border-0">
                    <h3 class="card-title">Latest Bussinesss</h3>
                </div>
                <div class="table-responsive attendance_table mt-4">
                    <table class="table mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th class="text-center">S.No</th>
                                <th class="text-start">Bussiness</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-center"><span class="avatar avatar-sm brround">1</span></td>
                                <td class="font-weight-semibold fs-14">FixHR Tech</td>
                                <td class="text-center"><span class="badge bg-success-transparent">Active</span></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-center"><span class="avatar avatar-sm brround">2</span></td>
                                <td class="font-weight-semibold fs-14">SmartPeople Co</td>
                                <td class="text-center"><span class="badge bg-success-transparent">Active</span></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-center"><span class="avatar avatar-sm brround">3</span></td>
                                <td class="font-weight-semibold fs-14">Beta Soft</td>
                                <td class="text-center"><span class="badge bg-danger-transparent">Active</span></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-center"><span class="avatar avatar-sm brround">4</span></td>
                                <td class="font-weight-semibold fs-14">AI Systems</td>
                                <td class="text-center"><span class="badge bg-success-transparent">Active</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom-0">
                <h3 class="card-title">Module-wise Revenue </h3>
            </div>
            <div class="table-responsive attendance_table mt-4">
                <table class="table mb-0 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center">S.No</th>
                            <th class="text-start">Module</th>
                            <th class="text-center">Active Bussiness</th>
                            <th class="text-center">Mothly Revenue</th>
                            <th class="text-center">Yearly Revenue</th>

                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-bottom">
                            <td class="text-center"><span class="avatar avatar-sm brround">1</span></td>
                            <td class="font-weight-semibold fs-14">Attandance</td>
                            <td class="text-center"><span>80</span></td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">32,000</span>
                            </td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">3,84,000</span>
                            </td>

                        </tr>
                        <tr class="border-bottom">
                            <td class="text-center"><span class="avatar avatar-sm brround">2</span></td>
                            <td class="font-weight-semibold fs-14">Payroll</td>
                            <td class="text-center"><span>60</span></td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">27,000</span>
                            </td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">2,24,000</span>
                            </td>

                        </tr>
                        <tr class="border-bottom">
                            <td class="text-center"><span class="avatar avatar-sm brround">3</span></td>
                            <td class="font-weight-semibold fs-14">Leave Mgmt</td>
                            <td class="text-center"><span>50</span></td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">22,000</span>
                            </td>
                            <td class="text-center">
                                <i class="fa-solid fa-indian-rupee-sign"></i>
                                <span style="font-weight: normal;">2,64,000</span>
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>






@section('script')
<script>
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6'
        });
    @endif
</script>

@endsection


