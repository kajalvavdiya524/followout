<script>
    var map;
    var marker;

    var latInput = $('#lat');
    var lngInput = $('#lng');

    var currentLocation = {
        lat: parseFloat(latInput.val()),
        lng: parseFloat(lngInput.val()),
    }

    var initalZoom = 14;

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: initalZoom,
            center: currentLocation,
        });

        marker = new google.maps.Marker({
            position: currentLocation,
            map: map,
        });
    }
</script>
