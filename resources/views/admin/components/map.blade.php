<div>
    <label class="form-label mb-0 mt-2" for="{{ $inputId }}">{{ $label }} Address <span class="text-danger">*</span></label>
    <input class="form-control" type="text" id="{{ $inputId }}" name="{{ $name }}"
        value="{{ $inputIdValue }}" placeholder="Search Your Location" >
    <span class="text-danger" id="{{ $inputIdError }}"></span>

    <div class="row">
        <div class="col-4">
            <label class="form-label mb-0 mt-2" for="{{ $longitudeId }}">Longitude <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="{{ $longitudeId }}" name="{{ $longitudeName ?? 'longitude' }}"
                value="{{ $longitudeIdValue }}" placeholder="Longitude" readonly required>
            <span class="text-danger" id="{{ $longitudeErrorId }}"></span>
        </div>
        <div class="col-4">
            <label class="form-label mb-0 mt-2" for="{{ $latitudeId }}">Latitude <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="{{ $latitudeId }}" name="{{ $latitudeName ?? 'latitude' }}"
                value="{{ $latitudeIdValue }}" placeholder="Latitude" readonly required>
            <span class="text-danger" id="{{ $latitudeErrorId }}"></span>
        </div>
        <div class="col-4">
            <label class="form-label mb-0 mt-2" for="{{ $pinCodeId }}">Zip Code <span class="text-danger">*</span></label>
            <input class="form-control" type="text" id="{{ $pinCodeId }}" name="{{ $pinCodeName ?? 'pinCode' }}"
                oninput="validatePositiveNumber(this)" maxlength="6" onkeypress="numericOnly(event)"
                value="{{ $pinCodeValue }}" placeholder="Zip Code" >
            <span class="text-danger" id="{{ $pinCodeErrorId }}"></span>
        </div>
    </div>

    <div class="mt-1" id="{{ $mapId }}" style="height: 400px; width: 100%;"></div>
</div>

<!-- Include Google Maps JavaScript API -->
{{-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script> --}}

<script>
function initializeMap(mapId, inputId, latitudeId, longitudeId, pinCodeId, latitudeValue, longitudeValue, latitudeErrorId = null, longitudeErrorId = null) {
    const defaultLatitude = latitudeValue || 28.6139; // Default to New Delhi
    const defaultLongitude = longitudeValue || 77.2090;
    const defaultLocation = {
        lat: defaultLatitude,
        lng: defaultLongitude,
    };
    const mapElement = document.getElementById(mapId);
    const currentZoom = mapElement.dataset.zoom || 12;

    const map = new google.maps.Map(mapElement, {
        center: defaultLocation,
        zoom: parseInt(currentZoom)
    });

    const input = document.getElementById(inputId);
    const searchBox = new google.maps.places.SearchBox(input);

    map.addListener("bounds_changed", function() {
        searchBox.setBounds(map.getBounds());
    });

    let marker = new google.maps.Marker({
        position: defaultLocation,
        map: map
    });

    searchBox.addListener("places_changed", function() {
        const places = searchBox.getPlaces();
        if (places.length === 0) {
            return;
        }

        const bounds = new google.maps.LatLngBounds();
        places.forEach(function(place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }

            marker.setMap(null); // Remove the previous marker
            marker = new google.maps.Marker({
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

        map.fitBounds(bounds);
        const selectedPlace = places[0];
        if (selectedPlace && selectedPlace.geometry && selectedPlace.geometry.location) {
            const latitude = selectedPlace.geometry.location.lat();
            const longitude = selectedPlace.geometry.location.lng();

            document.getElementById(latitudeId).value = latitude;
            document.getElementById(longitudeId).value = longitude;
            document.getElementById(latitudeErrorId).innerHTML = '';
            document.getElementById(longitudeErrorId).innerHTML = '';
        }
    });

    google.maps.event.addListener(map, 'zoom_changed', function() {
        mapElement.dataset.zoom = map.getZoom();
    });
}

// Initial map load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof google !== 'undefined') {
        initializeMap("{{ $mapId }}", "{{ $inputId }}", "{{ $latitudeId }}", "{{ $longitudeId }}", "{{ $pinCodeId }}", parseFloat(document.getElementById("{{ $latitudeId }}").value), parseFloat(document.getElementById("{{ $longitudeId }}").value), '{{ $latitudeErrorId }}', '{{ $longitudeErrorId }}');
    } else {
        console.error("Google Maps API not loaded. Please check your API key.");
    }
});
</script>
