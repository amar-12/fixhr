@extends('admin.layout.master')

@section('title', 'Cities')

@section('css')
    <style>
        .rotate {
            transition: 500ms;
            transform: rotate(90deg);
            /* Adjust the desired rotation value */
        }

        .bg-inf {
            /* background-color: #a3d5dd; */
            /* Change to your desired color */
        }

        .star-dot {
            color: red;
        }
    </style>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "city-table-dynamic",
                url: "{{ route('travel.cities.list') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });
        });
    </script>
@endsection

<style>
    .disable-alt{
        background-color: #eee !important;
        pointer-events: none !important;
    }

    /* Set the map's size */
    #map {
        height: 400px;
        width: 100%;
    }

    /* Adjust the search input style */
    #searchInput {
        width: 100%;
        margin-bottom: 10px;
    }

    #editAddressNameId {
        width: 100%;
        margin-bottom: 10px;

    }

    #mapeditload {
        height: 400px;
        width: 100%;

    }

    .pac-container {
        z-index: 10000 !important;
        /* Set a high z-index for the autocomplete dropdown */
    }
</style>

@section('content')
    {{-- Bradcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                <li class="active"><span><b>Metro Cities Settings</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                    <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <a id="addNewCity" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#cityName">Add City</a>                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Bradcrumbs End --}}




    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header d-flex">
                    <h4 class="card-title"><span>Cities</span></h4>
                    {{-- <div class="ms-auto">
                        <button class="btn text-white btn-info btn-sm" ><i
                            class="fe fe-plus bold" id="addCitiesFieldBtn"></i></button>
                    </div> --}}
                </div>

                <div class="card-body">
                    @csrf
                  <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                        data-length>
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-sm-2">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>

                            <div class="col-sm-7">
                            </div>


                            <div class="col-sm-1"
                                style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                                <div class="form-group dropdown">
                                    <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        <i class="fa fa-download me-2"></i> Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                        <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                    </ul>
                                </div>
                            </div>



                            <style>
                                .export-button {
                                    display: flex;
                                    align-items: center;
                                    gap: 6px;
                                    background-color: white;
                                    border: 1px solid #ddd;
                                    border-radius: 999px;
                                    padding: 8px 14px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                    transition: background-color 0.2s ease, box-shadow 0.2s ease;
                                }

                                .export-button:hover {
                                    background-color: #f1f1f1;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .dropdown-menu-export {
                                    font-size: 14px;
                                    min-width: 140px;
                                }

                                .dropdown-menu-export .dropdown-item:hover {
                                    background-color: #f8f9fa;
                                }

                                .custom-button {
                                    display: flex;
                                    align-items: center;
                                    gap: 6px;
                                    background-color: white;
                                    border: 1px solid #ddd;
                                    border-radius: 999px;
                                    padding: 8px 14px;
                                    font-size: 14px;
                                    cursor: pointer;
                                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                    transition: background-color 0.2s ease, box-shadow 0.2s ease;
                                }

                                .custom-button:hover {
                                    background-color: #f1f1f1;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .custom-button svg {
                                    width: 16px;
                                    height: 16px;
                                }
                            </style>

                        </div>

                    <div class="table-responsive">
                        <table class="table display table-hover  table-vcenter text-wrap border-bottom" id="city-table-dynamic">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
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

                {{-- <div class="d-flex justify-content-end">
                    <div class="d-flex">
                        <button type="submit" class="btn btn-outline-primary " id="saveUptBtn">Save & Update</button>
                    </div>
                </div> --}}

            </div>
        </div>
    </div>

    <div class="modal fade" id="editCityName" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">Update City Settings</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="updateCityFormId" action="{{ route('add.city') }}"> @csrf
                    <div class="modal-body">
                        <div class="col-lg">
                            <input type="text" id="editCityId" name="editCityId" hidden>

                            <label class="form-label mb-0 mt-2 ">City Address <span class="text-red">*</span></label>

                            <input class="form-control " id="editAddressNameId" type="text" placeholder="Address Name" name="location" required>
                            <span class="text-danger" id="ulocation-name"></span>

                            <div class="row">
                                <div class="col-6">
                                    <input class="form-control" type="text" id="longituder2" name="longitude"
                                        placeholder="Longitude" readonly>
                                    <span class="text-danger" id="ulongitude-name"></span>

                                </div>
                                <div class="col-6">
                                    <input class="form-control" type="text" id="latituder2" name="latitude"
                                        placeholder="Latitude" readonly>
                                    <span class="text-danger" id="ulatitude-name"></span>

                                </div>
                            </div>

                            <div class="m-1" id="mapeditload"></div>
                            <p class="mb-0 pb-0 text-muted fs-10 mt-5 ">By continuing you agree to <a href="#"
                                class="text-primary">Terms & Conditions</a></p>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <a class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</a>
                        <button type="submit" class="btn btn-outline-primary savebtn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cityName" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2">City Settings</h4><button aria-label="Close" class="btn-close"
                        data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form id="addCityFormId" action="{{ route('add.city') }}" onsubmit="return validateForm()">
                    <div class="modal-body">
                        <div class="col-lg">
                            <p class="form-label mb-0 mt-2">City Address <span class="text-red">*</span></p>

                            <input class="form-control" type="text" id="searchInput" name="location" placeholder="Search Your location" required>
                            <span class="text-danger" id="location-name"></span>

                            <div class="row">
                                <div class="col-6">
                                    <input class="form-control" type="text" id="longituder1" name="longitude"
                                        value="" placeholder="Longitude" readonly required>
                                    <span class="text-danger" id="longitude-name"></span>
                                </div>
                                <div class="col-6">
                                    <input class="form-control" type="text" id="latituder1" name="latitude"
                                        value="" placeholder="Latitude" readonly required>
                                    <span class="text-danger" id="latitude-name"></span>
                                </div>
                            </div>
                            <!-- Display the map -->
                            <div class="m-1" id="map"></div>

                            <p class="mb-0 pb-0 text-muted fs-10 mt-5 ">By continuing you agree to <a href="#"
                                class="text-primary">Terms & Conditions</a></p>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        @csrf
                        <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- modal for delete confirmation --}}
    <div>
        <div class="modal fade" id="cityDeletebtn" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Confirm Deletion</h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                                aria-hidden="true">&times;</span></button>
                    </div>
                    <form action="{{ route('delete.city') }}" method="POST"> @csrf
                        <input type="text" id="city_id" name="city_id" hidden>
                        <div class="modal-body text-center">
                            <h4 class="mt-5">Are you sure want to delete, <span class="text-primary"
                                    id="assign_city">{{-- $item->city_id ?? 0 --}}</span> city ?</h4>
                        </div>
                        <div class="modal-footer">
                            <a class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</a>
                            <button type="submit" class="btn btn-outline-danger " id="">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validateForm() {
            // Get the latitude and longitude values
            var latitude = document.getElementById('latituder1').value;
            var longitude = document.getElementById('longituder1').value;

            // Check if either latitude or longitude is empty
            if (latitude === '' || longitude === '') {
                alert('Please select a location on the map.');
                return false; // Prevent form submission
            }

            // If both latitude and longitude have values, allow the form submission
            return true;
        }
    </script>

    <script>
        let map;
        let editMap;
        let longitudeEdit;
        let latitudeEdit;
        let addressEdit;

        function ItemDeleteModel(context) {
            var id = $(context).data('city_id');
            var name = $(context).data('city_address');
            $('#city_id').val(id);
            $('#assign_city').text(name);
        }

        function openEditCity(context) {
            var id = $(context).data('id');
            var address = $(context).data('address');
            var longitude = $(context).data('longitude');
            var latitude = $(context).data('latitude');

            $('#editCityId').val(id);
            // $('#editCityNameId').val(city_name);
            // $('#editCityEmailId').val(city_email);
            $('#editAddressNameId').val(address);
            $('#longituder2').val(longitude);
            $('#latituder2').val(latitude);

            addressEdit = address;
            longitudeEdit = longitude;
            latitudeEdit = latitude;
        }


        function initMap() {
            // Create a map centered on a default location (you can change this)
            const defaultLocation = {
                lat: 28.6139,
                lng: 77.2090
            };

            // Initialize the map
            map = new google.maps.Map(document.getElementById("map"), {
                center: defaultLocation,
                zoom: 12 // Set the initial zoom level
            });

            // Create a search box and link it to the UI element
            const input = document.getElementById("searchInput");
            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards current map's viewport
            map.addListener("bounds_changed", function() {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction and retrieve more details
            searchBox.addListener("places_changed", function() {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the location and display it on the map
                const bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        // console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create a marker for each place
                    const marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location
                    });

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                // Fit the map to the bounds of the places found
                map.fitBounds(bounds);
                const selectedPlace = places[0]; // Assuming you are interested in the first place
                if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
                    const latitude = selectedPlace.geometry.location.lat();
                    const longitude = selectedPlace.geometry.location.lng();
                    document.getElementById('longituder1').value = longitude;
                    document.getElementById('latituder1').value = latitude;
                    // LoadAuto(latitude,longitude);
                }
            });
        }

        const mapModal = document.getElementById('cityName');
        mapModal.addEventListener('shown.bs.modal', function() {
            // Call the initMap function when the modal is fully visible
            initMap();
        });

        // only use edit set

        const mapModalEdit = document.getElementById('editCityName');
        mapModalEdit.addEventListener('shown.bs.modal', function() {

            // Initialize map after modal is shown
            const defaultLocation = {
                lat: latitudeEdit,
                lng: longitudeEdit
            };

            // Initialize the map
            const map = new google.maps.Map(document.getElementById("mapeditload"), {
                center: defaultLocation,
                zoom: 12 // Set the initial zoom level
            });

            // Create a search box and link it to the UI element
            const input = document.getElementById("editAddressNameId");
            const searchBox = new google.maps.places.SearchBox(input);

            // Bias the SearchBox results towards the map's viewport
            map.addListener("bounds_changed", function() {
                searchBox.setBounds(map.getBounds());
            });

            // Listen for the event fired when the user selects a prediction and retrieve more details
            searchBox.addListener("places_changed", function() {
                const places = searchBox.getPlaces();

                if (places.length === 0) {
                    return;
                }

                // For each place, get the location and display it on the map
                const bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry) {
                        console.log("Returned place contains no geometry");
                        return;
                    }

                    // Create a marker for each place
                    const marker = new google.maps.Marker({
                        map,
                        title: place.name,
                        position: place.geometry.location
                    });

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });

                // Fit the map to the bounds of the places found
                map.fitBounds(bounds);

                const selectedPlace = places[0]; // Assuming you are interested in the first place
                if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
                    const latitude = selectedPlace.geometry.location.lat();
                    const longitude = selectedPlace.geometry.location.lng();

                    // Update input fields with the selected location
                    document.getElementById('longituder2').value = longitude;
                    document.getElementById('latituder2').value = latitude;
                }
            });

            // currentEdit time value getset
            if (navigator.Geo - Location) {
                navigator.Geo - Location.getCurrentPosition(
                    function(position) {
                        const userLocation = {
                            lat: latitudeEdit,
                            lng: longitudeEdit
                        };

                        // Place a marker at the user's location
                        const marker = new google.maps.Marker({
                            position: userLocation,
                            map: map,
                            title: addressEdit
                        });

                        // Set map center to user's location
                        map.setCenter(userLocation);
                    },
                    // function() {
                    //     handleLocationError(true, map.getCenter());
                    // }
                );
            } else {
                // Browser doesn't support Geo-Location
                // handleLocationError(false, map.getCenter());
            }

            google.maps.event.addDomListener(window, 'load');
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> <!-- Load the Google Maps JavaScript API with your API key -->
    <script src="https://maps.googleapis.com/maps/api/js?key={{config('credentials')['MAP_API_KEY']}}&libraries=places&callback=initMap" async defer></script>

    <script>
        $('#addCityAddressId').on('input', function() {
            $('#location-name').html('');
        });

        $('#editAddressNameId').on('input', function() {
            $('#ulocation-name').html('');
        });

        document.getElementById('addNewCity').addEventListener('click', function() {
            document.getElementById('addCityFormId').reset();
            $('#location-name').html('');
            $('#longitude-name').html('');
            $('#latitude-name').html('');
        });

        $('#addCityFormId').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr("action");
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    // alert('Form submitted successfully');
                    if (response.success) {
                        $('#cityName').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.error,
                            timer: 3000,
                        });
                    }
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    if (errors.location) {
                        $('#location-name').text(errors.location[0]);
                    }
                    if (errors.longitude) {
                        $('#longitude-name').text(errors.longitude[0]);
                    }
                    if (errors.longitude) {
                        $('#latitude-name').text(errors.longitude[0]);
                    }
                }
            });
        });

        $('#updateCityFormId').submit(function(e) {
            e.preventDefault();
            var url = $(this).attr("action");
            let formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    if (response.success) {
                        $('#editCityName').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.error,
                            timer: 3000,
                        });
                    }
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    if (errors.address) {
                        $('#ulocation-name').text(errors.address[0]);
                    }
                    if (errors.longitude) {
                        $('#ulongitude-name').text(errors.longitude[0]);
                    }
                    if (errors.longitude) {
                        $('#ulatitude-name').text(errors.longitude[0]);
                    }
                }
            });
        });
    </script>
@endsection
