/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    var message_url = "/text/driver";


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

        var type = "POST";
        var driver_id = $('#message_id').val();
        var my_message_url = message_url + '/' + driver_id;

        console.log(formData);

        $.ajax({

            type: type,
            url: my_message_url,
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
                console.log('Error:', data);
                if (data.status == 500) {
                    var newDoc = document.open("text/html", "replace");
                    newDoc.write(data.responseText);
                    newDoc.close();
                } else if (data.status == 403) {
                    window.alert('Permission denied');
                } else if (data.status == 422) {
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