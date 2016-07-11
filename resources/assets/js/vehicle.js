/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    $('#registration_number').keyup(function(){
        this.value = this.value.toUpperCase();
    });

    setup_select('vehicle',true);

    //display modal form for sending locate request message to vehicle
    function setup_locate_vehicle() {
        $('.open-modal-location').click(function (e) {
            e.preventDefault();
            var vehicle_id = $('.vehicle_line.selected').attr('data');
            if (vehicle_id) {
                $('#btn-save-locateVehicle').val("send");
                $('#locateVehicle_id').val(vehicle_id);
                $('#locateVehicleModal').modal('show');
            }
        });
    }
    setup_locate_vehicle();

    //display modal form for vehicle editing
    function setup_edit_vehicle() {
        $('.open-modal-vehicle').click(function (e) {
            e.preventDefault();
            var vehicle_id = $('.vehicle_line.selected').attr('data');
            if (vehicle_id) {

                $.get( '/vehicles/' + vehicle_id, function (data) {
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
            }
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
        $('.delete-vehicle').click(function (e) {
            e.preventDefault();
            var vehicle_id = $('.vehicle_line.selected').attr('data');
            if (vehicle_id) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });
                $.ajax({

                    type: "DELETE",
                    url: '/vehicles/' + vehicle_id,
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
            }
        });
    }

    setup_delete_vehicle();

    function setup_reset_vehicle_form(){
        $('#vehicleForm').on("reset",function(){
            var $form = $('#vehicleForm');
            $form.find('.help-block').remove();
            $form.find('.form-group').removeClass('has-error');
        });
    }
    setup_reset_vehicle_form();

    //create new vehicle / update existing vehicle
    $("#btn-save-vehicle").click(function (e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        var formData = {
            registration_number: $('#registration_number').val(),
            mobile_phone_number: $('#vehicle_mobile_phone_number').val(),
            tracker_imei_number: $('#tracker_imei_number').val()
        };

        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-vehicle').val();

        var type = "POST"; //for creating new resource
        var vehicle_id = $('#vehicle_id').val();
        var vehicle_url = '/vehicles';

        if (state == "update") {
            type = "PUT"; //for updating existing resource
            vehicle_url += '/' + vehicle_id;
        }

        console.log(formData);

        $.ajax({

            type: type,
            url: vehicle_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                if (state == "add") { //if user added a new record
                    $('#btn-save-vehicle').text("Save Changes");
                    $('#vehicle')
                        .clone(false)
                        .appendTo('#vehicle_list')
                        .attr("id", "vehicle" + data._id)
                        .attr("data",data._id)
                        .show();
                }
                $("#vehicle" + data._id + " .registration_number").text(data.registration_number);

                $('#vehicleForm').trigger("reset");
                $('#vehicleModal').modal('hide');

                setup_select('vehicle',true);
                adjust_fluid_columns();
                setup_sse();

            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $('#vehicleForm span.help-block').remove();
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