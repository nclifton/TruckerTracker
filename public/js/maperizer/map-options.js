(function(window, google, maperizer) {

    maperizer.MAP_OPTIONS = {
        geolocation: false,
        center: {
            lat: -34.1164035,
            lng: 150.6345903
        },
        zoom: 12,
        searchbox: false,
        cluster: true,
        geocoder: true
    }


}(window, google, window.Maperizer || (window.Maperizer = {})));