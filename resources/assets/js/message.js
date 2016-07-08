
/**
 * Created by nclifton on 29/05/2016.
 */
$(document).ready(function () {

    setup_message_driver();
    
    function setup_view_conversation() {

        
        // This is shown on the message modal used to send messages
        // we'll populate the conversation panel when the message modal is opened.
        $('#messageDriverModal').on('shown.bs.modal', function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });

            var driver_id = $('#messageDriver_id').val();

            $.get('/driver/' + driver_id + '/conversation', function(data) {
                //success data
                console.log(data);

                var $conversationContainer = $('#driver_conversation');
                var $messagesContainer = $conversationContainer.find('.messages_container');

                $conversationContainer.find('.message_to_panel .header_text')
                    .empty()
                    .text(data.first_name + ' ' + data.last_name);

                $messagesContainer.children(':visible').remove();
                var pane = $conversationContainer.find('.conversation_panel');
                pane.jScrollPane(jScrollPaneSettings);
                var api = pane.data('jsp');
                   
                $.each(data.messages, function(){
                    var msgdata = this;
                    add_message_to_conversation($messagesContainer, msgdata);
                    api.reinitialise();
                });
                
                api.reinitialise();
                api.scrollTo(0, 9999);

            }).fail(function(data){
                var newDoc = document.open("text/html", "replace");
                newDoc.write(data.responseText);
                newDoc.close();
            });
        })
    }

    setup_view_conversation();

    setup_delete_message();

    var add_message_to_conversation_display = function (data) {

        if ($('#messageDriverModal:visible').length){
            var $conversationContainer = $('#driver_conversation');
            var $messagesContainer = $conversationContainer.find('.messages_container');
            add_message_to_conversation($messagesContainer,data);
        }

    };

    //send message to driver

    $("#btn-save-messageDriver").click(function (e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            }
        });


        var formData = {
            message_text: $('#message_text').val()
        };

        var driver_id = $('#messageDriver_id').val();

        console.log(formData);

        $.ajax({

            type: "POST",
            url: '/driver/' + driver_id + '/message/',
            data: formData,
            dataType: 'json',
            success: function (data) {
                console.log(data);
                
                $('#messageForm').trigger("reset");
                //$('#messageModal').modal('hide');
                $('#message-list-panel').show();
                
                add_message_line(data);
                add_message_to_conversation_display(data);
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