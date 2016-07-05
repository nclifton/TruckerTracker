
/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    // open some pop up type box for viewing and contributing to the conversation this message is a part of.
    function setup_view_conversation() {
        // TODO setup the view button click action
    }

    function add_message_line(data) {
        var msg = $('#message').clone(false).prependTo('#message_list').attr("id", "message" + data._id);
        msg.find('button.open-modal-view-message').val(data._id);
        msg.find('button.delete-message').val(data._id);
        msg.find('span.first_name').text(data.driver.first_name);
        msg.find('span.last_name').text(data.driver.last_name);
        msg.find('span.status').text(data.status);
        var status_at = data.queued_at;
        switch (data.status) {
            case 'sent':
                status_at = data.sent_at;
                break;
            case 'delivered':
                status_at = data.delivered_at;
                break;
            case 'received':
                status_at = data.received_at;
                break;
        }
        msg.find('span.status_at').text(status_at);
        msg.attr('title', data.message_text);
        msg.show();
    }

    function update_message_line(data) {
        var msg = $('#message' + data._id);
        msg.find('span.status').text(data.status);
        var status_at = data.queued_at;
        switch (data.status) {
            case 'sent':
                status_at = data.sent_at;
                break;
            case 'delivered':
                status_at = data.delivered_at;
                break;
        }
        msg.find('span.status_at').text(status_at);
        adjust_fluid_columns();
    }

    function setup_subscribe_message(){
        if (!sse){
            var sse = $.SSE('/sub/messages'+organisation_id, {
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
                    MessageUpdate: function(e) {
                        console.log(e);
                        update_message_line($.parseJSON(e.data));
                    },
                    MessageReceived: function(e) {
                        console.log(e);
                        add_message_line($.parseJSON(e.data));
                    }
                }
            });
            sse.start();

        }
    }
    if (subscribe_sse){
        setup_subscribe_message();
    }
    //delete message and remove it from list
    function setup_delete_message() {
        $('button.delete-message').click(function (e) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            e.preventDefault();

            var message_id = $(this).val();

            $.ajax({

                type: "DELETE",
                url: '/driver/message/' + message_id,
                success: function (data) {
                    console.log(data);
                    $("#message" + message_id).remove();
                },
                error: function (data) {
                    handleAjaxError(data);
                }
            });
        });
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