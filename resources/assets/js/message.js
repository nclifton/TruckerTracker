/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

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

                var msg;
                msg = $('#message').clone(false).prependTo('#message_list').attr("id", "message" + data._id);
                msg.find('button.open-modal-view-message').val(data._id);
                msg.find('button.delete-message').val(data._id);
                msg.find('span.first_name').text(data.driver.first_name);
                msg.find('span.last_name').text(data.driver.last_name);
                msg.find('span.message-sent_at').text(data.sent_at);
                msg.find('span.message-status').text(data.status);
                msg.show();

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