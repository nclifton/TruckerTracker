/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    if (truckertracker.channel){
        truckertracker.socket.on(truckertracker.channel + ':\\TruckerTracker\\Events\\LocationUpdate', function(data){
            var loc = $('#location' + data._id)
            loc.find('button.open-modal-view-location').val(data._id);
            loc.find('button.delete-location').val(data._id);
            loc.find('span.registration_number').text(data.vehicle.registration_number);
            loc.find('span.sent_at').text(data.sent_at);
            loc.find('span.status').text(data.status);
            loc.show();
        });
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
                loc.find('span.registration_number').text(data.vehicle.registration_number);
                loc.find('span.sent_at').text(data.queued_at);
                if (data.sent_at)
                    loc.find('span.sent_at').text(data.sent_at);
                loc.find('span.status').text(data.status);
                loc.show();

                setup_view_location();
                setup_delete_location();

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