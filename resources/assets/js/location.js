/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    setup_select('location');

    //display modal form viewing a location
    function setup_view_location() {

        var locationMap = null;
        var markerArray = [];
        var infoWindowArray = [];
        var currMapCenter;

        $('.open-modal-location-view').click(function () {

            var $location = $('.location_line.selected').first();
            if (!$location.length) {
                return;
            }
            var location_id = $location.attr('data');

            $.get('/vehicle/location/' + location_id, function (loc) {
                //success data
                console.log(loc);

                $('#location_id').val(loc._id);
                $('#datetime').text(loc.vehicle.datetime);
                $('#view_location_vehicle_registration_number').text(loc.vehicle.registration_number);
                $('#view_location_datetime').text(loc.datetime);

                $('#location-viewModal').on('shown.bs.modal', function (e) {
                    $('#location-viewModal').off('shown.bs.modal');

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

                    if (locationMap == null) {
                        $('#map')
                            .gmap3({
                                center: mapCenter,
                                zoom: 15,
                                mapTypeId: google.maps.MapTypeId.ROADMAP,
                                disableDefaultUI: true,
                                zoomControl: true,
                                scaleControl: true,
                                fullscreenControl: true })
                            .then(function(map){
                                locationMap = map;
                            });
                        $(window).on('resize',function(){
                            google.maps.event.trigger(locationMap, 'resize');
                        })

                    } else {
                        $.each(markerArray,function(i,marker){
                            marker.setMap(null);
                            markerArray.splice(i,1);
                        });
                        $.each(infoWindowArray,function(i,infoWindow){
                            infoWindow.setMap(null);
                            infoWindowArray.splice(i,1);
                        });
                    }
                    $('#map').gmap3()
                        .marker(markerOptions)
                        .then(function(marker){
                            markerArray[markerArray.length] = marker;
                        })
                        .infowindow(infoWindowOptions)
                        .then(function(infoWindow){
                            infoWindowArray[infoWindowArray.length] = infoWindow;
                            var map = this.get(0);
                            var marker = this.get(1);
                            marker.addListener('click',function(){
                                infoWindow.open(map,marker);
                            });
                            map.panTo(markerOptions.position);
                            return map;
                        })
                        .on({
                            idle:
                                function (map, e) {
                                    currMapCenter = map.getCenter();
                                },
                            resize:
                                function (map, e){
                                    map.setCenter(currMapCenter);
                                }
                        })

                    ;

                });  // end on show modal

                $('#location-viewModal').modal('show');


            }).fail(function (data) {
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_view_location();


    //delete location/s and remove it/tem from list
    function setup_delete_locations() {
        $('#btn-delete-locations').click(function (e) {
            e.preventDefault();
            delete_selected_location();
        });
    }

    setup_delete_locations();



    //send location request to vehicle
    $("#btn-save-locateVehicle").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var vehicle_id = $('#locateVehicle_id').val();

        $.ajax({

            type: 'POST',
            url: '/vehicle/' + vehicle_id + '/location',
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $('#locationForm').trigger("reset");
                $('#locateVehicleModal').modal('hide');
                $('#location-list-panel').show();

                var loc = $('#location')
                    .clone(false)
                    .appendTo('#location_list')
                    .attr("id", "location" + data._id)
                    .attr("data",data._id);
                loc.find('button.open-modal-view-location').val(data._id);
                loc.find('button.delete-location').val(data._id);
                loc.find('span.registration_number').text(data.vehicle.registration_number);
                loc.find('span.status').text(data.status);
                loc.find('span.status_at').text(data.queued_at);
                loc.show();
                loc[0].scrollIntoView();

                setup_view_location();
                setup_select('location');
                setup_sse();
                adjust_fluid_columns();

            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#location').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });



});