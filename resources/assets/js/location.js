/**
 * Created by nclifton on 29/05/2016.
 */



var LocationDialogue = {

    settings: {
        classPrefix:    'location',
        url:            '/vehicle/location',
        lineSelector:   '.location_line.selected',

        selectors: {
            viewButton:                 '#btn-view-locations',
            deleteButton:               '#btn-delete-locations',
            registrationNumberHeading:  '#view_location_vehicle_registration_number',
            datetimeHeading:            '#view_location_datetime',
            modal:                      '#location-viewModal',
            mapContainer:               '#map',
            lineTemplate:               '#location',
            list:                       '#location_list'
        },
        locate: {
            selectors: {
                form:                   '#locateVehicleForm',
                submitButton:           '#btn-save-locateVehicle',
                dataIdHolder:           '#locateVehicle_id',
                modal:                  '#locateVehicleModal',
            }
        },
        locationMap: null,
        markerArray: [],
        infoWindowArray: [],
        currMapCenter: null

    },

    init: function () {
        this.settings = Common.findElements(this.settings);
        this.onUIActions(this.settings);
        return this;
    },

    showLocationMap: function (loc, settings) {
        // map stuff here after the modal is finished forming
        var mapCenter = {lat: loc.latitude, lng: loc.longitude};

        var markerOptions = {
            position: mapCenter,
            tag: loc._id,
            title: loc.vehicle.registration_number,
            icon: {
                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                scale: 3
            }
        };

        var infoWindowOptions = {
            position: mapCenter,
            tag: loc._id,
            content: loc.vehicle.registration_number + ' '+ loc.course + '° at ' + loc.speed + ' km/h'
        };

        if (settings.locationMap == null) {
            settings.mapContainer
                .gmap3({
                    center: mapCenter,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    disableDefaultUI: true,
                    zoomControl: true,
                    scaleControl: true,
                    fullscreenControl: true })
                .then(function(map){
                    settings.locationMap = map;
                });
            $(window).on('resize',function(){
                google.maps.event.trigger(settings.locationMap, 'resize');
            })

        } else {
            $.each(settings.markerArray,function(i,marker){
                marker.setMap(null);
                settings.markerArray.splice(i,1);
            });
            $.each(settings.infoWindowArray,function(i,infoWindow){
                infoWindow.setMap(null);
                settings.infoWindowArray.splice(i,1);
            });
        }
        var mapMarker;
        settings.mapContainer
            .gmap3()
            .marker(markerOptions)
            .then(function(marker){
                mapMarker = marker;
                settings.markerArray[settings.markerArray.length] = marker;
            })
            .infowindow(infoWindowOptions)
            .then(function(infoWindow){
                settings.infoWindowArray[settings.infoWindowArray.length] = infoWindow;
                var map = settings.locationMap;
                google.maps.event.addListener(mapMarker,'click',function(){
                    infoWindow.open(map,mapMarker);
                });
                map.panTo(markerOptions.position);
                return map;
            })
            .on({
                idle:
                    function (map, e) {
                        settings.currMapCenter = map.getCenter();
                    },
                resize:
                    function (map, e){
                        map.setCenter(settings.currMapCenter);
                    }
            });
    },

    showViewLocation: function (loc, settings) {
        console.log(loc);
        settings.registrationNumberHeading.text(loc.vehicle.registration_number);
        settings.datetimeHeading
            .text((new Date(loc[loc.status + '_at']))
                .toLocaleString(Common.settings.default_locale_code,
                    {hour12: Common.settings.time_format_hour12}));
        settings.modal.on('shown.bs.modal', function (e) {

            settings.modal.off('shown.bs.modal');
            LocationDialogue.showLocationMap(loc, settings);

        });
        settings.modal.modal('show');
    },

    getViewLocation: function (settings) {

        $(settings.lineSelector).first().each(function (i, selected) {
            var dataId = $(selected).attr('data');
            $.get(settings.url + '/' + dataId, function (loc) {
                LocationDialogue.showViewLocation(loc, settings);

            }).fail(function (data) {
                Common.handleServerError(data);
            });

        });

    },

    setLineText: function (line, data) {
        line.find('span.registration_number').text(data.vehicle.registration_number);
        line.find('span.status').text(data.status);
        line.find('span.status_at').text(data[data.status + '_at']);
    },

    locateRequestSuccess: function (settings, data) {
        settings.locate.form.trigger("reset");
        settings.locate.modal.modal('hide');
        Common.prependListLine(data, settings, LocationDialogue.setLineText);
    },

    submitLocationRequest: function (settings) {
        Common.ajaxSetup();
        var vehicle_id = settings.locate.dataIdHolder.val();
        $.ajax({
            type: 'POST',
            url: '/vehicle/' + vehicle_id + '/location',
            dataType: 'json',
            success: function (data) {
                LocationDialogue.locateRequestSuccess(settings, data);
            },
            error: function (data) {
                Common.handleAjaxError(data,settings);
            }
        });

    },

    updateLocationLine: function (data) {
        var settings = LocationDialogue.settings;
        var line = $(settings.lineTemplate.selector + data._id);
        line.find('span.status').text(data.status);
        line.find('span.status_at').text(data[data.status + '_at']);
    },

    onUIActions: function(settings){

        Common.setupSelect(settings.classPrefix,true);

        settings.viewButton.click(function (e) {
            e.preventDefault();
            LocationDialogue.getViewLocation(settings);
        });

        settings.deleteButton.click(function (e) {
            e.preventDefault();
            Common.deleteSelection(settings);
        });

        settings.locate.submitButton.click(function (e) {
            e.preventDefault();
            LocationDialogue.submitLocationRequest(settings);

        });

    }

};

