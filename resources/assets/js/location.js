/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {


    //display modal form viewing a location
    function setup_view_location() {
        $('.open-modal-location-view').click(function () {
            var location_id = $(this).val();

            $.get('/vehicle/location/' + location_id, function (data) {
                //success data
                console.log(data);

                $('#location_id').val(data._id);
                $('#datetime').text(data.vehicle.datetime);
                $('#registration_number').text(data.vehicle.registration_number);
                $('#location-viewModal').modal('show');
                
                // map stuff here
                var $maperizer = $('#map-canvas').maperizer(Maperizer.MAP_OPTIONS);
                $maperizer.maperizer('addFocusedMarker', {
                    lat: data.latitude,
                    lng: data.longitude
                });

            }).fail(function(data){
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_view_location();

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
            url: '/vehicle/'+vehicle_id+'/location',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                
                $('#locationForm').trigger("reset");
                $('#locationModal').modal('hide');
                $('#location-list-panel').show();

                var msg;
                msg = $('#location').clone(false).prependTo('#location_list').attr("id", "location" + data._id);
                msg.find('button.open-modal-view-location').val(data._id);
                msg.find('button.delete-location').val(data._id);
                msg.find('span.registration_number').text(data.vehicle.registration_number);
                msg.find('span.location-sent_at').text(data.sent_at);
                msg.find('span.location-status').text(data.status);
                msg.show();

                setup_view_location();

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