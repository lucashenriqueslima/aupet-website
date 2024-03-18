
function gmaps(mapid) {


    // GMAPS VARS 
    //-----------------------------------------------------------------

    var map;
    var myLatlng = new google.maps.LatLng(-16.6765, -49.2546);
    var zoom = 10;
    var markers = [];
    var marker;
    var callback;
    var listenClick = false;
    var listenZoom = false;
    var sendLatLngBack;
    var sendZoomBack;


    // GMAPS SERVICES VARS
    //-----------------------------------------------------------------

    var directionsRender;
    var directionsService;
    var geocoder;
    var boundsService;



    // GMAPS PUBLIC VARS
    //-----------------------------------------------------------------

    this.map = map;

    // GMAPS PUBLIC FUNCTIONS
    //-----------------------------------------------------------------

    this.drawMap = drawMap;
    this.setZoom = setZoom;
    this.setLatLng = setLatLng;
    this.newRouteServices = newRouteServices;
    this.newGeocoder = newGeocoder;
    this.newBounds = newBounds;
    this.createMarker = createMarker;
    this.clearMarkers = clearMarkers;
    this.changeZoom = changeZoom;
    this.clearRoute = clearRoute;
    this.mapLoaded = mapLoaded;
    this.changeCenter = changeCenter;
    this.listenToClick = listenToClick;
    this.listenToZoom = listenToZoom;
    this.requestGeocode = requestGeocode;


    // GMAPS GETTERS
    //-----------------------------------------------------------------

    function getZoom() {
        return zoom;
    }

    /// GMAPS SETTERS
    //-----------------------------------------------------------------

    function setZoom(value) {
        zoom = parseInt(value);
    }
    function setLatLng(lat, lng) {
        myLatlng = new google.maps.LatLng(lat, lng);
    }



    /// GMAPS SERVICES
    //-----------------------------------------------------------------

    function newRouteServices(clear) {
        directionsService = new google.maps.DirectionsService();
        directionsRender = new google.maps.DirectionsRenderer();
        directionsRender.setMap(map);
        return new requestRoute();
    }

    function newGeocoder() {
        geocoder = new google.maps.Geocoder();
        return new requestGeocode();
    }

    function newBounds() {
        boundsService = new google.maps.LatLngBounds();
        return new requestBounds();
    }


    // GMAPS FUNCTIONS
    //-----------------------------------------------------------------

    function drawMap() {
        var myOptions = {
            scrollwheel: true,
            zoom: zoom,
            center: myLatlng,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            },
            navigationControl: true,
            navigationControlOptions: {
                style: google.maps.NavigationControlStyle.SMALL
            },
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById(mapid), myOptions);

        google.maps.event.addListenerOnce(map, 'idle', function () {
            //loaded fully
            callback();

            if (listenClick) {
                google.maps.event.addListener(map, 'click', function (event) {
                    sendLatLngBack(event.latLng.lat(), event.latLng.lng(), zoom);
                    clearMarkers();
                    createMarker(event.latLng.lat(), event.latLng.lng());
                });
            }

            if (listenZoom) {
                google.maps.event.addListener(map, 'zoom_changed', function () {
                    var zoom = map.getZoom();
                    sendZoomBack(zoom);
                });
            }
        });


        this.map = map;

    }

    function createMarker(lat, lng, label, icon) {
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(lat, lng),
            map: map,
            title: label || "",
            icon: icon || ""
        });
        markers.push(marker);
        return marker;
    }

    function clearMarkers() {
        for (i = 0; i < markers.length; i++) {
            markers[i].setMap(null);
        }
    }

    function changeZoom(zoom) {
        map.setZoom(zoom);
    }

    function changeCenter(lat, lng) {
        map.panTo(new google.maps.LatLng(lat, lng));

    }

    function clearRoute() {
        directionsRender.setMap(null);
    }

    function mapLoaded(func) {
        callback = func;
    }

    function listenToClick(func) {
        listenClick = true;
        sendLatLngBack = func;
    }

    function listenToZoom(func) {
        listenZoom = true;
        sendZoomBack = func;
    }


    // GMAPS OBJECTS
    //-----------------------------------------------------------------

    function requestBounds() {

        this.setBound = setBound;
        this.fitBounds = fitBounds;

        //SETTERS
        function setBound(lat, lng) {
            boundsService.extend(new google.maps.LatLng(lat, lng));
        }

        //FUNCTIONS
        function fitBounds() {
            map.fitBounds(boundsService);
        }

    }

    function requestRoute() {

        var origin = "";
        var originHtml = "";
        var destination = "";
        var destinationHtml = "";
        var travelMode = google.maps.DirectionsTravelMode.DRIVING;
        var describeRoute = false;
        var routeObject = "";

        this.setTravelMode = setTravelMode;
        this.setOrigin = setOrigin;
        this.setHtmlOriginBox = setHtmlOriginBox;
        this.setDestination = setDestination;
        this.setHtmlDestinationBox = setHtmlDestinationBox;
        this.requestRoute = requestRoute;


        //GETTERS

        function getRouteObject() {
            return routeObject;
        }


        //SETTERS

        function setOrigin(lat, lng) {
            origin = new google.maps.LatLng(lat, lng);
        }
        function setHtmlOriginBox(html) {
            originHtml = html;
        }
        function setDestination(lat, lng) {
            destination = new google.maps.LatLng(lat, lng);
        }
        function setHtmlDestinationBox(html) {
            destinationHtml = html;
        }
        function setDescribeRoute() {
            descriveRoute = true;
        }

        function setTravelMode(mode) {

            if (mode == "DRIVING") {
                travelMode = google.maps.DirectionsTravelMode.DRIVING;
            } else if (mode == "BICYCLING") {
                travelMode = google.maps.DirectionsTravelMode.BICYCLING;
            } else if (mode == "TRANSIT") {
                travelMode = google.maps.DirectionsTravelMode.TRANSIT;
            } else if (mode == "WALKING") {
                travelMode = google.maps.DirectionsTravelMode.WALKING;
            }

        }

        //FUNCTIONS

        function requestRoute(callback) {
            if (origin != "" || destination != "") {
                requestRoute = {
                    origin: origin,
                    destination: destination,
                    travelMode: travelMode
                };

                directionsService.route({origin: origin, destination: destination, travelMode: travelMode}, function (response, status) {
                    if (status == google.maps.DirectionsStatus.OK) {

                        if (originHtml != "") {
                            response.routes[0].legs[0].start_address = originHtml;
                        }
                        if (destinationHtml != "") {
                            response.routes[0].legs[0].end_address = destinationHtml;
                        }

                        routeObject = response;
                        directionsRender.setDirections(response);

                        if (describeRoute)
                            describeRouteTable();
                        if ($.isFunction(callback))
                            callback(response);

                    } else {
                        alert("Erro: " + status);
                    }
                });
            } else {
                alert("Erro: Origin or Destination not set");
            }
        }

        function describeRouteTable() {
            $(".rota").show();
            $(".rota .captions").html(response.routes[0].legs[0].distance.text + " - cerca de " + response.routes[0].legs[0].duration.text);
            $(".rota table.origem td:eq(1)").html(response.routes[0].legs[0].start_address);
            $(".rota table.destino td:eq(1)").html(response.routes[0].legs[0].end_address);
            $(".rota table.trajetos").empty();
            if (response.routes[0].legs[0].steps.length > 0) {

                for (i = 0; i < response.routes[0].legs[0].steps.length; i++) {

                    $("<tr><td>" + (i + 1) + ".</td><td>" + response.routes[0].legs[0].steps[i].instructions + "</td><td>" + response.routes[0].legs[0].steps[i].distance.text + "</td></tr>").appendTo(".rota table.trajetos");

                }

            }
        }
    }

    function requestGeocode() {
        var address = "";
        var latLng = "";
        var geoLatLng = "";
        var geoAddress = "";

        this.getGeocodedLatLng = getGeocodedLatLng;
        this.getGeocodedAddress = getGeocodedAddress;
        this.setLatLng = setLatLng;
        this.setAddress = setAddress;
        this.geocodeAddress = geocodeAddress;
        this.geocodeLatLng = geocodeLatLng;

        //GETTERS
        function getGeocodedLatLng() {
            return geoLatLng;
        }
        function getGeocodedAddress() {
            return geoAddress;
        }

        //SETTERS
        function setLatLng(lat, lng) {
            latLng = new google.maps.LatLng(lat, lng);
        }
        function setAddress(value) {
            address = value;
        }

        //FUNCTIONS
        function geocodeAddress(callback) {
            geocoder.geocode({
                address: address
            },   
                    function (results, status) {
                         if (status.toLowerCase() == 'ok') {
                            geoLatLng = {"lat": results[0]['geometry']['location'].lat(), "lng": results[0]['geometry']['location'].lng()};
                            if ($.isFunction(callback))
                                callback(geoLatLng);
                        } else {
                            if ($.isFunction(callback))
                                callback(geoLatLng);
                        }
                    }
            );
        }

        function geocodeLatLng(calback) {
            geocoder.geocode({
                latLng: latLng
            },
                    function (results, status) {
                        if (status.toLowerCase() == 'ok') {
                            geoAddress = results[0]['address_components'];
                            if ($.isFunction(callback))
                                callback(geoAddress);
                        } else {
                            if ($.isFunction(callback))
                                callback(geoAddress);
                        }
                    }
            );
        }
    }
}