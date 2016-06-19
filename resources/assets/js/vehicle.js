/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    var vehicles_url = "/vehicles";

    $('#registration_number').keyup(function(){
        this.value = this.value.toUpperCase();
    });


    //display modal form for sending locate request message to vehicle
    function setup_locate_vehicle() {
        $('.open-modal-location').click(function () {
            var vehicle_id = $(this).val();
            $('#btn-save-location').val("send");
            $('#vehicle_id').val(vehicle_id);
            $('#locationModal').modal('show');

        });
    }
    setup_locate_vehicle();

    //display modal form for vehicle editing
    function setup_edit_vehicle() {
        $('.open-modal-vehicle').click(function () {
            var vehicle_id = $(this).val();

            $.get(vehicles_url + '/' + vehicle_id, function (data) {
                //success data
                console.log(data);
                $('#vehicle_id').val(data._id);
                $('#registration_number').val(data.registration_number);
                $('#vehicle_mobile_phone_number').val(data.mobile_phone_number);
                $('#tracker_imei_number').val(data.tracker_imei_number);
                $('#btn-save-vehicle').val("update");
                $('#vehicleModal').modal('show');
            }).fail(function(data){
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_edit_vehicle();


    //display modal form for creating new vehicle
    $('#btn-add-vehicle').click(function () {
        $('#btn-save-vehicle').val("add");
        $('#btn-save-vehicle').text("Add Vehicle");
        $('#vehicleForm').trigger("reset");
        $('#vehicleModal').modal('show');
    });

    //delete vehicle and remove it from list

    function setup_delete_vehicle() {
        $('.delete-vehicle').click(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            var vehicle_id = $(this).val();

            $.ajax({

                type: "DELETE",
                url: vehicles_url + '/' + vehicle_id,
                success: function (data) {
                    console.log(data);

                    $("#vehicle" + vehicle_id).remove();
                },
                error: function (data) {
                    console.log('Error:', data);
                    var newDoc = document.open("text/html", "replace");
                    newDoc.write(data.responseText);
                    newDoc.close();
                }
            });
        });
    }

    setup_delete_vehicle();

    //create new vehicle / update existing vehicle
    $("#btn-save-vehicle").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var formData = {
            registration_number: $('#registration_number').val(),
            mobile_phone_number: $('#vehicle_mobile_phone_number').val(),
            tracker_imei_number: $('#tracker_imei_number').val()
        };

        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-vehicle').val();

        var type = "POST"; //for creating new resource
        var vehicle_id = $('#vehicle_id').val();
        var my_vehicle_url = vehicles_url;

        if (state == "update") {
            type = "PUT"; //for updating existing resource
            my_vehicle_url += '/' + vehicle_id;
        }

        console.log(formData);

        $.ajax({

            type: type,
            url: my_vehicle_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                if (state == "add") { //if user added a new record
                    $('#btn-save-vehicle').text("Save Changes");
                    $('#vehicle').clone(false).prependTo('#vehicle_list').attr("id", "vehicle" + data._id);
                    $("#vehicle" + data._id + ' button.open-modal-locate').val(data._id);
                    $("#vehicle" + data._id + ' button.open-modal-vehicle').val(data._id);
                    $("#vehicle" + data._id + ' button.delete-vehicle').val(data._id);
                    $("#vehicle" + data._id + ' #span_registration_number_').attr("id", "span_registration_number_" + data._id);
                    $("#vehicle" + data._id + ' #span_vehicle_mobile_phone_number_').attr("id", "span_vehicle_mobile_phone_number_" + data._id);
                    $("#vehicle" + data._id + ' #span_tracker_imei_number_').attr("id", "span_tracker_imei_number_" + data._id);
                    $("#vehicle" + data._id).css('display', '');
                }
                $("#span_registration_number_" + data._id).text(data.registration_number);
                $("#span_vehicle_mobile_phone_number_" + data._id).text(data.mobile_phone_number);
                $("#span_tracker_imei_number_" + data._id).text(data.tracker_imei_number);


                $('#vehicleForm').trigger("reset");
                $('#vehicleModal').modal('hide');
                setup_locate_vehicle();
                setup_edit_vehicle();
                setup_delete_vehicle();

            },
            error: function (data) {
                handleAjaxError();
                if (data.status == 422) {
                    $('#orgForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#vehicleForm').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });

});