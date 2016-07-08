/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function ($) {

    setup_message_driver();

    //display modal form for driver editing
    function setup_edit_driver() {
        $('.open-modal-driver').click(function (e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            var driver_id = $(this).val();

            $('#test-message-from-driver-button').show();

            $.get( '/drivers/' + driver_id, function (data) {
                //success data
                console.log(data);
                $('#driver_id').val(data._id);
                $('#first_name').val(data.first_name);
                $('#last_name').val(data.last_name);
                $('#driver_mobile_phone_number').val(data.mobile_phone_number);
                $('#drivers_licence_number').val(data.drivers_licence_number);
                $('#btn-save-driver').val("update");
                $('#driverModal').modal('show');
            }).fail(function(data){
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        });
    }

    setup_edit_driver();


    //display modal form for creating new driver
    $('#btn-add-driver').click(function () {
        $('#btn-save-driver').val("add");
        $('#btn-save-driver').text('Add Driver');
        $('#driverForm').trigger("reset");
        $('#driverModal').modal('show');
    });

    //delete driver and remove it from list

    function setup_delete_driver() {
        $('.delete-driver').click(function (e) {
            e.preventDefault();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            var driver_id = $(this).val();

            $.ajax({

                type: "DELETE",
                url:  '/drivers/' + driver_id,
                success: function (data) {
                    console.log(data);

                    $("#driver" + driver_id).remove();
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        });
    }

    setup_delete_driver();

    //create new driver / update existing driver
    $("#btn-save-driver").click(function (e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });


        var formData = {
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            mobile_phone_number: $('#driver_mobile_phone_number').val(),
            drivers_licence_number: $('#drivers_licence_number').val().toUpperCase()
        };

        //used to determine the http verb to use [add=POST], [update=PUT]
        var state = $('#btn-save-driver').val();

        var type = "POST"; //for creating new resource
        var driver_id = $('#driver_id').val();
        var my_driver_url = '/drivers';

        if (state == "update") {
            type = "PUT"; //for updating existing resource
            my_driver_url += '/' + driver_id;
        }

        console.log(formData);

        $.ajax({

            type: type,
            url: my_driver_url,
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);

                if (state == "add") { //if user added a new record
                    $('#btn-save-driver').text("Save Changes");
                    $('#driver').clone(false).appendTo('#driver_list').attr("id", "driver" + data._id);
                    $("#driver" + data._id + ' button.open-modal-message').val(data._id);
                    $("#driver" + data._id + ' button.open-modal-driver').val(data._id);
                    $("#driver" + data._id + ' button.delete-driver').val(data._id);
                    $("#driver" + data._id).css('display','');
                }
                $("#driver" + data._id + ' .name').text(data.first_name+' '+data.last_name);
                $('#driverForm').trigger("reset");
                $('#driverModal').modal('hide');
                
                setup_edit_driver();
                setup_delete_driver();
                setup_message_driver();
                adjust_fluid_columns();
                setup_sse();

            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $('#driverForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#driverForm').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });

});