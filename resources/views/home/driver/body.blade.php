<form id="driverForm" name="driverForm" class="form-horizontal" novalidate="">
    <div class="form-group error">
        <label for="input_first_name" class="col-sm-3 control-label">First
            name</label>
        <div class="col-sm-9">
            <input type="text" class="form-control has-error" id="first_name"
                   name="first_name" placeholder="required" value="">
        </div>
    </div>
    <div class="form-group error">
        <label for="input_last_name" class="col-sm-3 control-label">Last
            name</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="last_name"
                   name="last_name" placeholder="required" value="">
        </div>
    </div>
    <div class="form-group error">
        <label for="input_driver_mobile_phone_number"
               class="col-sm-3 control-label">Mobile phone number</label>
        <div class="col-sm-9">
            <input type="text" class="form-control"
                   id="driver_mobile_phone_number" name="mobile_phone_number"
                   placeholder="required" value="">
        </div>
    </div>
    <div class="form-group">
        <label for="input_drivers_licence_number"
               class="col-sm-3 control-label">Divers licence number</label>
        <div class="col-sm-9">
            <input type="text" class="form-control text-uppercase" id="drivers_licence_number"
                   name="drivers_licence_number" placeholder="optional"
                   value="">
        </div>
    </div>
</form>
<form id="test-incoming-message-form">

    <input id="test-message-from-driver-button" type="submit" value="test" style="display:none">
    <input type="hidden" name="MessageSid" value="{{bin2hex(random_bytes(17))}}">
    <input type="hidden" name="AccountSid" value="{{$org->twilio_account_sid}}">
    <input type="hidden" name="MessagingServiceSid" value="MG{{bin2hex(random_bytes(16))}}">
    <input type="hidden" name="From" value="">
    <input type="hidden" name="To" value="{{$org->twilio_phone_number}}">
    <input type="hidden" name="Body" value="test">
    <input type="hidden" name="NumMedia" value="0">


</form>
<div id="simulated-message-success-alert" class="alert alert-success" style="display:none">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <strong>Success!</strong> Server accepted simulated message.
</div>