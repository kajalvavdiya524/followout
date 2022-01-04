<script>
    var map;
    var marker;
    var geocoder;
    var autocomplete;
    var useCountrySelect = $('#country_id').length;
    var mapUseAutocomplete = false;

    var latInput = $('#lat');
    var lngInput = $('#lng');

    var currentLocation = {
        lat: parseFloat(latInput.val()),
        lng: parseFloat(lngInput.val()),
    }

    var initalZoom = parseInt(latInput.val(), 10) !== 0 && parseInt(lngInput.val(), 10) !== 0 ? 14 : 1;

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        }
    }

    function showPosition(position) {
        var coords = position.coords;

        currentLocation.lat = coords.latitude;
        currentLocation.lng = coords.longitude;

        latInput.attr('value', coords.latitude);
        lngInput.attr('value', coords.longitude);

        moveMarker(coords.latitude, coords.longitude);

        geocode(coords.latitude, coords.longitude);

        map.setZoom(16);
    }

    function handleMarkerChange(e) {
        var lat = e.latLng.lat();
        var lng = e.latLng.lng();

        moveMarker(lat, lng);

        latInput.val(lat);
        lngInput.val(lng);

        geocode(lat, lng);
    }

    function initMap() {
        if ($("#location").length) {
            mapUseAutocomplete = true;
        }

        map = new google.maps.Map(document.getElementById('map'), {
            zoom: initalZoom,
            center: currentLocation,
        });

        marker = new google.maps.Marker({
            position: currentLocation,
            map: map,
            draggable: true,
        });

        geocoder = new google.maps.Geocoder();

        if (mapUseAutocomplete) {
            enableAutocomplete();
        }

        map.addListener('click', handleMarkerChange);
        marker.addListener('dragend', handleMarkerChange);

        function enableAutocomplete() {
            var input = document.getElementById('location');

            autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.bindTo('bounds', map);

            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                setPlace(autocomplete.getPlace());
            });
        }

        function disableAutocomplete() {
            autocomplete.unbindAll();
        }
    }

    function moveMarker(lat, lng) {
        var latlng = new google.maps.LatLng(lat, lng);
        marker.setPosition(latlng);
        map.panTo(latlng);
    }

    function setPlace(place, fromMarker) {
        var city = '';
        var state = '';
        var zipcode = '';
        var address = '';
        var countryName = '';
        var countryCode = '';

        for (var i = 0 ; i < place.address_components.length; i++) {
            if (place.address_components[i].types[0] === "street_number") {
                address = place.address_components[i].long_name;
            }

            if (place.address_components[i].types[0] === "route") {
                address = address+' '+place.address_components[i].long_name;
            }

            if (place.address_components[i].types[0] === "locality") {
                city = place.address_components[i].long_name;
            }

            if (place.address_components[i].types[0] === "administrative_area_level_1") {
                state = place.address_components[i].long_name;
            }

            if (place.address_components[i].types[0] === "country") {
                countryName = place.address_components[i].long_name;
                countryCode = place.address_components[i].short_name;
            }

            if (place.address_components[i].types[0] === "postal_code") {
                zipcode = place.address_components[i].long_name;
            }
        }

        var latitude = place.geometry.location.lat();
        var longitude = place.geometry.location.lng();

        if (fromMarker !== true) {
            moveMarker(latitude, longitude);
        }

        if (map.getZoom() < 6) {
            map.setZoom(6);
        }

        $('#lat').val(latitude);
        $('#lng').val(longitude);
        $('#city').val(city);
        $('#state').val(state);
        $('#zip_code').val(zipcode);
        $('#address').val(address);

        if (useCountrySelect) {
            var countrySelect = $('#country_id')[0].selectize;
            var options = countrySelect.options;

            if (countryCode.length) {
                var option = _.find(options, { 'code': countryCode });

                if (option !== undefined) {
                    countrySelect.setValue(option.value, false);
                }
            }
        }
    }

    function geocode(lat, lng) {
        geocoder.geocode({'latLng': new google.maps.LatLng(lat, lng)}, function(results, status) {
            if (useCountrySelect) {
                $('#country_id')[0].selectize.clear(false);
            }

            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    setPlace(results[0], true);

                    if (mapUseAutocomplete) {
                        $("#location").val('');
                    }
                }
            }
        });
    }
</script>
