/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    function update_location_line(data) {
        var loc = $('#location' + data._id);
        var description = data.vehicle.registration_number + ' ' + data.status;
        switch (data.status) {
            case 'queued':
                description += ' '+data.queued_at;
                break;
            case 'sent':
                description += ' '+data.sent_at;
                break;
            case 'delivered':
                description += ' '+data.delivered_at;
                break;
            case 'received':
                description += ' '+data.received_at;
                loc.find('button.open-modal-location-view').val(data._id);
                loc.find('button.open-modal-location-view').show();
                break;
        }
        loc.find('span.description').text(description);
        adjust_fluid_columns();
    }
    function setup_subscribe_location(){
        if (!sse){
            var sse = $.SSE('/sub/locations'+organisation_id, {
                onOpen: function(e) {
                    console.log("SSE Open");
                    console.log(e);
                },
                onEnd: function(e) {
                    console.log("SSE Closed");
                    console.log(e);
                },
                onError: function(e) {
                    console.log("SSE Error");
                    console.log(e);
                },
                onMessage: function(e){
                    console.log("SSE Message");
                    console.log(e);
                },
                events: {
                    LocationUpdate: function(e) {
                        console.log(e);
                        update_location_line($.parseJSON(e.data));
                    },
                    LocationReceived: function(e) {
                        console.log(e);
                        update_location_line($.parseJSON(e.data));
                    }
                }
            });
            sse.start();

        }
    }
    if (subscribe_sse){
        setup_subscribe_location();
    }

    //display modal form viewing a location
    function setup_view_location() {
        $('.open-modal-location-view').click(function () {
            var location_id = $(this).val();

            $.get('/vehicle/location/' + location_id, function (data) {
                //success data
                console.log(data);

                $('#location_id').val(data._id);
                $('#datetime').text(data.vehicle.datetime);
                $('#view_location_vehicle_registration_number').text(data.vehicle.registration_number);
                $('#view_location_datetime').text(data.datetime);

                $('#location-viewModal').on('shown.bs.modal', function (e) {
                    // map stuff here after the modal is finished forming
                    var location = [data.latitude, data.longitude];
                    var latLng = new google.maps.LatLng(data.latitude, data.longitude);
                    $('#map')
                        .gmap3({
                            center: latLng,
                            zoom: 10,
                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                            disableDefaultUI: true,
                            zoomControl: true,
                            scaleControl: true,
                            fullscreenControl: true
                        })
                        .marker({
                            position: location,
                            icon: {
                                path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                                scale: 3
                            },
                            title: data.vehicle.registration_number + ' '+ data.course + '° at ' + data.speed + ' km/h'
                        });
                });

                $('#location-viewModal').modal('show');


            }).fail(function (data) {
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_view_location();

    //delete location and remove it from list
    function setup_delete_location() {
        $('button.delete-location').click(function (e) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            e.preventDefault();

            var location_id = $(this).val();

            $.ajax({

                type: "DELETE",
                url: '/vehicle/location/' + location_id,
                success: function (data) {
                    console.log(data);
                    $("#location" + location_id).remove();
                },
                error: function (data) {
                    handleAjaxError(data);
                }
            });
        });
    }

    setup_delete_location();

    //send location request to vehicle
    $("#btn-save-location").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var vehicle_id = $('#location_vehicle_id').val();

        $.ajax({

            type: 'POST',
            url: '/vehicle/' + vehicle_id + '/location',
            dataType: 'json',
            success: function (data) {
                console.log(data);

                $('#locationForm').trigger("reset");
                $('#locationModal').modal('hide');
                $('#location-list-panel').show();

                var loc = $('#location').clone(false).appendTo('#location_list').attr("id", "location" + data._id);
                loc.find('button.open-modal-view-location').val(data._id);
                loc.find('button.delete-location').val(data._id);
                loc.find('span.description').text(data.vehicle.registration_number+' '+data.status+' '+data.queued_at);
                loc.show();

                setup_view_location();
                setup_delete_location();
                setup_subscribe_location();
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