<!DOCTYPE html>
<head>
  <title>Pusher Test</title>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <script>

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('20dfa57ed89b1c7fe1a1', {
      cluster: 'ap2'
    });

    var channel = pusher.subscribe('my-channel');
    channel.bind('my-event', function(data) {
      alert(JSON.stringify(data));
    });
  </script>
</head>
<body>
  <h1>Pusher Test</h1>
  <p>
    Try publishing an event to channel <code>my-channel</code>
    with event name <code>my-event</code>.
  </p>
</body>

{{-- @extends('admin.layout.master')
@section('title')
    Branch Settings
@endsection

@section('css')
    <style>
        .rotate {
            transition: 500ms;
            transform: rotate(90deg);
        }

        .star-dot {
            color: red;
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

        .pac-container {
            z-index: 10000 !important;
        }
    </style>
@endsection

@section('script')
<script>
    let map;
    let markers = [];

    function initMap() {
        // Default location (example: New Delhi)
        const defaultLocation = { lat: 28.6139, lng: 77.2090 };

        // Initialize the map
        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLocation,
            zoom: 12
        });

        // Create the search box and link it to the UI element
        const input = document.getElementById("searchInput");
        const searchBox = new google.maps.places.SearchBox(input);

        // Bias the SearchBox results towards current map's viewport
        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });

        // Event listener for when the user selects a place
        searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();

            if (places.length === 0) {
                return;
            }

            // Clear out the old markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];

            // Get the first place from the places array
            const place = places[0];

            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }

            // Set the marker using google.maps.Marker
            const position = place.geometry.location;
            const marker = new google.maps.Marker({
                map: map,
                position: position, // The position (lat/lng)
                title: place.name // The title to display when hovering
            });

            markers.push(marker);

            // Set the map's view to include the place's location
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(position);
                map.setZoom(14);  // Adjust zoom level as needed
            }

            // Update latitude and longitude fields
            const latitude = position.lat();
            const longitude = position.lng();
            document.getElementById('longituder1').value = longitude;
            document.getElementById('latituder1').value = latitude;
            document.getElementById('longitude-name').innerHTML = '';
            document.getElementById('latitude-name').innerHTML = '';
        });
    }
</script>




@endsection

@section('content')
    <div class="card">
        <div class="card-header"></div>
        <div class="card-body">
            <div class="col-lg">
                <p class="mb-0 pb-0 text-dark fs-13 mt-1">Branch Address <span class="star-dot">*</span></p>
                <input class="form-control" type="text" id="searchInput" name="location" placeholder="Search Your location" required>
                <span class="text-danger" id="location-name"></span>

                <div class="row">
                    <div class="col-6">
                        <input class="form-control" type="text" id="longituder1" name="longitude" value="" placeholder="Longitude" readonly required>
                        <span class="text-danger" id="longitude-name"></span>
                    </div>
                    <div class="col-6">
                        <input class="form-control" type="text" id="latituder1" name="latitude" value="" placeholder="Latitude" readonly required>
                        <span class="text-danger" id="latitude-name"></span>
                    </div>
                </div>
                 Display the map
                <div class="m-1" id="map"></div>

                <p class="mb-0 pb-0 text-muted fs-12 mt-5">By continuing you agree to <a href="#" class="text-primary">Terms & Conditions</a></p>
            </div>
        </div>
        <div class="card-footer"></div>
    </div>
@endsection --}}
