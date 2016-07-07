
/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    // open some pop up type box for viewing and contributing to the conversation this message is a part of.
    function setup_view_conversation() {
        // TODO setup the view button click action
    }



    setup_delete_message();

    //send message to driver
    $("#btn-save-message").click(function (e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });

        e.preventDefault();

        var formData = {
            message_text: $('#message_text').val()
        };

        var driver_id = $('#message_id').val();

        console.log(formData);

        $.ajax({

            type: "POST",
            url: '/driver/' + driver_id + '/message/',
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);
                
                $('#messageForm').trigger("reset");
                $('#messageModal').modal('hide');
                $('#message-list-panel').show();
                
                add_message_line(data);
                setup_delete_message();
                setup_view_conversation();
                setup_sse();
                adjust_fluid_columns();

            },
            error: function (data) {
                handleAjaxError(data);
                if (data.status == 422) {
                    $('#messageForm span.help-block').remove();
                    $.each(data.responseJSON, function (index, value) {
                        var input = $('#messageForm').find('[name="' + index + '"]');
                        input.after('<span class="help-block"><strong>' + value + '</strong></span>');
                        input.closest('div.form-group').addClass('has-error');
                    });
                }
            }
        });
    });

});