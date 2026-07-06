@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}
@endsection
@section('content')
    {{-- Breadcrumbs Start --}}
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    {{-- Breadcrumbs End --}}


    <div class="container mt-5">
        <h2 class="text-center">Subscribe to Modules & Features</h2>

        {{-- <div class="row">
            @foreach ($modules as $module)
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">{{$module->mdl_name}}</div>
                    <div class="card-body">
                        <p>Select Features:</p>
                        <div class="form-check">
                            <input class="form-check-input feature-checkbox" type="checkbox" data-module="attendance" data-price="5">
                            <label class="form-check-label">Selfie Attendance ($5/employee)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input feature-checkbox" type="checkbox" data-module="attendance" data-price="10">
                            <label class="form-check-label">Face Attendance ($10/employee)</label>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-info text-white">Bill Summary</div>
                    <div class="card-body">
                        <p><strong>Number of Employees:</strong>
                            <span><input type="number" class="form-control" id="employeeCount" min="1" value="1"></span>

                        </p>
                        <p><strong>Selected Modules & Features:</strong></p>
                        <div id="moduleWiseSummary"></div>
                        <p><strong>Total Price:</strong> $<span id="totalPrice">0</span></p>
                        <div class="text-center mt-4">
                            <button class="btn btn-sm btn-danger">Reset</button>
                            <button class="btn btn-sm btn-primary">Subscription</button>
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="row">
            <div class="col-md-9">
                <div class="row">
                    @foreach ($modules as $index => $module)
                        <div class="col-md-4">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">{{$module->mdl_name}}</div>
                                <div class="card-body">
                                    <p>Select Features:</p>
                                    <div class="form-check">
                                        <input class="form-check-input feature-checkbox" type="checkbox" data-module="attendance" data-price="5">
                                        <label class="form-check-label">Selfie Attendance ($5/employee)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input feature-checkbox" type="checkbox" data-module="attendance" data-price="10">
                                        <label class="form-check-label">Face Attendance ($10/employee)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (($index + 1) % 3 == 0)
                            </div><div class="row">
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="col-md-3 end-0">
                <div class="card">
                    <div class="card-header bg-info text-white">Bill Summary</div>
                    <div class="card-body">
                        <p><strong>Number of Employees:</strong>
                            <span><input type="number" class="form-control" id="employeeCount" min="1" value="1"></span>
                        </p>
                        <p><strong>Selected Modules & Features:</strong></p>
                        <div id="moduleWiseSummary"></div>
                        <p><strong>Total Price:</strong> $<span id="totalPrice">0</span></p>
                        <div class="text-center mt-4">
                            <button class="btn btn-sm btn-danger">Reset</button>
                            <button class="btn btn-sm btn-primary">Subscription</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>


@endsection

@section('script')
    {{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script>
        $(document).ready(function () {
            let selectedFeatures = [];
            let employeeCount = 1;

            // Update employee count
            $('#employeeCount').on('input', function () {
                employeeCount = parseInt($(this).val()) || 1;
                updateBillSummary();
            });

            // Handle feature selection
            $('.feature-checkbox').on('change', function () {
                const feature = {
                    id: $(this).attr('id'),
                    module: $(this).data('module'),
                    name: $(this).next('label').text(),
                    price: $(this).data('price'),
                    isChecked: $(this).is(':checked')
                };

                if (feature.isChecked) {
                    selectedFeatures.push(feature);
                } else {
                    selectedFeatures = selectedFeatures.filter(f => f.id !== feature.id);
                }

                updateBillSummary();
            });

            // Update bill summary
            function updateBillSummary() {
                const moduleWiseSummary = $('#moduleWiseSummary');
                moduleWiseSummary.empty();

                // Group features by module
                const groupedFeatures = selectedFeatures.reduce((acc, feature) => {
                    if (!acc[feature.module]) {
                        acc[feature.module] = [];
                    }
                    acc[feature.module].push(feature);
                    return acc;
                }, {});

                // Display module-wise summary
                for (const module in groupedFeatures) {
                    const features = groupedFeatures[module];
                    let moduleSubtotal = 0;

                    const moduleSummary = $('<div>').addClass('mb-3');
                    moduleSummary.append($('<h5>').text(`${module.charAt(0).toUpperCase() + module.slice(1)} Module`));

                    const featureList = $('<ul>');
                    features.forEach(feature => {
                        const featurePrice = feature.price * employeeCount;
                        moduleSubtotal += featurePrice;
                        featureList.append($('<li>').text(`${feature.name} - $${featurePrice}`));
                    });

                    moduleSummary.append(featureList);
                    moduleSummary.append($('<p>').text(`Subtotal: $${moduleSubtotal}`));
                    moduleWiseSummary.append(moduleSummary);
                }

                // Calculate total price
                const totalPrice = selectedFeatures.reduce((sum, feature) => sum + feature.price * employeeCount, 0);
                $('#totalPrice').text(totalPrice);
            }

            // Handle subscribe button click
            $('.subscribe-btn').on('click', function () {
                const module = $(this).data('module');
                alert(`Subscribed to ${module} module!`);
            });
        });
    </script>

@endsection
