<form id="orgTwilioForm" name="orgTwilioForm" class="form-horizontal" novalidate="">
    <div class="form-group">
        <label for="input_twilio_account_sid" class="col-sm-5 control-label">Account SID</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_account_sid" name="twilio_account_sid"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_twilio_auth_token" class="col-sm-5 control-label">Authentication Token</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_auth_token" name="twilio_auth_token"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_twilio_phone_number" class="col-sm-5 control-label">Phone Number</label>
        <div class="col-sm-7">
            <input type="text" class="form-control"
                   id="twilio_phone_number" name="twilio_phone_number"
                   value="">
        </div>
    </div>
    <div class="form-group">
        <label for="span_twilio_inbound_message_request_url" class="col-sm-5 control-label">
            Inbound message request URL
        </label>
        <div class="col-sm-7">
            <div class="input-group">
                <input class="form-control" id="twilio_inbound_message_request_url" title=""
                       name="twilio_inbound_message_request_url" disabled="disabled"
                       value="{{ $twilio_inbound_message_request_url }}">
                <span class="input-group-button">
                    <button class="btn copy-to-clipboard" data-clipboard-target="#twilio_inbound_message_request_url"
                            data-clipboard-success-alert="#copy-twilio_inbound_message_request_url-to-clipboard-success-alert"
                            title="copy to clipboard">
                        <img class="clippy" src="{{ asset('clippy.svg') }}" alt="Copy to clipboard" width="13">
                    </button>
                </span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="span_twilio_outbound_message_status_callback_url" class="col-sm-5 control-label">
            Outbound status Callback URL
        </label>
        <div class="col-sm-7">
            <div class="input-group">
                <input class="form-control" id="twilio_outbound_message_status_callback_url" title=""
                       name="twilio_outbound_message_status_callback_url" disabled="disabled"
                       value="{{ $twilio_outbound_message_status_callback_url }}">
                </input>
                <span class="input-group-button">
                    <button class="btn copy-to-clipboard"
                            data-clipboard-target="#twilio_outbound_message_status_callback_url"
                            data-clipboard-success-alert="#copy-twilio_outbound_message_status_callback_url-to-clipboard-success-alert"
                            title="copy to clipboard">
                        <img class="clippy" src="{{ asset('clippy.svg') }}" alt="Copy to clipboard" width="13">
                    </button>
                </span>
            </div>
        </div>
    </div>
    <input type="hidden" name="twilio_username" id="twilio_username" value="{{ $twilio_username }}">
    <input type="hidden" name="twilio_user_password" id="twilio_user_password" value="{{ $twilio_user_password }}">
</form>
<div class="alert alert-success alert-dismissible fade in" role="alert" style="display:none"
     id="copy-twilio_outbound_message_status_callback_url-to-clipboard-success-alert">
    The url is in your paste buffer now, so go to your
    <a href="https://www.twilio.com/console/sms/services" target="_blank">Twilio Messaging Service</a>
    and paste it in as the Outbound Settings STATUS CALLBACK URL.
</div>
<div class="alert alert-success alert-dismissible fade in" role="alert" style="display:none"
     id="copy-twilio_inbound_message_request_url-to-clipboard-success-alert">
    The url is in your paste buffer now, so go to your
    <a href="https://www.twilio.com/console/sms/services" target="_blank">Twilio Messaging Service</a>
    and paste it in as the Inbound Settings REQUEST URL.
</div>

<script src="{{ asset('/js/clipboard.js') }}"></script>

<script>

    $(document).ready(function () {
        setClickableTooltip(
                '#twilio_inbound_message_request_url',
                'Copy and paste into your <a href="https://www.twilio.com/console/sms/services" ' +
                'target="_blank">Twilio Messaging Service</a> Inbound Settings Request URL');
        setClickableTooltip(
                '#twilio_outbound_message_status_callback_url',
                'Copy and paste into your <a href="https://www.twilio.com/console/sms/services" ' +
                'target="_blank">Twilio Messaging Service</a> Outbound Settings Status Callback URL'
        );

        function copyToClipboardFF(text) {
            window.prompt ("Copy to clipboard: Ctrl C, Enter", text);
        }

        $('button.copy-to-clipboard').click(function(e){
            var success   = true,
                    range     = document.createRange(),
                    selection;

            e.preventDefault();

            $(this).closest('.modal').find('.alert').hide();
            $(this).closest('.modal').on('hidden.bs.modal', function(){
                $(this).find('.alert').hide();
            });

            var input = $($(this).attr('data-clipboard-target'));

            if (window.clipboardData) {
                window.clipboardData.setData("Text", input.val());
            } else {

                // Create a "hidden" input
                var tmpElem = $('<div>');
                tmpElem.css({
                    position: "absolute",
                    left:     "-1000px",
                    top:      "-1000px",
                });

                // Add the input value to the temp element.
                tmpElem.text(input.val());

                // append the temp element into the parent modal - we don't want to go outside the modal
                $(this).closest('.modal').append(tmpElem);

                // Select temp element.
                range.selectNodeContents(tmpElem.get(0));
                selection = window.getSelection ();
                selection.removeAllRanges ();
                selection.addRange (range);

                // Lets copy.
                try {
                    success = document.execCommand("copy", false, null);
                }
                catch (e) {
                    copyToClipboardFF(input.val());
                }
                if (success) {
                    if ($(this).attr('data-clipboard-success-alert')){
                        $($(this).attr('data-clipboard-success-alert')).show();
                    }
                    // remove temp element.
                    tmpElem.remove();
                }
            }
        })



    });



</script>
